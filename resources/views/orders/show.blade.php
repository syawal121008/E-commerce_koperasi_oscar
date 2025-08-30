<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-receipt mr-2"></i>Detail Pesanan #{{ $order->order_id }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('paid'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Sukses</p>
                    <p>{{ session('paid') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            {{-- Receipt Button Section - Only show for paid orders --}}
            @if(in_array($order->status, ['paid']))
<div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-xl p-4 md:p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
        <div>
            <h3 class="text-lg font-semibold text-green-800 mb-2">
                <i class="fas fa-check-circle mr-2"></i>Pesanan Berhasil Dibayar
            </h3>
            <p class="text-sm text-green-700">
                Pembayaran telah diterima pada {{ $order->created_at->format('d F Y, H:i') }} WIB
            </p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
            <button onclick="openReceiptModal()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm text-center">
                <i class="fas fa-receipt mr-2"></i>Lihat Struk
            </button>
            <a href="{{ route('orders.receipt.download', $order->order_id) }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm text-center">
                <i class="fas fa-download mr-2"></i>Unduh PDF
            </a>
            {{-- FIXED: Tidak menggunakan target="_blank" dan menggunakan JavaScript print --}}
            <button onclick="printReceipt()" 
               class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm text-center">
                <i class="fas fa-print mr-2"></i>Cetak
            </button>
        </div>
    </div>
</div>
@endif

            {{-- Detail Pembeli & Penjual --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white shadow-md rounded-xl p-6 border">
                    <h3 class="text-lg font-bold text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-blue-600"></i>Pelanggan
                    </h3>
                    <p class="text-sm text-gray-800">
                        <strong>{{ $order->customer->full_name ?? $order->customer->name ?? 'Tidak diketahui' }}</strong> <br>
                        <span class="text-gray-600">NIS/NIP:</span> {{ $order->customer->student_id ?? '-' }}<br>
                        <span class="text-gray-600">Email:</span> {{ $order->customer->email ?? '-' }}
                    </p>
                </div>
                <div class="bg-white shadow-md rounded-xl p-6 border">
                    <h3 class="text-lg font-bold text-gray-700 mb-2">
                        <i class="fas fa-store mr-2 text-green-600"></i>Penjual
                    </h3>
                    <p class="text-sm text-gray-800">
                        <strong>{{ $order->admin->full_name ?? $order->admin->name ?? 'Tidak diketahui' }}</strong> <br>
                        <span class="text-gray-600">NIS/NIP:</span> {{ $order->admin->student_id ?? '-' }}<br>
                        <span class="text-gray-600">Email:</span> {{ $order->admin->email ?? '-' }}
                    </p>
                </div>
            </div>

            {{-- Order Status & Info --}}
            <div class="bg-white shadow-md rounded-xl p-6 border">
                <h3 class="text-lg font-bold text-gray-700 mb-4">
                    <i class="fas fa-info-circle mr-2 text-purple-600"></i>Informasi Pesanan
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <span class="text-sm text-gray-600">Status Pesanan</span>
                        <p class="font-semibold">
                            <span @class([
                                'inline-block px-3 py-1 text-xs font-semibold rounded-full',
                                'bg-yellow-200 text-yellow-800' => $order->status === 'pending',
                                'bg-green-200 text-green-800' => $order->status === 'paid',
                                'bg-green-200 text-green-800' => $order->status === 'completed',
                                'bg-red-200 text-red-800' => $order->status === 'cancelled',
                            ])>
                                @if($order->status === 'pending')
                                    Menunggu
                                @elseif($order->status === 'paid')
                                    Dibayar
                                @elseif($order->status === 'completed')
                                    Selesai
                                @elseif($order->status === 'cancelled')
                                    Dibatalkan
                                @else
                                    {{ ucfirst($order->status) }}
                                @endif
                            </span>
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Tanggal Pesanan</span>
                        <p class="font-semibold">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Metode Pembayaran</span>
                        <p class="font-semibold">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                    </div>
                    @if($order->status === 'paid')
                    <div>
                        <span class="text-sm text-gray-600">Selesai</span>
                        <p class="font-semibold">{{ $order->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>

                @if($order->notes)
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm"><strong>Catatan:</strong> {{ $order->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Produk Dipesan --}}
            <div class="bg-white shadow-md rounded-xl p-6 border">
                <h3 class="text-lg font-bold text-gray-700 mb-4">
                    <i class="fas fa-shopping-cart mr-2 text-blue-600"></i>Produk yang Dipesan
                </h3>

                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-start gap-4 {{ !$loop->last ? 'pb-4 border-b border-gray-200' : '' }}">
                            <img src="{{ $item->product->image ? asset('storage/' . $item->product->image) : 'https://via.placeholder.com/80x80?text=No+Image' }}"
                                 class="w-20 h-20 rounded-md border object-cover flex-shrink-0"
                                 alt="{{ $item->product->name }}">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-semibold text-gray-800 mb-1">
                                    {{ $item->product->name ?? 'Produk Dihapus' }}
                                </h4>
                                @if($item->product && $item->product->category)
                                <p class="text-sm text-gray-500 mb-2">
                                    Kategori: {{ $item->product->category->name }}
                                </p>
                                @endif
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-600">Harga Satuan:</span>
                                        <p class="font-medium">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Jumlah:</span>
                                        <p class="font-medium">{{ $item->quantity }} item</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Subtotal:</span>
                                        <p class="font-semibold text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Ringkasan Pembayaran --}}
            <div class="bg-white shadow-md rounded-xl p-6 border">
                <h3 class="text-lg font-bold text-gray-700 mb-4">
                    <i class="fas fa-calculator mr-2 text-indigo-600"></i>Ringkasan Pembayaran
                </h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Jumlah Item</span>
                            <span class="font-medium">{{ $order->items->sum('quantity') }} item</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </div>
                        @if($order->balance_used > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Dibayar dengan Saldo</span>
                            <span class="font-medium text-blue-600">Rp {{ number_format($order->balance_used, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($order->cash_amount > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Dibayar dengan Tunai</span>
                            <span class="font-medium text-green-600">Rp {{ number_format($order->cash_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="pt-3 border-t">
                            <div class="flex justify-between items-center text-lg">
                                <span class="font-bold text-gray-800">Total Harga</span>
                                <span class="font-extrabold text-blue-600">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex flex-col sm:flex-row justify-end items-center space-y-3 sm:space-y-0 sm:space-x-3">
                <a href="{{ route('orders.index') }}" 
                   class="w-full sm:w-auto bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg text-center font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                </a>
                
                @if (in_array($order->status, ['pending']) && (Auth::id() == $order->customer_id || Auth::user()->role == 'admin'))
                    <form action="{{ route('orders.cancel', $order->order_id) }}" method="POST" class="w-full sm:w-auto" onsubmit="return confirm('Yakin batalkan pesanan ini?')">
                        @csrf
                        <button class="w-full px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium">
                            <i class="fas fa-times mr-2"></i>Batalkan Pesanan
                        </button>
                    </form>
                @endif

                @if($order->status === 'paid' && (Auth::id() == $order->admin_id || Auth::user()->role == 'admin'))
                    <form action="{{ route('orders.complete', $order->order_id) }}" method="POST" class="w-full sm:w-auto" onsubmit="return confirm('Tandai pesanan sebagai selesai?')">
                        @csrf
                        <button class="w-full px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">
                            <i class="fas fa-check mr-2"></i>Selesaikan Pesanan
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal Struk --}}
    @if(in_array($order->status, ['paid', 'paid']))
<div id="receiptModal" class="fixed inset-0 z-50 hidden">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" onclick="closeReceiptModal()"></div>
    
    <!-- Modal Content -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all w-full max-w-md">
                <!-- Modal Header -->
                <div class="no-print flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-receipt mr-2"></i>Preview Struk
                    </h3>
                    <div class="flex space-x-2">
                        {{-- FIXED: Tidak menggunakan target="_blank" --}}
                        <button onclick="printReceipt()" 
                           class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded text-sm">
                            <i class="fas fa-print mr-1"></i>Cetak
                        </button>
                        <button onclick="closeReceiptModal()" 
                                class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Modal Body - Receipt Preview -->
                <div class="no-print p-6">
                    <div class="receipt-preview bg-gray-50 p-4 rounded" id="receiptContent">
                        <!-- Simplified Receipt Preview -->
                        <div class="text-center mb-4">
                            <div class="font-bold text-sm">KOPERASI SMKIUTAMA</div>
                            <div class="text-xs text-gray-600">Struk Pembelian</div>
                            <div class="text-xs">Order: #{{ substr($order->order_id, 0, 8) }}</div>
                        </div>
                        
                        <div class="text-xs space-y-1 mb-4">
                            <div class="flex justify-between">
                                <span>Tanggal:</span>
                                <span>{{ $order->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Kasir:</span>
                                <span>{{ Str::limit($order->admin->full_name ?? 'Admin', 15) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Customer:</span>
                                <span>{{ Str::limit($order->customer->full_name ?? 'Customer', 15) }}</span>
                            </div>
                        </div>
                        
                        <div class="border-t border-dashed border-gray-400 pt-2 mb-2">
                            @foreach($order->items as $item)
                            <div class="text-xs mb-1">
                                <div class="font-medium">{{ Str::limit($item->product->name ?? 'Produk', 25) }}</div>
                                <div class="flex justify-between pl-2">
                                    <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                    <span class="font-medium">{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="border-t border-dashed border-gray-400 pt-2">
                            <div class="flex justify-between font-bold text-sm">
                                <span>TOTAL:</span>
                                <span>Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        <div class="text-center text-xs text-gray-600 mt-3 pt-2 border-t border-dashed">
                            <div class="font-bold">TERIMA KASIH</div>
                            <div>Barang tidak dapat dikembalikan</div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4 text-sm text-gray-600">
                        <p>Klik "Cetak" di atas untuk mencetak struk</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Hidden Print Container - FIXED: Dibuat lebih sesuai untuk thermal printer --}}
<div id="printContainer" class="hidden">
    <div id="printContent">
        <div class="receipt-print">
            <!-- Header Struk -->
            <div class="text-center mb-4">
                <div class="font-bold text-lg mb-1">KOPERASI SMKIUTAMA</div>
                <div class="text-sm border-b-2 border-dashed border-black pb-2">Struk Pembelian</div>
            </div>
            
            <!-- Info Transaksi -->
            <table class="w-full text-xs mb-3">
                <tr>
                    <td class="w-1/3">Order ID</td>
                    <td class="w-8 text-center">:</td>
                    <td>#{{ substr($order->order_id, 0, 12) }}</td>
                </tr>
                <tr>
                    <td>Tanggal</td>
                    <td class="text-center">:</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>Kasir</td>
                    <td class="text-center">:</td>
                    <td>{{ $order->admin->full_name ?? 'Admin' }}</td>
                </tr>
                <tr>
                    <td>Customer</td>
                    <td class="text-center">:</td>
                    <td>{{ $order->customer->full_name ?? 'Customer' }}</td>
                </tr>
            </table>
            
            <!-- Item List -->
            <div class="border-b-2 border-dashed border-black pb-2 mb-2">
                @foreach($order->items as $item)
                <div class="mb-2">
                    <div class="font-medium text-xs">{{ $item->product->name ?? 'Produk' }}</div>
                    <table class="w-full text-xs">
                        <tr>
                            <td class="w-1/4">{{ $item->quantity }} x</td>
                            <td class="w-1/3 text-right">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="w-8 text-center">=</td>
                            <td class="text-right font-medium">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    </table>
                </div>
                @endforeach
            </div>
            
            <!-- Payment Summary -->
            <table class="w-full text-xs mb-3">
                <tr>
                    <td class="font-bold">Subtotal</td>
                    <td class="text-right font-bold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                </tr>
                @if($order->balance_used > 0)
                <tr>
                    <td>Bayar Saldo</td>
                    <td class="text-right">Rp {{ number_format($order->balance_used, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($order->cash_amount > 0)
                <tr>
                    <td>Bayar Tunai</td>
                    <td class="text-right">Rp {{ number_format($order->cash_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="border-t border-dashed border-black">
                    <td class="font-bold text-sm pt-1">TOTAL</td>
                    <td class="text-right font-bold text-sm pt-1">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                </tr>
            </table>
            
            <!-- Footer -->
            <div class="text-center text-xs border-t-2 border-dashed border-black pt-3">
                <div class="font-bold mb-2">TERIMA KASIH</div>
                <div class="mb-1">Barang yang sudah dibeli</div>
                <div class="mb-1">tidak dapat ditukar/dikembalikan</div>
                <div class="text-gray-600 mt-2">{{ now()->format('d/m/Y H:i') }} WIB</div>
            </div>
        </div>
    </div>
</div>

{{-- FIXED CSS Print Styles - Optimized untuk thermal printer --}}
<style>
/* Normal display styles */
.receipt-preview {
    font-family: 'Courier New', monospace;
    max-width: 300px;
    margin: 0 auto;
}

/* FIXED: Print styles yang lebih baik */
@media print {
    /* Reset semua margin dan padding */
    * {
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }
    
    /* Set ukuran kertas untuk thermal printer 80mm */
    @page {
        size: 80mm auto;
        margin: 2mm !important;
    }
    
    /* Hide semua content kecuali yang akan dicetak */
    body * {
        visibility: hidden !important;
    }
    
    /* Show hanya print container */
    #printContainer,
    #printContainer * {
        visibility: visible !important;
        display: block !important;
    }
    
    /* Print container positioning */
    #printContainer {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 76mm !important; /* Sedikit lebih kecil dari 80mm untuk margin */
        background: white !important;
    }
    
    /* Print content styling */
    #printContent {
        width: 100% !important;
        font-family: 'Courier New', monospace !important;
        font-size: 10px !important;
        line-height: 1.2 !important;
        color: black !important;
        background: white !important;
        padding: 2mm !important;
    }
    
    /* Receipt print specific styles */
    .receipt-print {
        width: 100% !important;
    }
    
    .receipt-print table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin-bottom: 2px !important;
    }
    
    .receipt-print td {
        padding: 0.5px 1px !important;
        vertical-align: top !important;
        font-size: 10px !important;
        line-height: 1.1 !important;
    }
    
    .receipt-print .text-center {
        text-align: center !important;
    }
    
    .receipt-print .text-right {
        text-align: right !important;
    }
    
    .receipt-print .font-bold {
        font-weight: bold !important;
    }
    
    .receipt-print .text-lg {
        font-size: 12px !important;
    }
    
    .receipt-print .text-sm {
        font-size: 10px !important;
    }
    
    .receipt-print .text-xs {
        font-size: 9px !important;
    }
    
    /* Border styles */
    .receipt-print .border-dashed {
        border-style: dashed !important;
        border-color: black !important;
    }
    
    .receipt-print .border-b-2 {
        border-bottom-width: 1px !important;
    }
    
    .receipt-print .border-t-2 {
        border-top-width: 1px !important;
    }
    
    /* Spacing */
    .receipt-print .mb-1 { margin-bottom: 1px !important; }
    .receipt-print .mb-2 { margin-bottom: 2px !important; }
    .receipt-print .mb-3 { margin-bottom: 3px !important; }
    .receipt-print .mb-4 { margin-bottom: 4px !important; }
    .receipt-print .pt-1 { padding-top: 1px !important; }
    .receipt-print .pt-3 { padding-top: 3px !important; }
    .receipt-print .pb-2 { padding-bottom: 2px !important; }
}

/* Hide print container on screen */
@media screen {
    #printContainer {
        display: none !important;
    }
}
</style>

{{-- FIXED JavaScript - Print langsung tanpa tab baru --}}
<script>
function openReceiptModal() {
    document.getElementById('receiptModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReceiptModal() {
    document.getElementById('receiptModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// FIXED: Print function yang tidak membuka tab baru
function printReceipt() {
    // Tampilkan print container
    const printContainer = document.getElementById('printContainer');
    printContainer.style.display = 'block';
    
    // Tunggu sebentar untuk memastikan content ter-render
    setTimeout(() => {
        // Print langsung dari halaman ini
        window.print();
        
        // Sembunyikan kembali print container setelah print dialog ditutup
        setTimeout(() => {
            printContainer.style.display = 'none';
        }, 100);
    }, 100);
}

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReceiptModal();
    }
});

// FIXED: Event listener untuk setelah print selesai
window.addEventListener('afterprint', function() {
    const printContainer = document.getElementById('printContainer');
    if (printContainer) {
        printContainer.style.display = 'none';
    }
});
</script>
</x-app-layout>