<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
            </h2>
            <div class="flex items-center space-x-4">
                </div>
        </div>
    </x-slot>

    <div class="space-y-6">
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    <i class="fas fa-wallet mr-2"></i>
                    @if($user->role === 'admin')
                        Pemasukan Hari Ini
                    @elseif($user->role === 'guru')
                        Pemasukan Bulan Ini
                    @else
                        Saldo Saat Ini
                    @endif
                </h3>
                <p class="text-3xl font-bold">
                    @if($user->role === 'admin')
                        Rp {{ number_format($dailyIncome, 0, ',', '.') }}
                    @elseif($user->role === 'guru')
                        Rp {{ number_format($monthlySaldoIncome, 0, ',', '.') }}
                    @else
                        Rp {{ number_format($user->balance, 0, ',', '.') }}
                    @endif
                </p>
                <p class="text-blue-200 text-sm mt-1">
                    @if($user->role === 'admin')
                        {{ now()->format('d M Y') . ' • ' . ucfirst($user->role_name) }}
                    @elseif($user->role === 'guru')
                        {{ now()->format('M Y') . ' • ' . ucfirst($user->role_name) }}
                    @else
                        {{ $user->student_id . ' • ' . ucfirst($user->role_name) }}
                    @endif
                </p>
            </div>
            <div class="text-right">
                @if($user->role === 'customer')
                    <a href="{{ route('topup.qris') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-lg text-sm transition-all">
                        <i class="fas fa-plus mr-2"></i>Isi Saldo
                    </a>
                @endif
                <div class="mt-2 text-sm text-blue-200">
                    <i class="{{ $user->role === 'admin' || $user->role === 'guru' ? 'fas fa-list' : 'fas fa-history' }} mr-1"></i>
                    <a href="{{ route('transactions.index') }}" class="hover:underline">
                        {{ $user->role === 'admin' || $user->role === 'guru' ? 'Lihat Rekap' : 'Lihat Riwayat' }}
                    </a>
                </div>
            </div>
        </div>
    </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @if($user->role === 'customer')
                <a href="{{ route('shop.index') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                    <div class="text-center">
                        <div class="bg-green-100 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-shopping-bag text-green-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Belanja</h3>
                        <p class="text-gray-600 text-sm">Cari Produk</p>
                    </div>
                </a>

            @elseif($user->role === 'admin')
                <a href="{{ route('supervisor.profit') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                    <div class="text-center">
                        <div class="bg-blue-100 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-file-invoice-dollar text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Laporan Keuntungan</h3>
                        <p class="text-gray-600 text-sm">Lihat Laporan Keuangan</p>
                    </div>
                </a>

            @elseif($user->role === 'guru')
                <a href="{{ route('supervisor.transactions') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                    <div class="text-center">
                        <div class="bg-purple-100 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-chart-bar text-purple-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Rekap Transaksi</h3>
                        <p class="text-gray-600 text-sm">Lihat Rekap Transaksi</p>
                    </div>
                </a>
            @endif

            @if($user->role === 'admin')
                <a href="{{ route('topup.scan') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                    <div class="text-center">
                        <div class="bg-blue-100 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-camera text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Pemindai Kode QR</h3>
                        <p class="text-gray-600 text-sm">Pindai untuk Isi Saldo</p>
                    </div>
                </a>
            @elseif($user->role === 'guru')
                <a href="{{ route('supervisor.topups') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                    <div class="text-center">
                        <div class="bg-blue-100 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-wallet text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Rekap Saldo</h3>
                        <p class="text-gray-600 text-sm">Lihat Rekap Saldo Siswa</p>
                    </div>
                </a>
            @elseif($user->role === 'customer')
                <a href="{{ route('carts.index') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                    <div class="text-center">
                        <div class="bg-blue-100 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Keranjang</h3>
                        <p class="text-gray-600 text-sm">Lihat & Kelola Belanjaan</p>
                    </div>
                </a>
            @endif

            <a href="{{ route('orders.index') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                <div class="text-center">
                    <div class="bg-purple-100 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-receipt text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Riwayat Pesanan</h3>
                    <p class="text-gray-600 text-sm">Cek riwayat dan status pesanan</p>
                </div>
            </a>

            @if($user->role === 'admin')
                <a href="{{ route('admin.pos') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                    <div class="text-center">
                        <div class="bg-green-100 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-cash-register text-green-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Buat Pesanan</h3>
                        <p class="text-gray-600 text-sm">Akses fitur POS untuk transaksi</p>
                    </div>
                </a>
            @elseif($user->role === 'customer')
                <a href="{{ route('qr-code') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                    <div class="text-center">
                        <div class="bg-yellow-100 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-qrcode text-yellow-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Kode QR Saya</h3>
                        <p class="text-gray-600 text-sm">Gunakan kode ini untuk isi saldo</p>
                    </div>
                </a>
            @elseif($user->role === 'guru')
                <a href="{{ route('admin.users.index') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                    <div class="text-center">
                        <div class="bg-blue-100 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-users text-blue-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Pengguna</h3>
                        <p class="text-gray-600 text-sm">Lihat Siswa yang daftar</p>
                    </div>
                </a>
            @endif
        </div>

        @if($user->role === 'admin' || $user->role === 'customer')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @if($user->role === 'admin')
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-users text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_users'] }}</p>
                                <p class="text-gray-600 text-sm">Total Pengguna</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="bg-green-100 p-3 rounded-lg">
                                <i class="fas fa-shopping-cart text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_orders'] }}</p>
                                <p class="text-gray-600 text-sm">Total Pesanan</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="bg-purple-100 p-3 rounded-lg">
                                <i class="fas fa-box text-purple-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['total_products'] }}</p>
                                <p class="text-gray-600 text-sm">Total Produk</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="bg-yellow-100 p-3 rounded-lg">
                                <i class="fas fa-money-check-alt text-yellow-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['pending_topups'] }}</p>
                                <p class="text-gray-600 text-sm">Saldo Tertunda</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-3 rounded-lg">
                                <i class="fas fa-shopping-cart text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['my_orders'] }}</p>
                                <p class="text-gray-600 text-sm">Pesanan Saya</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="bg-yellow-100 p-3 rounded-lg">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['pending_orders'] }}</p>
                                <p class="text-gray-600 text-sm">Pesanan Tertunda</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="bg-green-100 p-3 rounded-lg">
                                <i class="fas fa-check-circle text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-800">{{ $stats['paid_orders'] }}</p>
                                <p class="text-gray-600 text-sm">Pesanan Selesai</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="bg-purple-100 p-3 rounded-lg">
                                <i class="fas fa-money-bill-wave text-purple-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</p>
                                <p class="text-gray-600 text-sm">Total Belanja</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        @if($user->role === 'guru')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-chart-line mr-2"></i>Grafik Penjualan Per Bulan
                        </h3>
                    </div>
                    <div class="p-6">
                        <canvas id="salesChart" width="400" height="200"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-chart-bar mr-2"></i>Pemasukan Saldo Per Bulan
                        </h3>
                    </div>
                    <div class="p-6">
                        <canvas id="incomeChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>
        @endif

        @if($user->role === 'admin' || $user->role === 'customer')
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-history mr-2"></i>Transaksi Terbaru
                        </h3>
                        <a href="{{ route('transactions.index') }}" class="text-blue-600 hover:text-blue-800 text-sm">
                            Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @if($recentTransactions->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentTransactions as $transaction)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center
                                            @if($transaction->type === 'income') bg-green-100 
                                            @elseif($transaction->type === 'topup') bg-blue-100 
                                            @elseif($transaction->type === 'payment') bg-orange-100 
                                            @elseif($transaction->type === 'refund') bg-purple-100
                                            @else bg-red-100 @endif">
                                            @if($transaction->type === 'income')
                                                <i class="fas fa-money-bill-wave text-green-600"></i>
                                            @elseif($transaction->type === 'topup')
                                                <i class="fas fa-wallet text-blue-600"></i>
                                            @elseif($transaction->type === 'payment')
                                                <i class="fas fa-credit-card text-orange-600"></i>
                                            @elseif($transaction->type === 'refund')
                                                <i class="fas fa-undo text-purple-600"></i>
                                            @else
                                                <i class="fas fa-minus-circle text-red-600"></i>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <p class="font-medium text-gray-800">
                                                @if($transaction->type === 'income')
                                                    Pemasukan
                                                @elseif($transaction->type === 'topup')
                                                    Isi Saldo
                                                @elseif($transaction->type === 'payment')
                                                    Pembayaran
                                                @elseif($transaction->type === 'refund')
                                                    Pengembalian
                                                @elseif($transaction->type === 'expense')
                                                    Pengeluaran
                                                @else
                                                    {{ ucfirst($transaction->type) }}
                                                @endif
                                            </p>
                                            <p class="text-sm text-gray-600">
                                                @php
                                                    $description = '';
                                                    
                                                    // Jika ada deskripsi manual, gunakan itu
                                                    if ($transaction->description && !str_contains($transaction->description, 'pesanan #')) {
                                                        $description = $transaction->description;
                                                    } 
                                                    // Untuk transaksi yang berkaitan dengan order
                                                    elseif (in_array($transaction->type, ['income', 'payment', 'refund']) && $transaction->related_id) {
                                                        // Cari order berdasarkan related_id
                                                        $order = \App\Models\Order::where('order_id', $transaction->related_id)->first();
                                                        
                                                        if ($order) {
                                                            // Ambil semua item dalam order
                                                            $orderItems = \App\Models\OrderItem::where('order_id', $order->order_id)->get();
                                                            $productNames = [];
                                                            
                                                            // Kumpulkan nama produk dan quantity
                                                            foreach ($orderItems as $item) {
                                                                $product = \App\Models\Product::where('product_id', $item->product_id)->first();
                                                                if ($product) {
                                                                    $productName = $product->name;
                                                                    if ($item->quantity > 1) {
                                                                        $productName .= ' (' . $item->quantity . 'x)';
                                                                    }
                                                                    $productNames[] = $productName;
                                                                }
                                                            }
                                                            
                                                            $productList = implode(', ', $productNames);
                                                            
                                                            // Format deskripsi berdasarkan tipe dan role
                                                            if ($transaction->type === 'income' && auth()->user()->role === 'admin' && !empty($productNames)) {
                                                                $customer = \App\Models\User::where('user_id', $order->customer_id)->first();
                                                                $customerName = $customer ? $customer->full_name : 'Customer';
                                                                $description = 'Penjualan ' . $productList . ' - Dibeli oleh ' . $customerName;
                                                            } elseif ($transaction->type === 'income' && !empty($productNames)) {
                                                                $description = 'Pembelian ' . $productList;
                                                            } elseif ($transaction->type === 'payment' && !empty($productNames)) {
                                                                $description = 'Pembayaran untuk ' . $productList;
                                                            } elseif ($transaction->type === 'refund' && !empty($productNames)) {
                                                                $description = 'Pengembalian dana untuk ' . $productList;
                                                            } else {
                                                                // Fallback jika produk tidak ditemukan
                                                                if ($transaction->type === 'income') {
                                                                    $description = 'Pemasukan dari penjualan';
                                                                } elseif ($transaction->type === 'payment') {
                                                                    $description = 'Pembayaran transaksi';
                                                                } elseif ($transaction->type === 'refund') {
                                                                    $description = 'Pengembalian dana';
                                                                }
                                                            }
                                                        } else {
                                                            // Order tidak ditemukan
                                                            if ($transaction->type === 'income') {
                                                                $description = 'Pemasukan dari penjualan';
                                                            } elseif ($transaction->type === 'payment') {
                                                                $description = 'Pembayaran transaksi';
                                                            } elseif ($transaction->type === 'refund') {
                                                                $description = 'Pengembalian dana';
                                                            }
                                                        }
                                                    } 
                                                    // Untuk transaksi topup
                                                    elseif ($transaction->type === 'topup' && $transaction->related_id) {
                                                        $topup = \App\Models\Topup::where('topup_id', $transaction->related_id)->first();
                                                        if ($topup) {
                                                            if ($topup->method === 'qris') {
                                                                $description = 'Isi saldo via QRIS';
                                                            } elseif ($topup->method === 'ewallet') {
                                                                $description = 'Isi saldo via E-Wallet';
                                                            } elseif ($topup->method === 'koperasi') {
                                                                $description = 'Isi saldo via Koperasi';
                                                            } elseif ($topup->method === 'scan_qr') {
                                                                $description = 'Isi saldo via Scan QR';
                                                            } else {
                                                                $description = 'Isi saldo via ' . ucfirst($topup->method);
                                                            }
                                                        } else {
                                                            $description = 'Isi saldo';
                                                        }
                                                    } elseif ($transaction->type === 'topup') {
                                                        $description = 'Isi saldo';
                                                    } elseif ($transaction->type === 'expense') {
                                                        $description = 'Pengeluaran untuk operasional';
                                                    } else {
                                                        $description = 'Transaksi ' . ucfirst($transaction->type);
                                                    }
                                                @endphp
                                                {{ $description }}
                                            </p>
                                            <p class="text-xs text-gray-500">{{ $transaction->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold
                                            @if($transaction->type === 'income' || $transaction->type === 'topup' || $transaction->type === 'refund') text-green-600 
                                            @elseif($transaction->type === 'payment' || $transaction->type === 'expense') text-red-600 
                                            @else text-gray-600 @endif">
                                            @if($transaction->type === 'income' || $transaction->type === 'topup' || $transaction->type === 'refund') + 
                                            @elseif($transaction->type === 'payment' || $transaction->type === 'expense') - 
                                            @endif
                                            Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            @if($transaction->status === 'pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Menunggu
                                                </span>
                                            @elseif($transaction->status === 'paid')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Dibayar
                                                </span>
                                            @elseif($transaction->status === 'completed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Selesai
                                                </span>
                                            @elseif($transaction->status === 'failed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Gagal
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ ucfirst($transaction->status) }}
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-receipt text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-600 font-medium">Belum Ada Transaksi</p>
                            <p class="text-gray-500 text-sm">Mulai berbelanja atau isi saldo untuk melihat riwayat transaksi</p>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if($user->role === 'admin')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-plus-circle mr-2"></i>Akses Cepat
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('products.create') }}" class="flex items-center p-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors">
                                <i class="fas fa-plus text-blue-600 mr-3"></i>
                                <span class="text-gray-800">Tambah Produk Baru</span>
                            </a>
                            <a href="{{ route('orders.pending') }}" class="flex items-center p-3 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition-colors">
                                <i class="fas fa-clock text-yellow-600 mr-3"></i>
                                <span class="text-gray-800">Lihat Pesanan Tertunda</span>
                            </a>
                            <a href="{{ route('admin.topups') }}" class="flex items-center p-3 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                                <i class="fas fa-check-circle text-green-600 mr-3"></i>
                                <span class="text-gray-800">Isi Saldo Tertunda</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i class="fas fa-tools mr-2"></i>Fitur
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-3">
                            <a href="{{ route('topup.scan') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition-shadow">
                                <div class="text-center">
                                    <div class="bg-blue-100 w-12 h-12 rounded-lg flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-camera text-blue-600 text-xl"></i>
                                    </div>
                                    <h3 class="font-semibold text-gray-800">Pindai QR Kode</h3>
                                    <p class="text-gray-600 text-sm">Pindai Untuk Isi Saldo Pengguna</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($user->role === 'guru')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Data untuk grafik penjualan
            const salesData = @json($chartData['sales'] ?? []);
            const incomeData = @json($chartData['income'] ?? []);
            const monthLabels = @json($chartData['months'] ?? []);

            // Grafik Penjualan
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Total Penjualan (Rp)',
                        data: salesData,
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });

            // Grafik Pemasukan Saldo
            const incomeCtx = document.getElementById('incomeChart').getContext('2d');
            new Chart(incomeCtx, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Pemasukan Saldo (Rp)',
                        data: incomeData,
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        </script>
    @endif
</x-app-layout>