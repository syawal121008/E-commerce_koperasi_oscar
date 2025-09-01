<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CartsController extends Controller
{
    /**
     * Display cart items for current user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get cart items for the current user
        $cartItems = Cart::with(['product.admin', 'product.category'])
            ->where('user_id', $user->user_id)
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'paid' => true,
                'data' => $cartItems
            ]);
        }

        return view('carts.index', compact('cartItems'));
    }

    /**
     * Add item to cart
     */
    public function store(Request $request)
    {
        try {
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

            // Check if item already exists in cart
            $existingCart = Cart::where('user_id', $user->user_id)
                ->where('product_id', $product->product_id)
                ->first();

            if ($existingCart) {
                // Update quantity
                $newQuantity = $existingCart->quantity + $request->quantity;
                
                if ($product->stock < $newQuantity) {
                    throw new \Exception('Stok tidak mencukupi untuk jumlah total yang diminta. Stok tersedia: ' . $product->stock . ', Total diminta: ' . $newQuantity);
                }
                
                $existingCart->quantity = $newQuantity;
                $existingCart->unit_price = $product->price;
                $existingCart->subtotal = $existingCart->quantity * $product->price;
                $existingCart->save();
                
                $cartItem = $existingCart;
            } else {
                // Create new cart item
                $cartItem = Cart::create([
                    'user_id' => $user->user_id,
                    'product_id' => $product->product_id,
                    'quantity' => $request->quantity,
                    'unit_price' => $product->price,
                    'subtotal' => $product->price * $request->quantity,
                    'admin_id' => $product->admin_id,
                ]);
            }

            DB::commit();

            Log::info('Cart item added paidfully', ['cart_id' => $cartItem->cart_id]);

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Produk berhasil ditambahkan ke keranjang',
                    'data' => $cartItem->load('product')
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
    public function update(Request $request, $cartId)
    {
        $validator = Validator::make(array_merge($request->all(), ['cart_id' => $cartId]), [
            'cart_id' => 'required|exists:carts,cart_id',
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

            $cartItem = Cart::with('product')->findOrFail($cartId);
            
            // Verify ownership
            if ($cartItem->user_id !== $request->user()->user_id) {
                throw new \Exception('Tidak diizinkan');
            }

            if ($request->has('quantity')) {
                $newQuantity = $request->quantity;
            } else {
                $newQuantity = $request->action === 'increase' 
                    ? $cartItem->quantity + 1 
                    : $cartItem->quantity - 1;
            }

            if ($newQuantity <= 0) {
                $cartItem->delete();
            } else {
                // Validate stock
                if ($cartItem->product->stock < $newQuantity) {
                    throw new \Exception('Stok tidak mencukupi. Stok tersedia: ' . $cartItem->product->stock);
                }

                $cartItem->quantity = $newQuantity;
                $cartItem->subtotal = $cartItem->unit_price * $newQuantity;
                $cartItem->save();
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Keranjang berhasil diperbarui',
                    'data' => $cartItem->exists ? $cartItem->fresh('product') : null
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
    public function destroy(Request $request, $cartId)
    {
        try {
            DB::beginTransaction();

            $cartItem = Cart::findOrFail($cartId);
            
            // Verify ownership
            if ($cartItem->user_id !== $request->user()->user_id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'paid' => false,
                        'message' => 'Tidak diizinkan'
                    ], 403);
                }
                abort(403);
            }

            $cartItem->delete();

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
            'cart_ids' => 'required|string'
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

            $cartIds = explode(',', $request->cart_ids);
            $cartItems = Cart::whereIn('cart_id', $cartIds)->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception('Tidak ada item yang dipilih');
            }

            foreach ($cartItems as $cartItem) {
                // Verify ownership
                if ($cartItem->user_id !== $request->user()->user_id) {
                    throw new \Exception('Tidak diizinkan');
                }

                $cartItem->delete();
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
        
        $count = Cart::where('user_id', $user->user_id)->sum('quantity');

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
            
            Cart::where('user_id', $user->user_id)->delete();

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

    /**
     * Checkout selected items
     */
    public function checkout(Request $request)
    {
        Log::info('Checkout request data:', $request->all());
        
        $validator = Validator::make($request->all(), [
            'cart_ids' => 'required|array|min:1',
            'cart_ids.*' => 'exists:carts,cart_id',
            'payment_method' => 'required|in:balance,cash,mixed',
            'balance_used' => 'nullable|numeric|min:0',
            'cash_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            Log::error('Checkout validation failed:', $validator->errors()->toArray());
            return response()->json([
                'paid' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            
            $cartItems = Cart::with(['product'])
                ->whereIn('cart_id', $request->cart_ids)
                ->where('user_id', $user->user_id)
                ->get();

            if ($cartItems->isEmpty()) {
                throw new \Exception('Tidak ada item yang dipilih untuk checkout.');
            }

            $grandTotal = $cartItems->sum('subtotal');
            
            // === START: PERBAIKAN LOGIKA PEMBAYARAN ===
            $totalBalanceUsed = floatval($request->balance_used ?? 0);
            $totalCashAmount = floatval($request->cash_amount ?? 0);

            if ($request->payment_method === 'balance') {
                if ($user->balance < $grandTotal) {
                    throw new \Exception('Saldo tidak mencukupi untuk total pembayaran.');
                }
                $totalBalanceUsed = $grandTotal;
                $totalCashAmount = 0;
            } elseif ($request->payment_method === 'cash') {
                $totalBalanceUsed = 0;
                $totalCashAmount = $grandTotal;
            } elseif ($request->payment_method === 'mixed') {
                if (abs(($totalBalanceUsed + $totalCashAmount) - $grandTotal) > 0.01) {
                    throw new \Exception('Jumlah pembayaran (saldo + tunai) tidak sesuai dengan total belanja.');
                }
                if ($totalBalanceUsed > $user->balance) {
                    throw new \Exception('Saldo yang ingin digunakan melebihi saldo yang Anda miliki.');
                }
            }
            // === END: PERBAIKAN LOGIKA PEMBAYARAN ===

            $itemsByAdmin = $cartItems->groupBy('admin_id');
            $createdOrderIds = [];

            foreach ($itemsByAdmin as $adminId => $adminItems) {
                $orderTotal = $adminItems->sum('subtotal');
                $balanceForThisOrder = 0;
                $cashForThisOrder = 0;

                // Distribusikan pembayaran secara proporsional
                if ($grandTotal > 0) {
                    $proportion = $orderTotal / $grandTotal;
                    $balanceForThisOrder = round($totalBalanceUsed * $proportion, 2);
                    $cashForThisOrder = $orderTotal - $balanceForThisOrder; // Sisanya dari cash
                }
                
                $order = Order::create([
                    'customer_id' => $user->user_id,
                    'admin_id' => $adminId,
                    'total_price' => $orderTotal,
                    'status' => ($balanceForThisOrder >= $orderTotal) ? 'paid' : 'pending',
                    'payment_method' => $request->payment_method,
                    'balance_used' => $balanceForThisOrder,
                    'cash_amount' => $cashForThisOrder,
                    'notes' => $request->notes
                ]);

                foreach ($adminItems as $cartItem) {
                    if ($cartItem->product->stock < $cartItem->quantity) {
                        throw new \Exception("Stok tidak mencukupi untuk produk: {$cartItem->product->name}");
                    }

                    OrderItem::create([
                        'order_id' => $order->order_id,
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $cartItem->unit_price,
                        'subtotal' => $cartItem->subtotal
                    ]);

                    $cartItem->product->decrement('stock', $cartItem->quantity);
                    $cartItem->delete();
                }

                if ($balanceForThisOrder > 0) {
                    $user->deductBalance($balanceForThisOrder);

                    Transaction::create([
                        'user_id' => $user->user_id,
                        'type' => 'payment',
                        'amount' => $balanceForThisOrder,
                        'status' => 'paid',
                        'related_id' => $order->order_id,
                        'description' => 'Pembayaran pesanan #' . $order->order_id
                    ]);

                    $admin = User::findOrFail($adminId);
                    $admin->addBalance($balanceForThisOrder);

                    Transaction::create([
                        'user_id' => $admin->user_id,
                        'type' => 'income',
                        'amount' => $balanceForThisOrder,
                        'status' => 'paid',
                        'related_id' => $order->order_id,
                        'description' => 'Penjualan produk dari pesanan #' . $order->order_id
                    ]);
                }
                
                $createdOrderIds[] = $order->order_id;
            }

            DB::commit();
            
            // === START: PERBAIKAN PENGAMBILAN DATA UNTUK RESPONSE ===
            $processedOrders = Order::with(['items.product', 'admin'])
                                    ->whereIn('order_id', $createdOrderIds)
                                    ->get();
            // === END: PERBAIKAN PENGAMBILAN DATA UNTUK RESPONSE ===

            Log::info('Checkout paidful', [
                'user_id' => $user->user_id,
                'orders_count' => $processedOrders->count()
            ]);

            // Selalu kembalikan JSON karena request datang dari fetch/AJAX
            return response()->json([
                'paid' => true,
                'message' => 'Checkout berhasil',
                'data' => [
                    'orders' => $processedOrders
                ],
                // Tambahkan redirect URL agar JavaScript bisa mengarahkan pengguna
                'redirect_url' => $processedOrders->count() === 1 
                    ? route('orders.show', $processedOrders->first()->order_id) 
                    : route('orders.index')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Checkout error: ' . $e->getMessage(), [
                'user_id' => optional($request->user())->user_id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'paid' => false,
                'message' => $e->getMessage(),
                'error' => 'Checkout gagal'
            ], 500);
        }
    }
}