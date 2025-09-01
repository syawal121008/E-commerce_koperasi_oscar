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
use Carbon\CarbonPeriod;
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

    // Filter berdasarkan role user
    if ($user->role === 'customer') {
        $query->where('customer_id', $user->user_id);
    } elseif ($user->role === 'admin') {
        $query->where('admin_id', $user->user_id);
    }

    // Filter pencarian nama pelanggan (hanya untuk admin/guru)
    if ($request->filled('search') && $user->role !== 'customer') {
        $searchTerm = $request->search;
        $query->whereHas('customer', function ($q) use ($searchTerm) {
            $q->where('full_name', 'like', '%' . $searchTerm . '%')
              ->orWhere('student_id', 'like', '%' . $searchTerm . '%')
              ->orWhere('email', 'like', '%' . $searchTerm . '%');
        });
    }

    // Filter berdasarkan status
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // Filter berdasarkan tanggal (opsional - tambahan fitur)
    if ($request->filled('date_from')) {
        $query->whereDate('created_at', '>=', $request->date_from);
    }

    if ($request->filled('date_to')) {
        $query->whereDate('created_at', '<=', $request->date_to);
    }

    // Urutkan berdasarkan tanggal terbaru
    $orders = $query->orderBy('created_at', 'desc')->paginate(10);

    // Preserve query parameters dalam pagination
    $orders->appends($request->query());

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'data' => $orders,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ]
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
        $admin = User::findOrFail($adminId);
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

        // Determine order status based on payment method
        $orderStatus = ($request->payment_method === 'cash' || ($request->payment_method === 'mixed' && $cashAmount > 0)) ? 'pending' : 'paid';

        // Create order
        $order = Order::create([
            'customer_id' => $customerId,
            'admin_id' => $adminId,
            'total_price' => $totalAmount,
            'status' => $orderStatus,
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

        }

        // Process balance payment if used
        if ($balanceUsed > 0) {
            if (!$customer->deductBalance($balanceUsed)) {
                throw new \Exception('Gagal mengurangi saldo');
            }

            // PERBAIKAN: Status transaksi saldo disesuaikan dengan status order
            $balanceTransactionStatus = ($orderStatus === 'paid') ? 'paid' : 'completed';

            // Create transaction record for customer (balance deduction)
            Transaction::create([
                'user_id' => $customerId,
                'type' => 'payment',
                'amount' => $balanceUsed,
                'status' => $balanceTransactionStatus, // Sesuaikan dengan status order
                'related_id' => $order->order_id,
                'description' => 'Pembayaran pesanan #' . $order->order_id . ' (saldo)'
            ]);

            // Add balance to admin
            $admin->addBalance($balanceUsed);

            // Create transaction record for admin (balance income)
            Transaction::create([
                'user_id' => $admin->user_id,
                'type' => 'income',
                'amount' => $balanceUsed,
                'status' => $balanceTransactionStatus, // Sesuaikan dengan status order
                'related_id' => $order->order_id,
                'description' => 'Penjualan produk dari pesanan #' . $order->order_id . ' (saldo)'
            ]);
        }

        // Process cash payment if used
        if ($cashAmount > 0) {
            // PERBAIKAN: Status transaksi cash disesuaikan dengan status order
            $cashTransactionStatus = ($orderStatus === 'pending') ? 'pending' : 'paid';

            // Create transaction record for customer (cash payment)
            Transaction::create([
                'user_id' => $customerId,
                'type' => 'payment',
                'amount' => $cashAmount,
                'status' => $cashTransactionStatus, // Sesuaikan dengan status order
                'related_id' => $order->order_id,
                'description' => 'Pembayaran pesanan #' . $order->order_id . ' (cash)'
            ]);

            // Create transaction record for admin (cash income)
            Transaction::create([
                'user_id' => $admin->user_id,
                'type' => 'income',
                'amount' => $cashAmount,
                'status' => $cashTransactionStatus, // Sesuaikan dengan status order
                'related_id' => $order->order_id,
                'description' => 'Penjualan produk dari pesanan #' . $order->order_id . ' (cash)'
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

        // Redirect logic based on payment method
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

    public function completeOrder(Order $order, Request $request)
    {
        // PERUBAHAN LOGIKA: Memperbolehkan penyelesaian dari status 'paid' atau 'pending'
        if (!in_array($order->status, [Order::STATUS_PAID, Order::STATUS_PENDING])) {
            $message = 'Hanya pesanan dengan status Dibayar atau Tertunda yang dapat diselesaikan.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }
            return back()->with('error', $message);
        }

        DB::beginTransaction();
        try {
            // 1. Ubah status pesanan (Order) menjadi 'completed'
            $order->update(['status' => Order::STATUS_COMPLETED]);

            // 2. Cari transaksi pembayaran yang terkait dengan pesanan ini
            $transaction = Transaction::where('related_id', $order->order_id)
                                      ->where('type', Transaction::TYPE_PAYMENT)
                                      ->first();

            // 3. Jika transaksi ditemukan, ubah statusnya menjadi 'success'
            if ($transaction) {
                // PERUBAHAN KUNCI: Status transaksi diubah menjadi 'success'
                $transaction->update(['status' => Transaction::STATUS_COMPLETED]);
            }

            DB::commit();

            $message = 'Pesanan berhasil diselesaikan.';
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message]);
            }
            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $message = 'Terjadi kesalahan saat menyelesaikan pesanan.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return back()->with('error', $message);
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
                    'status' => 'completed',
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

            // Restore product stock and reactivate if needed
            foreach ($order->items as $item) {
                $this->updateProductStockAndStatus($item->product, $item->quantity);
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
                    'status' => 'completed',
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
                'status' => 'completed',
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

            $order->status = 'completed';
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
                    $this->updateProductStockAndStatus($item->product, -$item->quantity);
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
                        'status' => 'completed',
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
                        'status' => 'completed',
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

    try {
        DB::beginTransaction();

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // 3. Update Status Order
        $order->status = $newStatus;
        $order->save();

        // 4. PERBAIKAN: Update status transaksi terkait
        if ($oldStatus !== $newStatus) {
            // Cari semua transaksi yang terkait dengan order ini
            $relatedTransactions = Transaction::where('related_id', $order->order_id)->get();

            foreach ($relatedTransactions as $transaction) {
                // Tentukan status transaksi berdasarkan status order baru
                $transactionStatus = $this->determineTransactionStatus($newStatus, $transaction->type);
                
                // Update status transaksi
                $transaction->update(['status' => $transactionStatus]);
            }
        }

        DB::commit();

        // 5. Redirect kembali dengan pesan sukses
        return back()->with('success', 'Status pesanan #' . $order->order_id . ' berhasil diubah menjadi ' . ucfirst($newStatus));

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Gagal mengubah status pesanan: ' . $e->getMessage());
    }
}

/**
 * Tentukan status transaksi berdasarkan status order dan tipe transaksi
 */
private function determineTransactionStatus($orderStatus, $transactionType)
{
    // Mapping status order ke status transaksi
    switch ($orderStatus) {
        case 'pending':
            return 'pending';
        case 'paid':
            return 'paid'; // atau 'paid' tergantung preferensi
        case 'completed':
            return 'completed';
        case 'cancelled':
            return 'cancelled';
        default:
            return 'pending';
    }
}

    /**
     * Show POS interface for admin
     * PERBAIKAN: Query produk yang lebih fleksibel
     */
    public function pos(Request $request)
    {
        // Check if user is admin
        if ($request->user()->role !== 'admin') {
            abort(403, 'AKSES DITOLAK.');
        }

        // PERBAIKAN: Ambil semua produk admin (aktif dan non-aktif) yang punya stok
        // Sistem akan otomatis mengaktifkan produk yang stoknya > 0
        $products = Product::with(['category'])
            ->where('admin_id', $request->user()->user_id)
            ->where('stock', '>', 0) // Hanya produk yang ada stoknya
            ->orderBy('name')
            ->get();

        // PERBAIKAN: Auto-activate products yang punya stok tapi tidak aktif
        Product::where('admin_id', $request->user()->user_id)
            ->where('stock', '>', 0)
            ->where('is_active', false)
            ->update(['is_active' => true]);

        // Get active categories that have products from this admin
        $categories = Category::whereHas('products', function($query) use ($request) {
            $query->where('admin_id', $request->user()->user_id)
                  ->where('stock', '>', 0); // Ubah dari is_active ke stock check
        })->where('is_active', true)->get();

        return view('admin.pos', compact('products', 'categories'));
    }

    /**
     * Store POS order - FIXED VERSION
     * PERBAIKAN: Perbaiki logika stock management
     */
    public function storePOS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|uuid|exists:users,user_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:balance,cash',
            'total_amount' => 'required|numeric|min:0',
            'table_number' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $admin = $request->user();
            $customer = User::findOrFail($request->customer_id);
            $totalAmount = $request->total_amount;

            // Validate payment method and customer balance
            if ($request->payment_method === 'balance') {
                if ($customer->balance < $totalAmount) {
                    throw new \Exception('Saldo customer tidak mencukupi');
                }
            }

            // Set status berdasarkan metode pembayaran
            $orderStatus = $request->payment_method === 'balance' ? 'completed' : 'completed';

            // Create order
            $order = Order::create([
                'customer_id' => $customer->user_id,
                'admin_id' => $admin->user_id,
                'total_price' => $totalAmount,
                'status' => $orderStatus,
                'payment_method' => $request->payment_method,
                'balance_used' => $request->payment_method === 'balance' ? $totalAmount : 0,
                'cash_amount' => $request->payment_method === 'cash' ? $totalAmount : 0,
                'notes' => ($request->table_number ? "Table {$request->table_number} - " : '') . ($request->notes ?? 'POS Order')
            ]);

            $calculatedTotal = 0;

            // Create order items and validate stock
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Validate product belongs to admin
                if ($product->admin_id !== $admin->user_id) {
                    throw new \Exception("Produk {$product->name} bukan milik toko Anda");
                }

                // Validate stock
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stok tidak mencukupi untuk produk: {$product->name}. Stok tersedia: {$product->stock}");
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

            }

            // Validate calculated total (include 10% tax)
            $expectedTotal = $calculatedTotal * 1.1; // Add 10% tax
            if (abs($expectedTotal - $totalAmount) > 0.01) {
                throw new \Exception('Total perhitungan tidak sesuai');
            }

            // Process payment
            if ($request->payment_method === 'balance') {
                // Deduct from customer
                $customer->deductBalance($totalAmount);

                // Create transaction record for customer
                Transaction::create([
                    'user_id' => $customer->user_id,
                    'type' => 'payment',
                    'amount' => $totalAmount,
                    'status' => 'completed',
                    'related_id' => $order->order_id,
                    'description' => 'Pembayaran POS pesanan #' . $order->order_id
                ]);

                // Add to admin balance
                $admin->addBalance($totalAmount);

                // Create transaction record for admin
                Transaction::create([
                    'user_id' => $admin->user_id,
                    'type' => 'income',
                    'amount' => $totalAmount,
                    'status' => 'completed',
                    'related_id' => $order->order_id,
                    'description' => 'Penjualan POS dari pesanan #' . $order->order_id
                ]);
            }

            DB::commit();

            $order->load(['items.product', 'customer']);

            // Pesan sukses yang berbeda berdasarkan status
            $successMessage = $request->payment_method === 'balance' 
                ? 'Pembayaran berhasil diproses dan pesanan telah selesai'
                : 'Pembayaran berhasil diproses';

            return response()->json([
                'success' => true,
                'message' => $successMessage,
                'data' => [
                    'order' => $order,
                    'receipt_url' => route('orders.receipt.show', $order->order_id),
                    'customer_new_balance' => $customer->balance,
                    'order_status' => $orderStatus,
                    // PERBAIKAN: Kirim info produk yang diupdate untuk refresh UI
                    'updated_products' => $order->items->map(function($item) {
                        return [
                            'product_id' => $item->product_id,
                            'new_stock' => $item->product->stock,
                            'is_active' => $item->product->is_active
                        ];
                    })
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran',
                'error' => $e->getMessage()
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
     * PERBAIKAN: Tambahkan auto-activation logic
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

            // PERBAIKAN: Auto-activate jika produk punya stok tapi tidak aktif
            if ($product->stock > 0 && !$product->is_active) {
                $product->update(['is_active' => true]);
                $product->refresh();
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
     * PERBAIKAN: Method baru untuk refresh produk di POS
     */
    public function refreshPOSProducts(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak diizinkan'
            ], 403);
        }

        try {
            // Auto-activate products yang punya stok tapi tidak aktif
            Product::where('admin_id', $request->user()->user_id)
                ->where('stock', '>', 0)
                ->where('is_active', false)
                ->update(['is_active' => true]);

            // Ambil semua produk yang available untuk POS
            $products = Product::with(['category'])
                ->where('admin_id', $request->user()->user_id)
                ->where('stock', '>', 0)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'products' => $products->map(function($product) {
                        return [
                            'product_id' => $product->product_id,
                            'name' => $product->name,
                            'price' => $product->price,
                            'stock' => $product->stock,
                            'is_active' => $product->is_active,
                            'category_id' => $product->category_id,
                            'image_url' => $product->image_url,
                            'category_name' => $product->category->name ?? 'Tanpa Kategori'
                        ];
                    })
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal refresh produk',
                'error' => $e->getMessage()
            ], 500);
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
    private function getDateRangeFromPeriod(Request $request): array
{
    $period = $request->get('period', 'today');
    $startDate = $request->get('start_date');
    $endDate = $request->get('end_date');

    switch ($period) {
        case 'yesterday':
            $start = Carbon::yesterday()->startOfDay();
            $end = Carbon::yesterday()->endOfDay();
            break;
        case 'this_week':
            $start = Carbon::now()->startOfWeek();
            $end = Carbon::now()->endOfWeek();
            break;
        case 'last_week':
            $start = Carbon::now()->subWeek()->startOfWeek();
            $end = Carbon::now()->subWeek()->endOfWeek();
            break;
        case 'this_month':
            $start = Carbon::now()->startOfMonth();
            $end = Carbon::now()->endOfMonth();
            break;
        case 'last_month':
            $start = Carbon::now()->subMonth()->startOfMonth();
            $end = Carbon::now()->subMonth()->endOfMonth();
            break;
        case 'this_year':
            $start = Carbon::now()->startOfYear();
            $end = Carbon::now()->endOfYear();
            break;
        case 'custom':
            if ($startDate && $endDate) {
                $start = Carbon::parse($startDate)->startOfDay();
                $end = Carbon::parse($endDate)->endOfDay();
            } else {
                // Default to today if custom dates not provided
                $start = Carbon::today()->startOfDay();
                $end = Carbon::today()->endOfDay();
            }
            break;
        case 'today':
        default:
            $start = Carbon::today()->startOfDay();
            $end = Carbon::today()->endOfDay();
            break;
    }

    return ['start' => $start, 'end' => $end];
}

    /**
     * Reset profit report filters
     */
    public function resetProfitReport(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'guru'])) {
            abort(403, 'AKSES DITOLAK.');
        }

        // FIXED: Reset ke today (hari ini) instead of custom
        return redirect()->route('supervisor.profit', [
            'period' => 'today',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
            'admin_id' => '',
        ]);
    }

    /**
     * Update profitReport method untuk menggunakan filter periode
     */
   public function profitReport(Request $request)
{
    // Check authorization
    if (!in_array($request->user()->role, ['admin', 'guru'])) {
        abort(403, 'AKSES DITOLAK.');
    }

    try {
        // FIXED: Default filter handling
        $period = $request->get('period', 'today');
        $adminId = $request->get('admin_id');

        // Get date range
        $dateRange = $this->getDateRangeFromPeriod($request);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];

        // FIXED: Base query dengan relasi yang benar
        $ordersQuery = Order::with([
            'items.product.category',
            'admin',
            'customer'
        ])
        ->whereIn('status', ['paid', 'completed'])
        ->whereBetween('created_at', [$startDate, $endDate]);

        // Filter by admin role
        if ($request->user()->role === 'admin') {
            $ordersQuery->where('admin_id', $request->user()->user_id);
        } elseif ($adminId) {
            $ordersQuery->where('admin_id', $adminId);
        }

        $orders = $ordersQuery->get();

        // FIXED: Calculate summary dengan benar
        $totalRevenue = $orders->sum('total_price');
        $totalOrders = $orders->count();
        $totalModalCost = 0;
        $totalProfit = 0;

        // Calculate modal cost and profit properly
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                // Use modal_price from order_items first, then product
                $modalPrice = $item->modal_price ?? $item->product->modal_price ?? 0;
                $itemModalCost = $modalPrice * $item->quantity;
                $totalModalCost += $itemModalCost;
            }
        }

        $totalProfit = $totalRevenue - $totalModalCost;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // FIXED: Product statistics
        $productStats = collect();
        $productData = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $productId = $item->product_id;
                
                if (!isset($productData[$productId])) {
                    $productData[$productId] = [
                        'product' => $item->product,
                        'quantity_sold' => 0,
                        'revenue' => 0,
                        'modal_cost' => 0,
                        'profit' => 0,
                        'profit_margin' => 0
                    ];
                }

                $modalPrice = $item->modal_price ?? $item->product->modal_price ?? 0;
                $itemModalCost = $modalPrice * $item->quantity;

                $productData[$productId]['quantity_sold'] += $item->quantity;
                $productData[$productId]['revenue'] += $item->subtotal;
                $productData[$productId]['modal_cost'] += $itemModalCost;
                $productData[$productId]['profit'] += ($item->subtotal - $itemModalCost);
            }
        }

        // Calculate profit margins
        foreach ($productData as &$data) {
            $data['profit_margin'] = $data['revenue'] > 0 
                ? ($data['profit'] / $data['revenue']) * 100 
                : 0;
        }

        $productStats = collect($productData)->sortByDesc('profit');

        // FIXED: Admin statistics
        $adminStats = collect();
        $adminData = [];

        foreach ($orders as $order) {
            $adminId = $order->admin_id;
            
            if (!isset($adminData[$adminId])) {
                $adminData[$adminId] = [
                    'admin' => $order->admin,
                    'orders_count' => 0,
                    'revenue' => 0,
                    'modal_cost' => 0,
                    'profit' => 0,
                    'profit_margin' => 0,
                    'processed_orders' => []
                ];
            }

            // Count unique orders
            if (!in_array($order->order_id, $adminData[$adminId]['processed_orders'])) {
                $adminData[$adminId]['orders_count']++;
                $adminData[$adminId]['processed_orders'][] = $order->order_id;
            }

            foreach ($order->items as $item) {
                $modalPrice = $item->modal_price ?? $item->product->modal_price ?? 0;
                $itemModalCost = $modalPrice * $item->quantity;

                $adminData[$adminId]['revenue'] += $item->subtotal;
                $adminData[$adminId]['modal_cost'] += $itemModalCost;
                $adminData[$adminId]['profit'] += ($item->subtotal - $itemModalCost);
            }
        }

        // Calculate admin profit margins
        foreach ($adminData as &$data) {
            $data['profit_margin'] = $data['revenue'] > 0 
                ? ($data['profit'] / $data['revenue']) * 100 
                : 0;
            unset($data['processed_orders']); // Remove helper array
        }

        $adminStats = collect($adminData)->sortByDesc('profit');

        // FIXED: Daily sales untuk chart
        $dailySales = collect();
        $period = CarbonPeriod::create($startDate->copy()->startOfDay(), '1 day', $endDate->copy()->endOfDay());

        foreach ($period as $date) {
            $dateString = $date->format('M d');
            $dayOrders = $orders->filter(function($order) use ($date) {
                return $order->created_at->format('Y-m-d') === $date->format('Y-m-d');
            });

            $dayRevenue = $dayOrders->sum('total_price');
            $dayModalCost = 0;

            foreach ($dayOrders as $order) {
                foreach ($order->items as $item) {
                    $modalPrice = $item->modal_price ?? $item->product->modal_price ?? 0;
                    $dayModalCost += $modalPrice * $item->quantity;
                }
            }

            $dayProfit = $dayRevenue - $dayModalCost;

            $dailySales->put($dateString, [
                'revenue' => $dayRevenue,
                'profit' => $dayProfit,
                'orders' => $dayOrders->count()
            ]);
        }

        // Get all admins for filter
        $admins = User::where('role', 'admin')->get(['user_id', 'full_name']);

        $data = [
            'orders' => $orders,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_modal_cost' => $totalModalCost,
                'total_profit' => $totalProfit,
                'profit_margin' => $profitMargin,
                'total_orders' => $totalOrders,
                'average_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
            ],
            'product_stats' => $productStats,
            'admin_stats' => $adminStats,
            'daily_sales' => $dailySales,
            'admins' => $admins,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'admin_id' => $request->get('admin_id'),
                'period' => $period
            ]
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

    // ... method lain di dalam controller, seperti getDateRangeFromPeriod() ...
    
    /**
     * Helper method to get date range from request.
     *
     * @param Request $request
     * @return array
     */

    
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
    try {
        $dateRange = $this->getDateRangeFromPeriod($request);
        $adminId = $request->get('admin_id');
        
        $query = DB::table('users')
            ->join('orders', 'users.user_id', '=', 'orders.admin_id')
            ->join('order_items', 'orders.order_id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->where('users.role', 'admin')
            ->whereIn('orders.status', ['paid', 'completed'])
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
                return '<div class="text-right py-2">
                            <span class="text-sm font-semibold text-green-700">
                                Rp ' . number_format($row->revenue, 0, ',', '.') . '
                            </span>
                        </div>';
            })
            ->editColumn('modal_cost', function($row) {
                return '<div class="text-right py-2">
                            <span class="text-sm font-medium text-red-600">
                                Rp ' . number_format($row->modal_cost, 0, ',', '.') . '
                            </span>
                        </div>';
            })
            ->editColumn('profit', function($row) {
                $profit = $row->revenue - $row->modal_cost;
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
                $margin = $row->revenue > 0 ? (($row->revenue - $row->modal_cost) / $row->revenue) * 100 : 0;
                
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
                $query->orderByRaw('(SUM(order_items.subtotal) - SUM(order_items.quantity * COALESCE(NULLIF(order_items.modal_price, 0), NULLIF(products.modal_price, 0), 0))) ' . $order);
            })
            ->filterColumn('full_name', function($query, $keyword) {
                $query->where('users.full_name', 'like', "%{$keyword}%");
            })
            ->rawColumns(['seller_info', 'revenue', 'modal_cost', 'profit', 'profit_margin'])
            ->make(true);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error loading admin stats: ' . $e->getMessage()
        ], 500);
    }
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
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->endOfDay() : Carbon::now()->endOfDay();
        $categoryId = $request->input('category_id');

        $query = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.product_id')
            ->join('category', 'products.category_id', '=', 'category.category_id')
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'products.product_id',
                'products.name as product_name',
                'category.name as category_name',
                'category.category_id',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.modal_price) as total_modal'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as total_revenue')
            )
            ->groupBy('products.product_id', 'products.name', 'category.name', 'category.category_id');

        if ($categoryId) {
            $query->where('products.category_id', $categoryId);
        }

        $productStats = $query->get();

        // Calculate profit for each product
        $productStats->map(function ($item) {
            $item->total_profit = $item->total_revenue - $item->total_modal;
            return $item;
        });

        // Calculate summary
        $summary = [
            'total_revenue' => $productStats->sum('total_revenue'),
            'total_modal' => $productStats->sum('total_modal'),
            'total_profit' => $productStats->sum('total_profit'),
        ];

        return [
            'product_stats' => $productStats,
            'summary' => $summary
        ];
    }

    /**
     * Menampilkan halaman laporan keuntungan.
     */
    public function profit(Request $request)
    {
        // Ambil data ringkasan untuk ditampilkan di kartu statistik
        $data = $this->getProfitData($request);

        // Siapkan data awal untuk grafik
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        
        // Ambil data untuk chart
        $chartData = $this->prepareChartData($startDate, $endDate);
        
        $categories = Category::all();

        return view('supervisor.profit', [
            'summary' => $data['summary'],
            'categories' => $categories,
            'chartData' => $chartData, 
            'request' => $request // <-- PERBAIKAN DI SINI
        ]);
    }


    /**
     * Menyediakan data untuk DataTables.
     */
    public function getData(Request $request)
    {
        $data = $this->getProfitData($request)['product_stats'];
        return DataTables::of($data)
            ->editColumn('total_modal', fn($row) => 'Rp ' . number_format($row->total_modal, 0, ',', '.'))
            ->editColumn('total_revenue', fn($row) => 'Rp ' . number_format($row->total_revenue, 0, ',', '.'))
            ->editColumn('total_profit', function ($row) {
                $profit = $row->total_revenue - $row->total_modal;
                $color = $profit >= 0 ? 'text-green-600' : 'text-red-600';
                return '<span class="font-semibold ' . $color . '">Rp ' . number_format($profit, 0, ',', '.') . '</span>';
            })
            ->rawColumns(['total_profit'])
            ->make(true);
    }

    /**
     * Menyediakan data untuk grafik via AJAX.
     */
    public function getChartData(Request $request)
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->startOfMonth();
        $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();

        return response()->json($this->prepareChartData($startDate, $endDate));
    }


    /**
     * Menangani export ke Excel.
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
            'profits' => $profits
        ];
    }
}