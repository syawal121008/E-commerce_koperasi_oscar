<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class OrderItemsController extends Controller
{
    /**
     * Display cart items for current user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get pending order items for the current user (acts as cart)
        $orderItems = OrderItem::with(['product.admin', 'order'])
            ->whereHas('order', function($query) use ($user) {
                $query->where('customer_id', $user->user_id)
                      ->where('status', Order::STATUS_PENDING);
            })
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'paid' => true,
                'data' => $orderItems
            ]);
        }

        return view('cart.index', compact('orderItems'));
    }

    /**
     * Add item to cart (create pending order item)
     */
    public function store(Request $request)
    {
        try {
            // Log incoming request for debugging
            Log::info('Cart store request', $request->all());
            
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,product_id',
                'quantity' => 'required|integer|min:1',
                'admin_id' => 'required|exists:users,user_id'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed', $validator->errors()->toArray());
                if ($request->expectsJson()) {
                    return response()->json([
                        'paid' => false,
                        'message' => 'Data tidak valid',
                        'errors' => $validator->errors()
                    ], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();

            $user = $request->user();
            $product = Product::with('admin')->find($request->product_id);
            
            if (!$product) {
                throw new \Exception('Produk tidak ditemukan');
            }
            
            // Validate product ownership
            if ($product->admin_id != $request->admin_id) {
                throw new \Exception('Produk tidak sesuai dengan penjual');
            }
            
            // Validate stock
            if ($product->stock < $request->quantity) {
                throw new \Exception('Stok tidak mencukupi. Stok tersedia: ' . $product->stock);
            }

            // Check if product is active
            if (!$product->is_active) {
                throw new \Exception('Produk tidak tersedia');
            }

            // Find or create pending order for this admin
            $order = Order::where([
                'customer_id' => $user->user_id,
                'admin_id' => $request->admin_id,
                'status' => Order::STATUS_PENDING
            ])->first();

            if (!$order) {
                $order = Order::create([
                    'customer_id' => $user->user_id,
                    'admin_id' => $request->admin_id,
                    'status' => Order::STATUS_PENDING,
                    'total_price' => 0,
                    'payment_method' => 'pending',
                    'balance_used' => 0,
                    'cash_amount' => 0,
                ]);
            }

            // Check if item already exists in cart
            $existingItem = OrderItem::where('order_id', $order->order_id)
                ->where('product_id', $product->product_id)
                ->first();

            if ($existingItem) {
                // Update quantity
                $newQuantity = $existingItem->quantity + $request->quantity;
                
                if ($product->stock < $newQuantity) {
                    throw new \Exception('Stok tidak mencukupi untuk jumlah total yang diminta. Stok tersedia: ' . $product->stock . ', Total diminta: ' . $newQuantity);
                }
                
                $existingItem->quantity = $newQuantity;
                $existingItem->unit_price = $product->price;
                $existingItem->subtotal = $existingItem->quantity * $product->price;
                $existingItem->save();
                
                $orderItem = $existingItem;
            } else {
                // Create new order item
                $orderItem = OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $product->product_id,
                    'quantity' => $request->quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $product->price * $request->quantity,
                ]);
            }

            // Update order total
            $order->calculateTotal();

            DB::commit();

            Log::info('Cart item added paidfully', ['item_id' => $orderItem->order_item_id]);

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Produk berhasil ditambahkan ke keranjang',
                    'data' => $orderItem->load('product')
                ], 201);
            }

            return back()->with('paid', 'Produk berhasil ditambahkan ke keranjang');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cart store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $orderItemId)
    {
        $validator = Validator::make(array_merge($request->all(), ['item_id' => $orderItemId]), [
            'item_id' => 'required|exists:order_items,order_item_id',
            'action' => 'required|in:increase,decrease',
            'quantity' => 'sometimes|integer|min:1'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $orderItem = OrderItem::with(['product', 'order'])->findOrFail($orderItemId);
            
            // Verify ownership
            if ($orderItem->order->customer_id !== $request->user()->user_id) {
                throw new \Exception('Tidak diizinkan');
            }

            // Verify order is still pending
            if ($orderItem->order->status !== Order::STATUS_PENDING) {
                throw new \Exception('Keranjang sudah tidak dapat diubah');
            }

            if ($request->has('quantity')) {
                $newQuantity = $request->quantity;
            } else {
                $newQuantity = $request->action === 'increase' 
                    ? $orderItem->quantity + 1 
                    : $orderItem->quantity - 1;
            }

            if ($newQuantity <= 0) {
                $order = $orderItem->order;
                $orderItem->delete();
                
                // Update order total or delete order if empty
                if ($order->items()->count() === 0) {
                    $order->delete();
                } else {
                    $order->calculateTotal();
                }
            } else {
                // Validate stock
                if ($orderItem->product->stock < $newQuantity) {
                    throw new \Exception('Stok tidak mencukupi. Stok tersedia: ' . $orderItem->product->stock);
                }

                $orderItem->quantity = $newQuantity;
                $orderItem->subtotal = $orderItem->unit_price * $newQuantity;
                $orderItem->save();

                // Update order total
                $orderItem->order->calculateTotal();
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Keranjang berhasil diperbarui',
                    'data' => $orderItem->exists ? $orderItem->fresh('product') : null
                ]);
            }

            return back()->with('paid', 'Keranjang berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove item from cart
     */
    public function destroy(Request $request, $orderItemId)
    {
        try {
            DB::beginTransaction();

            $orderItem = OrderItem::with('order')->findOrFail($orderItemId);
            
            // Verify ownership
            if ($orderItem->order->customer_id !== $request->user()->user_id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'paid' => false,
                        'message' => 'Tidak diizinkan'
                    ], 403);
                }
                abort(403);
            }

            // Verify order is still pending
            if ($orderItem->order->status !== Order::STATUS_PENDING) {
                throw new \Exception('Item tidak dapat dihapus');
            }

            $order = $orderItem->order;
            $orderItem->delete();

            // Update order total or delete order if empty
            if ($order->items()->count() === 0) {
                $order->delete();
            } else {
                $order->calculateTotal();
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Item berhasil dihapus dari keranjang'
                ]);
            }

            return back()->with('paid', 'Item berhasil dihapus dari keranjang');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Bulk delete selected items
     */
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_item_ids' => 'required|string'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $orderItemIds = explode(',', $request->order_item_ids);
            $orderItems = OrderItem::with('order')->whereIn('order_item_id', $orderItemIds)->get();

            if ($orderItems->isEmpty()) {
                throw new \Exception('Tidak ada item yang dipilih');
            }

            $affectedOrders = collect();

            foreach ($orderItems as $orderItem) {
                // Verify ownership
                if ($orderItem->order->customer_id !== $request->user()->user_id) {
                    throw new \Exception('Tidak diizinkan');
                }

                // Verify order is still pending
                if ($orderItem->order->status !== Order::STATUS_PENDING) {
                    throw new \Exception('Item tidak dapat dihapus');
                }

                $affectedOrders->push($orderItem->order);
                $orderItem->delete();
            }

            // Update or delete affected orders
            foreach ($affectedOrders->unique('order_id') as $order) {
                if ($order->items()->count() === 0) {
                    $order->delete();
                } else {
                    $order->calculateTotal();
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Item terpilih berhasil dihapus'
                ]);
            }

            return back()->with('paid', 'Item terpilih berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get cart count for current user
     */
    public function count(Request $request)
    {
        $user = $request->user();
        
        $count = OrderItem::whereHas('order', function($query) use ($user) {
            $query->where('customer_id', $user->user_id)
                  ->where('status', Order::STATUS_PENDING);
        })->sum('quantity');

        return response()->json([
            'paid' => true,
            'count' => $count
        ]);
    }

    /**
     * Clear entire cart for current user
     */
    public function clear(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user();
            
            // Get all pending orders for user
            $pendingOrders = Order::where('customer_id', $user->user_id)
                ->where('status', Order::STATUS_PENDING)
                ->get();

            foreach ($pendingOrders as $order) {
                $order->items()->delete();
                $order->delete();
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Keranjang berhasil dikosongkan'
                ]);
            }

            return back()->with('paid', 'Keranjang berhasil dikosongkan');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}