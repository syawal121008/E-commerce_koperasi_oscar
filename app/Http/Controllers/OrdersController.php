<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables; 
use App\Exports\ProfitExport; // Tambahkan ini
use Maatwebsite\Excel\Facades\Excel; // Tambahkan ini

class OrdersController extends Controller
{
    /**
     * Display orders based on user role
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with(['customer', 'admin', 'items.product']);

        // Filter orders based on user role
        if ($user->role === 'customer') {
            $query->where('customer_id', $user->user_id);
        } elseif ($user->role === 'admin') {
            $query->where('admin_id', $user->user_id);
        }
        // Admin can see all orders

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        }

        return view('orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new order
     */
    public function create(Request $request)
    {
        // Get all active products with admin and category relationships
        $products = Product::with(['admin', 'category'])
            ->active()
            ->inStock()
            ->get();

        // Get user balance for payment calculation
        $userBalance = $request->user()->balance;

        return view('orders.create', compact('products', 'userBalance'));
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_id' => 'nullable|uuid|exists:users,user_id',
            'customer_id' => 'nullable|uuid|exists:users,user_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:balance,cash,mixed',
            'balance_used' => 'nullable|numeric|min:0',
            'cash_amount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            
            // Determine customer and admin
            $customerId = $request->customer_id ?? $user->user_id;
            $adminId = $request->admin_id;
            
            // If admin_id not provided, get from first product
            if (!$adminId) {
                $firstProduct = Product::find($request->items[0]['product_id']);
                $adminId = $firstProduct->admin_id;
            }
            
            $customer = User::findOrFail($customerId);
            $totalAmount = $request->total_amount;
            $balanceUsed = $request->balance_used ?? 0;
            $cashAmount = $request->cash_amount ?? 0;

            // Validate payment method and amounts
            if ($request->payment_method === 'balance') {
                if ($customer->balance < $totalAmount) {
                    throw new \Exception('Saldo tidak mencukupi');
                }
                $balanceUsed = $totalAmount;
                $cashAmount = 0;
            } elseif ($request->payment_method === 'cash') {
                $balanceUsed = 0;
                $cashAmount = $totalAmount;
            } elseif ($request->payment_method === 'mixed') {
                $balanceUsed = min($customer->balance, $totalAmount);
                $cashAmount = $totalAmount - $balanceUsed;
            }

            // Validate total amount
            if (($balanceUsed + $cashAmount) != $totalAmount) {
                throw new \Exception('Invalid payment calculation');
            }

            // Create order
            $order = Order::create([
                'customer_id' => $customerId,
                'admin_id' => $adminId,
                'total_price' => $totalAmount,
                'status' => ($request->payment_method === 'cash') ? 'pending' : 'paid',
                'payment_method' => $request->payment_method,
                'balance_used' => $balanceUsed,
                'cash_amount' => $cashAmount,
                'notes' => $request->notes
            ]);

            $calculatedTotal = 0;

            // Create order items and validate stock
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok tidak mencukupi untuk produk: {$product->name}");
                }

                if ($product->admin_id !== $adminId) {
                    throw new \Exception("Produk {$product->name} bukan milik penjual yang dipilih");
                }

                $unitPrice = $product->price;
                $subtotal = $unitPrice * $item['quantity'];
                $calculatedTotal += $subtotal;

                OrderItem::create([
                    'order_id' => $order->order_id,
                    'product_id' => $product->product_id,
                    'quantity' => $item['quantity'],
                    'modal_price' => $product->modal_price ?? 0,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal
                ]);

                // Reserve stock
                $product->decrement('stock', $item['quantity']);
            }

            // Process payment if using balance
            if ($balanceUsed > 0) {
                if (!$customer->deductBalance($balanceUsed)) {
                    throw new \Exception('Gagal mengurangi saldo');
                }

                // Create transaction record for balance usage
                Transaction::create([
                    'user_id' => $customerId,
                    'type' => 'payment',
                    'amount' => $balanceUsed,
                    'status' => 'completed',
                    'related_id' => $order->order_id,
                    'description' => 'Pembayaran pesanan #' . $order->order_id
                ]);

                // Add balance to admin
                $admin = User::findOrFail($adminId);
                $admin->addBalance($balanceUsed);

                // Create transaction record for admin
                Transaction::create([
                    'user_id' => $admin->user_id,
                    'type' => 'income',
                    'amount' => $balanceUsed,
                    'status' => 'completed',
                    'related_id' => $order->order_id,
                    'description' => 'Penjualan produk dari pesanan #' . $order->order_id
                ]);
            }

            DB::commit();

            $order->load(['items.product', 'customer', 'admin']);

            if ($request->expectsJson()) {
                return response()->json([
                    'completed' => true,
                    'message' => 'Pesanan berhasil dibuat',
                    'data' => $order
                ], 201);
            }

            // Redirect ke struk jika pesanan langsung dibayar dengan saldo
            if ($request->payment_method === 'balance') {
                return redirect()->route('orders.receipt.show', $order->order_id)
                    ->with('success', 'Pesanan berhasil dibuat dan dibayar! Berikut struk pembelian Anda.');
            }
            return redirect()->route('orders.show', $order->order_id)
                ->with('success', 'Pesanan berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat pesanan',
                    'error' => $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, $orderId)
    {
        try {
            $order = Order::with(['items.product', 'customer', 'admin', 'transaction'])
                ->findOrFail($orderId);

            // Check authorization
            if ($order->customer_id !== $request->user()->user_id && 
                $order->admin_id !== $request->user()->user_id &&
                $request->user()->role !== 'admin') {
                
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'Tidak diizinkan'], 403);
                }
                abort(403, 'AKSES DITOLAK.');
            }

            return view('orders.show', compact('order'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
            }
            return redirect()->route('orders.index')->with('error', 'Terjadi kesalahan saat menampilkan pesanan.');
        }
    }

    /**
     * Pay remaining cash amount for mixed payment orders
     */
    public function payCash(Request $request, $orderId)
    {
        $validator = Validator::make($request->all(), [
            'amount_paid' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::with(['items.product', 'customer', 'admin'])->findOrFail($orderId);
            
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak dalam status pending'
                ], 400);
            }

            if ($order->payment_method !== 'cash' && $order->payment_method !== 'mixed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Metode pembayaran tidak valid untuk pembayaran tunai'
                ], 400);
            }

            $requiredAmount = $order->cash_amount;
            if ($request->amount_paid < $requiredAmount) {
                return response()->json([
                    'completed' => false,
                    'message' => 'Jumlah pembayaran kurang',
                    'data' => [
                        'required' => $requiredAmount,
                        'paid' => $request->amount_paid
                    ]
                ], 400);
            }

            DB::beginTransaction();

            // Update order status
            $order->status = 'completed';
            $order->save();

            // Add cash amount to admin balance
            if ($requiredAmount > 0) {
                $admin = User::findOrFail($order->admin_id);
                $admin->addBalance($requiredAmount);

                // Create transaction record for admin
                Transaction::create([
                    'user_id' => $admin->user_id,
                    'type' => 'income',
                    'amount' => $requiredAmount,
                    'status' => 'success',
                    'related_id' => $order->order_id,
                    'description' => 'Penjualan produk (tunai) dari pesanan #' . $order->order_id
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran tunai berhasil',
                'data' => [
                    'order' => $order->fresh(['items.product']),
                    'change' => max(0, $request->amount_paid - $requiredAmount)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, $orderId)
    {
        try {
            $order = Order::with('items.product')->findOrFail($orderId);

            // Check authorization
            if ($order->customer_id !== $request->user()->user_id && 
                $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak diizinkan untuk membatalkan pesanan ini'
                ], 403);
            }

            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya pesanan pending yang dapat dibatalkan'
                ], 400);
            }

            DB::beginTransaction();

            // Restore product stock
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            // Refund balance if any was used
            if ($order->balance_used > 0) {
                $customer = User::findOrFail($order->customer_id);
                $customer->addBalance($order->balance_used);

                // Deduct from admin if already paid
                $admin = User::findOrFail($order->admin_id);
                $admin->deductBalance($order->balance_used);

                // Create refund transaction
                Transaction::create([
                    'user_id' => $customer->user_id,
                    'type' => 'refund',
                    'amount' => $order->balance_used,
                    'status' => 'success',
                    'related_id' => $order->order_id,
                    'description' => 'Refund pembatalan pesanan #' . $order->order_id
                ]);
            }

            $order->status = 'cancelled';
            $order->save();

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil dibatalkan',
                    'data' => $order->fresh()
                ]);
            }

            return back()->with('success', 'Pesanan berhasil dibatalkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pesanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show order after QR scan for payment
     */
    public function showForPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,order_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $order = Order::with(['items.product', 'customer', 'admin'])
                ->findOrFail($request->order_id);

            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak tersedia untuk pembayaran'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail pesanan untuk pembayaran',
                'data' => $order
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Pay order using QR scan
     */
    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,order_id',
            'qr_data' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Decode QR data to get payer info
            $qrData = json_decode($request->qr_data, true);
            
            if (!$qrData || !isset($qrData['user_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format QR code tidak valid'
                ], 400);
            }

            $order = Order::with(['items.product', 'customer', 'admin'])->findOrFail($request->order_id);
            
            if ($order->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan tidak dalam status pending'
                ], 400);
            }

            // Get payer from QR
            $payer = User::findOrFail($qrData['user_id']);
            
            // Check balance
            if (!$payer->hasSufficientBalance($order->total_price)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo tidak mencukupi',
                    'data' => [
                        'required' => $order->total_price,
                        'available' => $payer->balance
                    ]
                ], 400);
            }

            DB::beginTransaction();

            // Process payment
            $payer->deductBalance($order->total_price);
            $order->admin->addBalance($order->total_price);
            $order->status = 'paid';
            $order->save();

            // Create transaction record
            Transaction::create([
                'user_id' => $payer->user_id,
                'type' => 'payment',
                'amount' => $order->total_price,
                'status' => 'success',
                'related_id' => $order->order_id,
                'description' => 'Pembayaran QR pesanan #' . $order->order_id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil',
                'data' => [
                    'order' => $order->fresh(['items.product']),
                    'payer_balance' => $payer->balance
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran gagal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete order (admin only)
     */
    public function complete(Request $request, $orderId)
    {
        try {
            $order = Order::findOrFail($orderId);

            // Check if user is the admin
            if ($order->admin_id !== $request->user()->user_id && $request->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak diizinkan. Hanya penjual yang dapat menyelesaikan pesanan.'
                ], 403);
            }

            if ($order->status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan harus dibayar sebelum dapat diselesaikan'
                ], 400);
            }

            $order->status = 'complete';
            $order->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pesanan berhasil diselesaikan',
                    'data' => $order->fresh()
                ]);
            }

            return back()->with('success', 'Pesanan berhasil diselesaikan');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan pesanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending orders for admin
     */
    public function pending(Request $request)
    {
        $user = $request->user();
        
        // Query untuk pending orders
        $query = Order::with(['customer', 'admin', 'items.product'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc');
        
        // Filter berdasarkan role user
        if ($user->role === 'admin') {
            // Admin hanya melihat pesanan untuk toko mereka
            $query->where('admin_id', $user->user_id);
        } elseif ($user->role === 'guru') {
            // Guru bisa melihat semua pending orders (supervisor)
            // Tidak perlu filter tambahan
        } else {
            // Customer tidak bisa akses halaman pending
            abort(403, 'Akses ditolak');
        }

        $orders = $query->paginate(10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        }

        // Ambil nilai 'search' dari request
    $search = $request->input('search');

    // Mulai query untuk mendapatkan pesanan
    $query = Order::where('status', 'pending')->latest();

    // Jika ada nilai 'search', tambahkan filter
    if ($search) {
        $query->whereHas('customer', function ($q) use ($search) {
            $q->where('full_name', 'like', '%' . $search . '%');
        });
    }

    // Terapkan pagination
    $orders = $query->paginate(10); // Misalnya 10 item per halaman

        return view('orders.pending', compact('orders'));
    }

    /**
     * Method helper untuk mendapatkan jumlah pending orders
     * Bisa dipanggil dari mana saja
     */
    public function getPendingOrdersCount($adminId = null)
    {
        $query = Order::where('status', 'pending');
        
        if ($adminId) {
            $query->where('admin_id', $adminId);
        }
        
        return $query->count();
    }

    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_item_ids' => 'required|array|min:1',
            'order_item_ids.*' => 'exists:order_items,item_id',
            'payment_method' => 'required|in:balance,cash,mixed',
            'balance_used' => 'nullable|numeric|min:0',
            'cash_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            
            // Get selected order items with their orders
            $orderItems = OrderItem::with(['order', 'product'])
                ->whereIn('item_id', $request->order_item_ids)
                ->get();

            if ($orderItems->isEmpty()) {
                throw new \Exception('Tidak ada item yang dipilih');
            }

            // Verify all items belong to user and are pending
            foreach ($orderItems as $item) {
                if ($item->order->customer_id !== $user->user_id) {
                    throw new \Exception('Tidak diizinkan');
                }
                if ($item->order->status !== Order::STATUS_PENDING) {
                    throw new \Exception('Item tidak dapat diproses');
                }
            }

            // Group items by admin
            $itemsByAdmin = $orderItems->groupBy('order.admin_id');
            $processedOrders = collect();

            foreach ($itemsByAdmin as $adminId => $adminItems) {
                $totalAmount = $adminItems->sum('subtotal');
                $balanceUsed = 0;
                $cashAmount = 0;

                // Calculate payment distribution
                if ($request->payment_method === 'balance') {
                    if ($user->balance < $totalAmount) {
                        throw new \Exception('Saldo tidak mencukupi');
                    }
                    $balanceUsed = $totalAmount;
                } elseif ($request->payment_method === 'cash') {
                    $cashAmount = $totalAmount;
                } elseif ($request->payment_method === 'mixed') {
                    $requestedBalance = $request->balance_used ?? 0;
                    $requestedCash = $request->cash_amount ?? 0;
                    
                    if (($requestedBalance + $requestedCash) != $totalAmount) {
                        throw new \Exception('Jumlah pembayaran tidak sesuai dengan total');
                    }
                    
                    if ($requestedBalance > $user->balance) {
                        throw new \Exception('Saldo tidak mencukupi');
                    }
                    
                    $balanceUsed = $requestedBalance;
                    $cashAmount = $requestedCash;
                }

                // Update the existing pending order
                $pendingOrder = $adminItems->first()->order;
                
                // Remove items that are not selected from this order
                $allOrderItems = $pendingOrder->items;
                $selectedItemIds = $adminItems->pluck('item_id')->toArray();
                
                foreach ($allOrderItems as $item) {
                    if (!in_array($item->order_item_id, $selectedItemIds)) {
                        // Move unselected items to a new pending order
                        $newPendingOrder = Order::create([
                            'customer_id' => $user->user_id,
                            'admin_id' => $adminId,
                            'total_price' => 0,
                            'status' => 'pending',
                            'payment_method' => null,
                            'balance_used' => 0,
                            'cash_amount' => 0,
                        ]);
                        
                        $item->order_id = $newPendingOrder->order_id;
                        $item->save();
                    }
                }

                // Update the order for checkout
                $pendingOrder->update([
                    'total_price' => $totalAmount,
                    'status' => $request->payment_method === 'balance' ? 'paid' : 'pending',
                    'payment_method' => $request->payment_method,
                    'balance_used' => $balanceUsed,
                    'cash_amount' => $cashAmount,
                    'notes' => $request->notes
                ]);

                // Validate stock and reserve products
                foreach ($adminItems as $item) {
                    if ($item->product->stock < $item->quantity) {
                        throw new \Exception("Stok tidak mencukupi untuk produk: {$item->product->name}");
                    }
                    $item->product->decrement('stock', $item->quantity);
                }

                // Process payment if using balance
                if ($balanceUsed > 0) {
                    if (!$user->deductBalance($balanceUsed)) {
                        throw new \Exception('Gagal mengurangi saldo');
                    }

                    // Create transaction record for balance usage
                    Transaction::create([
                        'user_id' => $user->user_id,
                        'type' => 'payment',
                        'amount' => $balanceUsed,
                        'status' => 'success',
                        'related_id' => $pendingOrder->order_id,
                        'description' => 'Pembayaran pesanan #' . $pendingOrder->order_id
                    ]);

                    // Add balance to admin
                    $admin = User::findOrFail($adminId);
                    $admin->addBalance($balanceUsed);

                    // Create transaction record for admin
                    Transaction::create([
                        'user_id' => $admin->user_id,
                        'type' => 'income',
                        'amount' => $balanceUsed,
                        'status' => 'success',
                        'related_id' => $pendingOrder->order_id,
                        'description' => 'Penjualan produk dari pesanan #' . $pendingOrder->order_id
                    ]);
                }

                // Calculate new total for remaining items in new pending order
                $newPendingOrder = Order::where('customer_id', $user->user_id)
                    ->where('admin_id', $adminId)
                    ->where('status', 'pending')
                    ->where('order_id', '!=', $pendingOrder->order_id)
                    ->first();
                
                if ($newPendingOrder && $newPendingOrder->items()->count() > 0) {
                    $newPendingOrder->calculateTotal();
                } elseif ($newPendingOrder && $newPendingOrder->items()->count() === 0) {
                    $newPendingOrder->delete();
                }

                $processedOrders->push($pendingOrder);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Checkout berhasil',
                    'data' => [
                        'orders' => $processedOrders->load(['items.product', 'admin'])
                    ]
                ]);
            }

            // Redirect to orders page or specific order if single order
            if ($processedOrders->count() === 1) {
                return redirect()->route('orders.show', $processedOrders->first()->order_id)
                    ->with('success', 'Checkout berhasil! Pesanan Anda telah dibuat.');
            }

            return redirect()->route('orders.index')
                ->with('success', 'Checkout berhasil! ' . $processedOrders->count() . ' pesanan telah dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checkout gagal',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        // 1. Validasi: Pastikan status yang dikirim valid
        $request->validate([
            'status' => 'required|in:pending,paid,completed,cancelled',
        ]);

        // 2. Otorisasi: Hanya admin pemilik order atau admin yang boleh mengubah
        $user = $request->user();
        if ($user->role !== 'admin' && $order->admin_id !== $user->user_id) {
            abort(403, 'AKSES DITOLAK.');
        }

        // 3. Update Status
        $order->status = $request->status;
        $order->save();

        // 4. Redirect kembali dengan pesan sukses
        return back()->with('success', 'Status pesanan #' . $order->order_id . ' berhasil diubah menjadi ' . ucfirst($request->status));
    }

    /**
     * Show POS interface for admin
     */
    public function pos(Request $request)
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            abort(403, 'AKSES DITOLAK.');
        }

        // Get admin's active products with categories
        $products = Product::with(['category'])
            ->where('admin_id', $request->user()->user_id)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        // Get active categories that have products from this admin
        $categories = Category::whereHas('products', function($query) use ($request) {
            $query->where('admin_id', $request->user()->user_id)
                  ->where('is_active', true)
                  ->where('stock', '>', 0);
        })->where('is_active', true)->get();

        return view('admin.pos', compact('products', 'categories'));
    }

    /**
     * Store POS order
     */
    /**
 * Store POS order - FIXED VERSION
 */
public function storePOS(Request $request)
{
    try {
        // 1. ENHANCED VALIDATION WITH BETTER ERROR MESSAGES
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|string|exists:users,user_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|string|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:balance,cash',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ], [
            'customer_id.required' => 'Customer ID harus diisi',
            'customer_id.exists' => 'Customer tidak ditemukan',
            'items.required' => 'Item pesanan tidak boleh kosong',
            'items.min' => 'Minimal 1 item harus dipilih',
            'items.*.product_id.exists' => 'Produk tidak ditemukan',
            'items.*.quantity.min' => 'Jumlah minimal 1',
            'payment_method.in' => 'Metode pembayaran tidak valid',
            'total_amount.min' => 'Total amount harus lebih dari 0'
        ]);

        if ($validator->fails()) {
            \Log::warning('POS Validation Failed', [
                'errors' => $validator->errors()->toArray(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. GET ADMIN AND CUSTOMER WITH ERROR HANDLING
        $admin = $request->user();
        
        if ($admin->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Hanya admin yang dapat menggunakan POS.'
            ], 403);
        }

        $customer = User::where('user_id', $request->customer_id)->first();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer tidak ditemukan'
            ], 404);
        }

        $totalAmount = floatval($request->total_amount);

        // 3. VALIDATE BALANCE IF PAYING WITH BALANCE
        if ($request->payment_method === 'balance') {
            if ($customer->balance < $totalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo customer tidak mencukupi',
                    'data' => [
                        'customer_balance' => floatval($customer->balance),
                        'required_amount' => $totalAmount
                    ]
                ], 400);
            }
        }

        DB::beginTransaction();

        // 4. VALIDATE PRODUCTS AND STOCK WITH DETAILED LOGGING
        $validatedItems = [];
        $calculatedSubtotal = 0;

        foreach ($request->items as $index => $itemData) {
            \Log::info("Processing item {$index}", $itemData);
            
            $product = Product::where('product_id', $itemData['product_id'])->first();
            if (!$product) {
                throw new \Exception("Produk dengan ID {$itemData['product_id']} tidak ditemukan");
            }

            // Check product ownership
            if ($product->admin_id !== $admin->user_id) {
                throw new \Exception("Produk {$product->name} bukan milik toko Anda");
            }

            // Check if product is active
            if (!$product->is_active) {
                throw new \Exception("Produk {$product->name} tidak aktif");
            }

            // Check stock
            $quantity = intval($itemData['quantity']);
            if ($product->stock < $quantity) {
                throw new \Exception("Stok tidak mencukupi untuk {$product->name}. Tersedia: {$product->stock}, Diminta: {$quantity}");
            }

            // Calculate item subtotal
            $itemSubtotal = floatval($product->price) * $quantity;
            $calculatedSubtotal += $itemSubtotal;
            
            $validatedItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => floatval($product->price),
                'subtotal' => $itemSubtotal
            ];
        }

        // 5. VALIDATE TOTAL AMOUNT WITH TOLERANCE
        $tax = round($calculatedSubtotal * 0.1);
        $expectedTotal = $calculatedSubtotal + $tax;
        
        \Log::info('Total validation', [
            'calculated_subtotal' => $calculatedSubtotal,
            'tax' => $tax,
            'expected_total' => $expectedTotal,
            'received_total' => $totalAmount
        ]);
        
        if (abs($expectedTotal - $totalAmount) > 2) {
            throw new \Exception("Total tidak sesuai perhitungan. Expected: {$expectedTotal}, Got: {$totalAmount}");
        }

        // 6. CREATE ORDER WITH PROPER FIELDS
        $order = Order::create([
            'customer_id' => $customer->user_id,
            'admin_id' => $admin->user_id,
            'total_price' => $totalAmount,
            'status' => 'paid', // Always paid for POS transactions
            'payment_method' => $request->payment_method,
            'balance_used' => $request->payment_method === 'balance' ? $totalAmount : 0,
            'cash_amount' => $request->payment_method === 'cash' ? $totalAmount : 0,
            'notes' => $request->notes ?? 'POS Order'
        ]);

        if (!$order) {
            throw new \Exception('Gagal membuat pesanan');
        }

        \Log::info('Order created', ['order_id' => $order->order_id]);

        // 7. CREATE ORDER ITEMS AND UPDATE STOCK
        foreach ($validatedItems as $validatedItem) {
            $product = $validatedItem['product'];
            
            // Create order item - FIXED: menggunakan field yang benar
            $orderItem = OrderItem::create([
                'order_id' => $order->order_id,
                'product_id' => $product->product_id,
                'quantity' => $validatedItem['quantity'],
                'modal_price' => floatval($product->modal_price ?? 0),
                'unit_price' => $validatedItem['unit_price'],
                'subtotal' => $validatedItem['subtotal']
            ]);

            if (!$orderItem) {
                throw new \Exception("Gagal membuat item pesanan untuk produk {$product->name}");
            }

            // Update stock - FIXED: menggunakan decrement yang benar
            $product->decrement('stock', $validatedItem['quantity']);
            
            \Log::info('Stock updated', [
                'product_id' => $product->product_id,
                'quantity_sold' => $validatedItem['quantity'],
                'remaining_stock' => $product->fresh()->stock
            ]);
        }

        // 8. PROCESS PAYMENT IF BALANCE - FIXED: menggunakan method yang benar
        if ($request->payment_method === 'balance') {
            // Deduct from customer using model method
            if (!$customer->deductBalance($totalAmount)) {
                throw new \Exception('Gagal mengurangi saldo customer');
            }

            // Add to admin using model method
            if (!$admin->addBalance($totalAmount)) {
                throw new \Exception('Gagal menambah saldo admin');
            }

            // Create customer transaction
            Transaction::create([
                'user_id' => $customer->user_id,
                'type' => 'payment',
                'amount' => $totalAmount,
                'status' => 'success',
                'related_id' => $order->order_id,
                'description' => 'Pembayaran POS pesanan #' . $order->order_id
            ]);

            // Create admin transaction
            Transaction::create([
                'user_id' => $admin->user_id,
                'type' => 'income',
                'amount' => $totalAmount,
                'status' => 'success',
                'related_id' => $order->order_id,
                'description' => 'Penjualan POS dari pesanan #' . $order->order_id
            ]);

            \Log::info('Balance transactions completed', [
                'customer_new_balance' => $customer->fresh()->balance,
                'admin_new_balance' => $admin->fresh()->balance
            ]);
        }

        DB::commit();

        // 9. RETURN SUCCESS RESPONSE WITH FRESH DATA
        $order->load(['items.product', 'customer', 'admin']);

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil diproses',
            'data' => [
                'order_id' => $order->order_id,
                'customer_new_balance' => $request->payment_method === 'balance' ? 
                    floatval($customer->fresh()->balance) : floatval($customer->balance),
                'total_paid' => $totalAmount,
                'payment_method' => $request->payment_method,
                'order' => $order
            ]
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        
        \Log::error('POS Validation Exception', [
            'errors' => $e->errors(),
            'request_data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Data tidak valid',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        DB::rollBack();
        
        // Enhanced error logging
        \Log::error('POS Payment Error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'request_data' => $request->all(),
            'user_id' => $request->user()->user_id ?? 'unknown',
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'error_code' => 'PAYMENT_FAILED'
        ], 500);
    }
}
    /**
     * Get customer profile from QR code
     */
    public function getCustomerFromQR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Decode QR data
            $qrData = json_decode($request->qr_data, true);
            
            if (!$qrData || !isset($qrData['user_id'])) {
                throw new \Exception('Format QR code tidak valid');
            }

            // Get customer data
            $customer = User::where('user_id', $qrData['user_id'])
                ->whereIn('role', ['customer', 'guru', 'admin'])
                ->first();

            if (!$customer) {
                throw new \Exception('Customer tidak ditemukan');
            }

            // Generate profile photo URL menggunakan method profilePhotoUrl()
            $profilePhotoUrl = method_exists($customer, 'profilePhotoUrl') ? $customer->profilePhotoUrl() : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'profile' => [
                        'user_id' => $customer->user_id,
                        'full_name' => $customer->full_name,
                        'student_id' => $customer->student_id,
                        'role' => $customer->role,
                        'balance' => $customer->balance,
                        'formatted_balance' => 'Rp ' . number_format($customer->balance, 0, ',', '.'),
                        'profile_photo_url' => $profilePhotoUrl
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get real-time product stock
     */
    public function getProductStock(Request $request, $productId)
    {
        try {
            $product = Product::where('product_id', $productId)
                ->where('admin_id', $request->user()->user_id)
                ->first();

            if (!$product) {
                throw new \Exception('Produk tidak ditemukan');
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'product_id' => $product->product_id,
                    'stock' => $product->stock,
                    'is_active' => $product->is_active,
                    'price' => $product->price
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get POS sales report
     */
    public function posReport(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'AKSES DITOLAK.');
        }

        $startDate = $request->get('start_date', now()->startOfDay());
        $endDate = $request->get('end_date', now()->endOfDay());

        $orders = Order::with(['items.product', 'customer'])
            ->where('admin_id', $request->user()->user_id)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('notes', 'LIKE', '%POS%')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $totalSales = Order::where('admin_id', $request->user()->user_id)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('notes', 'LIKE', '%POS%')
            ->sum('total_price');

        $totalOrders = $orders->total();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $orders,
                    'summary' => [
                        'total_sales' => $totalSales,
                        'total_orders' => $totalOrders,
                        'average_order' => $totalOrders > 0 ? $totalSales / $totalOrders : 0
                    ]
                ]
            ]);
        }

        return view('admin.pos-report', compact('orders', 'totalSales', 'totalOrders', 'startDate', 'endDate'));
    }

    /**
     * Handle period filter and get appropriate date range
     */
    private function getDateRangeFromPeriod(Request $request)
{
    $period = $request->get('period', 'custom');
    $today = now();

    switch ($period) {
        case 'today':
            return [
                'start' => $today->copy()->startOfDay(),
                'end' => $today->copy()->endOfDay()
            ];

        case 'yesterday':
            return [
                'start' => $today->copy()->subDay()->startOfDay(),
                'end' => $today->copy()->subDay()->endOfDay()
            ];

        case 'this_week':
            return [
                'start' => $today->copy()->startOfWeek(),
                'end' => $today->copy()->endOfWeek()
            ];

        case 'last_week':
            return [
                'start' => $today->copy()->subWeek()->startOfWeek(),
                'end' => $today->copy()->subWeek()->endOfWeek()
            ];

        case 'this_month':
            return [
                'start' => $today->copy()->startOfMonth(),
                'end' => $today->copy()->endOfMonth()
            ];

        case 'last_month':
            return [
                'start' => $today->copy()->subMonth()->startOfMonth(),
                'end' => $today->copy()->subMonth()->endOfMonth()
            ];

        case 'this_year':
            return [
                'start' => $today->copy()->startOfYear(),
                'end' => $today->copy()->endOfYear()
            ];

        default: // custom
            return [
                'start' => Carbon::parse($request->get('start_date', $today->startOfMonth())),
                'end' => Carbon::parse($request->get('end_date', $today))
            ];
    }
}
/**
 * Reset profit report filters
 */
public function resetProfitReport(Request $request)
{
    if (!in_array($request->user()->role, ['admin', 'guru'])) {
        abort(403, 'AKSES DITOLAK.');
    }

    // Reset ke custom default (awal bulan - hari ini)
    return redirect()->route('supervisor.profit', [
        'period' => 'custom',
        'start_date' => now()->copy()->startOfMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
        'admin_id' => '',
    ]);
}

    /**
     * Update profitReport method untuk menggunakan filter periode
     */
    public function profitReport(Request $request)
{
    // Check authorization - only admin and teachers can access
    if (!in_array($request->user()->role, ['admin', 'guru'])) {
        abort(403, 'AKSES DITOLAK.');
    }

    try {
        // Jika tidak ada parameter apapun, set ke default (bulan ini)
       // Jika tidak ada parameter apapun, set ke default custom (awal bulan s/d hari ini)
if (!$request->hasAny(['period', 'start_date', 'end_date', 'admin_id'])) {
    $request->merge([
        'period' => 'custom',
        'start_date' => now()->copy()->startOfMonth()->format('Y-m-d'),
        'end_date' => now()->format('Y-m-d'),
    ]);
}


        // Gunakan method getDateRangeFromPeriod untuk handle filter
        $dateRange = $this->getDateRangeFromPeriod($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        $adminId = $request->get('admin_id');

        // Base query for orders - PENTING: Load semua relasi yang diperlukan
        $ordersQuery = Order::with([
            'items' => function($query) {
                $query->with('product');
            }, 
            'admin', 
            'customer'
        ])
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Filter by admin if specified
        if ($request->user()->role === 'admin') {
            $ordersQuery->where('admin_id', $request->user()->user_id);
        } elseif ($adminId) {
            $ordersQuery->where('admin_id', $adminId);
        }

        // Get orders with items
        $orders = $ordersQuery->orderBy('created_at', 'desc')->get();

        // Sisa kode seperti sebelumnya...
        $totalRevenue = $orders->sum('total_price');
        $totalOrders = $orders->count();
        
        $totalProfit = 0;
        $totalModalCost = 0;
        $productStats = [];
        $adminStats = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $itemRevenue = $item->subtotal;
                
                $modalPricePerUnit = 0;
                if (!is_null($item->modal_price) && $item->modal_price > 0) {
                    $modalPricePerUnit = $item->modal_price;
                } elseif ($item->product && !is_null($item->product->modal_price) && $item->product->modal_price > 0) {
                    $modalPricePerUnit = $item->product->modal_price;
                }
                
                $itemModalCost = $modalPricePerUnit * $item->quantity;
                $itemProfit = $itemRevenue - $itemModalCost;

                $totalModalCost += $itemModalCost;
                $totalProfit += $itemProfit;

                // Product statistics
                $productId = $item->product_id;
                if (!isset($productStats[$productId])) {
                    $productStats[$productId] = [
                        'product' => $item->product,
                        'quantity_sold' => 0,
                        'revenue' => 0,
                        'modal_cost' => 0,
                        'profit' => 0,
                        'profit_margin' => 0
                    ];
                }
                
                $productStats[$productId]['quantity_sold'] += $item->quantity;
                $productStats[$productId]['revenue'] += $itemRevenue;
                $productStats[$productId]['modal_cost'] += $itemModalCost;
                $productStats[$productId]['profit'] += $itemProfit;
                
                if ($productStats[$productId]['revenue'] > 0) {
                    $productStats[$productId]['profit_margin'] = ($productStats[$productId]['profit'] / $productStats[$productId]['revenue']) * 100;
                }

                // Admin/Seller statistics
                $sellerId = $order->admin_id;
                if (!isset($adminStats[$sellerId])) {
                    $adminStats[$sellerId] = [
                        'admin' => $order->admin,
                        'orders_count' => 0,
                        'revenue' => 0,
                        'modal_cost' => 0,
                        'profit' => 0,
                        'profit_margin' => 0
                    ];
                }
                
                $adminStats[$sellerId]['revenue'] += $itemRevenue;
                $adminStats[$sellerId]['modal_cost'] += $itemModalCost;
                $adminStats[$sellerId]['profit'] += $itemProfit;
            }
            
            // Count orders per admin
            $sellerId = $order->admin_id;
            if (isset($adminStats[$sellerId])) {
                if (!isset($adminStats[$sellerId]['counted_orders'])) {
                    $adminStats[$sellerId]['counted_orders'] = [];
                }
                if (!in_array($order->order_id, $adminStats[$sellerId]['counted_orders'])) {
                    $adminStats[$sellerId]['orders_count']++;
                    $adminStats[$sellerId]['counted_orders'][] = $order->order_id;
                }
            }
        }

        // Calculate profit margins for admin stats
        foreach ($adminStats as &$stat) {
            $stat['profit_margin'] = $stat['revenue'] > 0 
                ? ($stat['profit'] / $stat['revenue']) * 100 
                : 0;
            unset($stat['counted_orders']);
        }

        $productStats = collect($productStats)->sortByDesc('profit')->take(20);
        $adminStats = collect($adminStats)->sortByDesc('profit');

        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;
        $averageOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Daily sales data for chart
        $dailySales = $orders->groupBy(function($order) {
            return $order->created_at->format('Y-m-d');
        })->map(function($dayOrders) {
            $revenue = $dayOrders->sum('total_price');
            $profit = 0;
            
            foreach ($dayOrders as $order) {
                foreach ($order->items as $item) {
                    $modalPricePerUnit = 0;
                    
                    if (!is_null($item->modal_price) && $item->modal_price > 0) {
                        $modalPricePerUnit = $item->modal_price;
                    } elseif ($item->product && !is_null($item->product->modal_price) && $item->product->modal_price > 0) {
                        $modalPricePerUnit = $item->product->modal_price;
                    }
                    
                    $itemModalCost = $modalPricePerUnit * $item->quantity;
                    $profit += $item->subtotal - $itemModalCost;
                }
            }
            
            return [
                'revenue' => $revenue,
                'profit' => $profit,
                'orders' => $dayOrders->count()
            ];
        });

        $admins = User::where('role', 'admin')->get(['user_id', 'full_name']);

        $data = [
            'orders' => $orders,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_modal_cost' => $totalModalCost,
                'total_profit' => $totalProfit,
                'profit_margin' => $profitMargin,
                'total_orders' => $totalOrders,
                'average_order_value' => $averageOrderValue,
            ],
            'product_stats' => $productStats,
            'admin_stats' => $adminStats,
            'daily_sales' => $dailySales,
            'admins' => $admins,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'admin_id' => $adminId,
                'period' => $request->get('period', 'custom')
            ],
            // Tambahkan flag untuk mendeteksi apakah ada filter aktif
            'has_active_filters' => $request->hasAny(['period', 'start_date', 'end_date', 'admin_id']) && 
                                  !($request->get('period') == 'custom' && !$adminId)
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        return view('supervisor.profit', $data);

    } catch (\Exception $e) {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil laporan keuntungan',
                'error' => $e->getMessage()
            ], 500);
        }

        return back()->withErrors(['error' => 'Gagal mengambil laporan keuntungan: ' . $e->getMessage()]);
    }
}
 public function getProductStatsData(Request $request)
{
    $dateRange = $this->getDateRangeFromPeriod($request);
    $adminId = $request->get('admin_id');

    $query = DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
        ->join('products', 'order_items.product_id', '=', 'products.product_id')
        ->leftJoin('category', 'products.category_id', '=', 'category.category_id')
        ->where('orders.status', 'paid')
        ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
        ->select(
            'products.product_id',
            'products.name as product_name',
            'products.image as product_image', 
            'products.price as sell_price',
            'category.name as category_name',
            DB::raw('SUM(order_items.quantity) as quantity_sold'),
            DB::raw('SUM(order_items.subtotal) as revenue'),
            DB::raw('AVG(COALESCE(NULLIF(order_items.modal_price, 0), NULLIF(products.modal_price, 0), 0)) as modal_price_used'),
            DB::raw('SUM(order_items.quantity * COALESCE(NULLIF(order_items.modal_price, 0), NULLIF(products.modal_price, 0), 0)) as total_modal_cost')
        )
        ->groupBy(
            'products.product_id', 
            'products.name', 
            'products.image', 
            'products.price',
            'category.name'
        );

    if ($request->user()->role === 'admin') {
        $query->where('orders.admin_id', $request->user()->user_id);
    } elseif ($adminId) {
        $query->where('orders.admin_id', $adminId);
    }

    return DataTables::of($query)
        ->addColumn('profit', function($row) {
            return $row->revenue - $row->total_modal_cost;
        })
        ->addColumn('profit_margin', function($row) {
            return $row->revenue > 0 ? (($row->revenue - $row->total_modal_cost) / $row->revenue) * 100 : 0;
        })
        ->addColumn('product_info', function ($row) {
            $imageUrl = $row->product_image 
                ? asset('storage/' . $row->product_image) 
                : asset('assets/img/default-product.png');
            
            $categoryName = $row->category_name ?? 'Tanpa Kategori';
            
            // Improved styling with better spacing and alignment
            return '<div class="flex items-center space-x-3 py-2">
                        <div class="flex-shrink-0">
                            <img class="h-12 w-12 rounded-lg object-cover border border-gray-200 shadow-sm" 
                                 src="' . $imageUrl . '" 
                                 alt="' . e($row->product_name) . '" 
                                 onerror="this.src=\'/assets/img/default-product.png\'">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-gray-900 truncate max-w-xs" title="' . e($row->product_name) . '">
                                ' . e($row->product_name) . '
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-gray-800">
                                    ' . e($categoryName) . '
                                </span>
                            </div>
                        </div>
                    </div>';
        })
        ->editColumn('quantity_sold', function($row) {
            // Better alignment and styling for quantity
            return '<div class="text-center py-2">
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 min-w-[50px]">
                            ' . number_format($row->quantity_sold, 0, ',', '.') . '
                        </span>
                    </div>';
        })
        ->editColumn('sell_price', function($row) {
            // Consistent currency formatting
            return '<div class="text-right py-2">
                        <span class="text-sm font-medium text-gray-900">
                            Rp ' . number_format($row->sell_price, 0, ',', '.') . '
                        </span>
                    </div>';
        })
        ->editColumn('modal_price_used', function($row) {
            return '<div class="text-right py-2">
                        <span class="text-sm font-medium text-gray-700">
                            Rp ' . number_format($row->modal_price_used, 0, ',', '.') . '
                        </span>
                    </div>';
        })
        ->editColumn('revenue', function($row) {
            return '<div class="text-right py-2">
                        <span class="text-sm font-semibold text-green-700">
                            Rp ' . number_format($row->revenue, 0, ',', '.') . '
                        </span>
                    </div>';
        })
        ->editColumn('total_modal_cost', function($row) {
            return '<div class="text-right py-2">
                        <span class="text-sm font-medium text-red-600">
                            Rp ' . number_format($row->total_modal_cost, 0, ',', '.') . '
                        </span>
                    </div>';
        })
        ->editColumn('profit', function($row) {
            $profit = $row->revenue - $row->total_modal_cost;
            $class = $profit >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50';
            $icon = $profit >= 0 ? '↑' : '↓';
            
            return '<div class="text-right py-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-sm font-bold ' . $class . '">
                            <span class="mr-1">' . $icon . '</span>
                            Rp ' . number_format($profit, 0, ',', '.') . '
                        </span>
                    </div>';
        })
        ->editColumn('profit_margin', function ($row) {
            $margin = $row->revenue > 0 ? (($row->revenue - $row->total_modal_cost) / $row->revenue) * 100 : 0;
            
            if ($margin >= 30) {
                $class = 'bg-green-100 text-green-800 border-green-200';
            } elseif ($margin >= 20) {
                $class = 'bg-blue-100 text-blue-800 border-blue-200';
            } elseif ($margin >= 10) {
                $class = 'bg-yellow-100 text-yellow-800 border-yellow-200';
            } else {
                $class = 'bg-red-100 text-red-800 border-red-200';
            }
            
            return '<div class="text-center py-2">
                        <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-bold border ' . $class . ' min-w-[60px]">
                            ' . number_format($margin, 1) . '%
                        </span>
                    </div>';
        })
        ->orderColumn('profit', function ($query, $order) {
            $query->orderByRaw('(revenue - total_modal_cost) ' . $order);
        })
        ->filterColumn('product_name', function($query, $keyword) {
            $query->where('products.name', 'like', "%{$keyword}%");
        })
        ->rawColumns(['product_info', 'quantity_sold', 'sell_price', 'modal_price_used', 'revenue', 'total_modal_cost', 'profit', 'profit_margin'])
        ->make(true);
}
    /**
     * Provides server-side data for the Admin/Seller Stats DataTable.
     * -- COMPLETELY REWRITTEN & CORRECTED --
     */
     public function getAdminStatsData(Request $request)
{
    $dateRange = $this->getDateRangeFromPeriod($request);
    $adminId = $request->get('admin_id');
    
    // Use DB query builder for better control
    $query = DB::table('users')
        ->join('orders', 'users.user_id', '=', 'orders.admin_id')
        ->join('order_items', 'orders.order_id', '=', 'order_items.order_id')
        ->join('products', 'order_items.product_id', '=', 'products.product_id')
        ->where('users.role', 'admin')
        ->where('orders.status', 'paid')
        ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
        ->select(
            'users.user_id',
            'users.full_name',
            'users.student_id',
            DB::raw('COUNT(DISTINCT orders.order_id) as orders_count'),
            DB::raw('SUM(order_items.subtotal) as revenue'),
            DB::raw('SUM(order_items.quantity * COALESCE(NULLIF(order_items.modal_price, 0), NULLIF(products.modal_price, 0), 0)) as modal_cost')
        )
        ->groupBy('users.user_id', 'users.full_name', 'users.student_id');

    // Apply admin filter if specified
    if ($adminId) {
        $query->where('orders.admin_id', $adminId);
    }

    return DataTables::of($query)
        ->addColumn('profit', function($row) {
            return $row->revenue - $row->modal_cost;
        })
        ->addColumn('profit_margin', function($row) {
            return $row->revenue > 0 ? (($row->revenue - $row->modal_cost) / $row->revenue) * 100 : 0;
        })
        ->addColumn('seller_info', function ($row) {
            $initials = strtoupper(substr($row->full_name, 0, 2));
            return '<div class="flex items-center">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                            <span class="text-sm font-medium text-blue-600">' . e($initials) . '</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">' . e($row->full_name) . '</div>
                            <div class="text-sm text-gray-500">' . e($row->student_id) . '</div>
                        </div>
                    </div>';
        })
        ->editColumn('revenue', function($row) {
            return 'Rp ' . number_format($row->revenue, 0, ',', '.');
        })
        ->editColumn('modal_cost', function($row) {
            return 'Rp ' . number_format($row->modal_cost, 0, ',', '.');
        })
        ->editColumn('profit', function($row) {
            $profit = $row->revenue - $row->modal_cost;
            $class = $profit >= 0 ? 'text-green-600' : 'text-red-600';
            return '<span class="'.$class.' font-medium">Rp ' . number_format($profit, 0, ',', '.') . '</span>';
        })
        ->editColumn('profit_margin', function ($row) {
            $margin = $row->revenue > 0 ? (($row->revenue - $row->modal_cost) / $row->revenue) * 100 : 0;
            $class = $margin >= 20 ? 'bg-green-100 text-green-800' : 
                    ($margin >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
            return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . $class . '">' 
                   . number_format($margin, 1) . '%</span>';
        })
        ->orderColumn('profit', function ($query, $order) {
            $query->orderByRaw('(revenue - modal_cost) ' . $order);
        })
        ->filterColumn('full_name', function($query, $keyword) {
            $query->where('users.full_name', 'like', "%{$keyword}%");
        })
        ->rawColumns(['seller_info', 'profit', 'profit_margin'])
        ->make(true);
}
    /**
     * Export profit report to Excel/CSV
     */
    public function exportProfitReport(Request $request)
    {
        // Check authorization
        if (!in_array($request->user()->role, ['admin', 'guru'])) {
            abort(403, 'AKSES DITOLAK.');
        }

        // This would integrate with Laravel Excel package
        // For now, return JSON data that can be processed
        $data = $this->profitReport($request);
        
        return response()->json($data);
    }

    /**
     * Get profit summary dashboard data
     */
    public function profitSummary(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'guru'])) {
            abort(403, 'AKSES DITOLAK.');
        }

        try {
            $today = now();
            $periods = [
                'today' => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
                'yesterday' => [$today->copy()->subDay()->startOfDay(), $today->copy()->subDay()->endOfDay()],
                'this_week' => [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()],
                'this_month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
                'last_month' => [$today->copy()->subMonth()->startOfMonth(), $today->copy()->subMonth()->endOfMonth()],
            ];

            $summary = [];
            foreach ($periods as $period => $dates) {
                $orders = Order::with('items.product')
                    ->where('status', 'paid')
                    ->whereBetween('created_at', $dates);

                if ($request->user()->role === 'admin') {
                    $orders->where('admin_id', $request->user()->user_id);
                }

                $orders = $orders->get();
                
                $revenue = $orders->sum('total_price');
                $profit = $orders->sum(function($order) {
                    return $order->items->sum(function($item) {
                        $modalCost = ($item->modal_price ?: $item->product->modal_price ?? 0) * $item->quantity;
                        return $item->subtotal - $modalCost;
                    });
                });

                $summary[$period] = [
                    'revenue' => $revenue,
                    'profit' => $profit,
                    'orders' => $orders->count(),
                    'profit_margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $summary
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil ringkasan keuntungan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function showProfitReport(Request $request)
    {
        // 1. Ambil input filter
        // Dengan ini:
$dateRange = $this->getDateRangeFromPeriod($request);
$startDate = $dateRange['start'];
$endDate = $dateRange['end'];
        $adminId = $request->input('admin_id');

        // 2. Query dasar untuk item pesanan yang sudah lunas
        $query = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate->endOfDay()])
            // Urutkan dari yang terbaru (sesuai permintaan)
            ->orderBy('orders.created_at', 'desc');

        // Filter berdasarkan penjual jika ada
        if ($adminId) {
            $query->where('orders.admin_id', $adminId);
        }

        // 3. Ambil statistik produk dengan PAGINASI
        $product_stats = $query->select(
                'products.product_id',
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue'),
                DB::raw('SUM(order_items.quantity * order_items.modal_price) as total_modal_cost')
            )
            ->groupBy('products.product_id', 'products.name')
            // Menggunakan paginate() untuk membatasi 10 item per halaman
            ->paginate(10);

        // 4. Kalkulasi Ringkasan (Summary)
        $summaryQuery = Order::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$startDate, $endDate->endOfDay()]);

        if ($adminId) {
            $summaryQuery->where('admin_id', $adminId);
        }
        
        $paidOrders = $summaryQuery->with('items')->get();
        $totalRevenue = $paidOrders->sum('total_price');
        $totalModalCost = $paidOrders->sum(function($order) {
            return $order->items->sum(function($item) {
                return $item->quantity * $item->modal_price;
            });
        });
        $totalProfit = $totalRevenue - $totalModalCost;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        $summary = [
            'total_revenue' => $totalRevenue,
            'total_modal_cost' => $totalModalCost,
            'total_profit' => $totalProfit,
            'profit_margin' => $profitMargin,
        ];
        
        // 5. Siapkan data untuk grafik
        $chartData = $this->prepareChartData($startDate, $endDate, $adminId);
        
        // 6. Ambil daftar admin (untuk filter)
        $admins = User::where('role', 'admin')->get();

        return view('profit.blade.php', [ // Ganti dengan path view Anda yang benar
            'product_stats' => $product_stats,
            'summary' => $summary,
            'chart_data' => $chartData,
            'admins' => $admins,
        ]);
    }

    /**
     * Helper untuk menyiapkan data grafik.
     */
    private function getProfitData(Request $request)
{
    $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
    $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now()->endOfMonth();
    $adminId = $request->input('admin_id');

    // 1. Ambil statistik produk - PERBAIKAN NAMA TABEL
    $product_stats_query = DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
        ->join('products', 'order_items.product_id', '=', 'products.product_id')
        ->leftJoin('category', 'products.category_id', '=', 'category.category_id') // UBAH: categories -> category, inner -> left
        ->where('orders.status', 'paid')
        ->whereBetween('orders.created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
        ->select(
            'products.name as product_name',
            'category.name as category_name', // UBAH: categories -> category
            DB::raw('SUM(order_items.quantity) as total_quantity'),
            DB::raw('SUM(order_items.quantity * COALESCE(NULLIF(order_items.modal_price, 0), NULLIF(products.modal_price, 0), 0)) as total_modal'), // PERBAIKAN MODAL PRICE
            DB::raw('SUM(order_items.subtotal) as total_revenue'), // UBAH: quantity * price -> subtotal
            DB::raw('SUM(order_items.subtotal - (order_items.quantity * COALESCE(NULLIF(order_items.modal_price, 0), NULLIF(products.modal_price, 0), 0))) as total_profit') // PERBAIKAN PROFIT
        )
        ->groupBy('products.product_id', 'products.name', 'category.name') // UBAH: categories -> category
        ->orderBy('total_profit', 'desc');

    if ($adminId) {
        $product_stats_query->where('orders.admin_id', $adminId);
    }
    $product_stats = $product_stats_query->get();

    // 2. Hitung ringkasan total
    $summary = [
        'total_quantity' => $product_stats->sum('total_quantity'),
        'total_modal' => $product_stats->sum('total_modal'),
        'total_revenue' => $product_stats->sum('total_revenue'),
        'total_profit' => $product_stats->sum('total_profit'),
    ];
    
    return compact('product_stats', 'summary', 'startDate', 'endDate', 'adminId');
}

    /**
     * Menampilkan halaman laporan keuntungan.
     */
    public function profit(Request $request)
    {
        $data = $this->getProfitData($request);
        
        // Data untuk chart
        $chartData = $this->prepareChartData($data['startDate'], $data['endDate'], $data['adminId']);
        
        // Ambil daftar admin (untuk filter)
        $admins = User::where('role', 'admin')->get();

        return view('profit.profit', array_merge($data, [
            'chart_data' => $chartData,
            'admins' => $admins,
        ]));
    }

    /**
     * Menangani permintaan ekspor ke Excel.
     */
    public function exportExcel(Request $request)
    {
        $data = $this->getProfitData($request);
        return Excel::download(new ProfitExport($data['product_stats'], $data['summary']), 'laporan-keuntungan.xlsx');
    }

    /**
     * Menampilkan halaman untuk dicetak.
     */
    public function print(Request $request)
    {
        $data = $this->getProfitData($request);
        return view('profit.print', $data);
    }

    /**
     * Helper untuk menyiapkan data grafik.
     */
    private function prepareChartData(Carbon $startDate, Carbon $endDate, $adminId = null)
    {
        $period = CarbonPeriod::create($startDate, '1 day', $endDate);
        $labels = [];
        $revenues = [];
        $profits = [];

        foreach ($period as $date) {
            $labels[] = $date->format('d M');
            $query = Order::query()
                ->where('status', 'paid')
                ->whereDate('created_at', $date);

            if ($adminId) {
                $query->where('admin_id', $adminId);
            }

            $dailyOrders = $query->with('items')->get();
            $dailyRevenue = $dailyOrders->sum('total_price');
            $dailyModal = $dailyOrders->sum(function($order) {
                return $order->items->sum(fn($item) => $item->quantity * $item->modal_price);
            });

            $revenues[] = $dailyRevenue;
            $profits[] = $dailyRevenue - $dailyModal;
        }

        return [
            'labels' => $labels,
            'revenues' => $revenues,
            'profits' => $profits,
        ];
    }
}