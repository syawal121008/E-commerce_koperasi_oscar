{{-- resources/views/orders/receipt.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg md:text-xl text-gray-800 leading-tight">
                <i class="fas fa-receipt mr-2"></i>Struk Pesanan
            </h2>
            <div class="flex space-x-2 no-print">
                <a href="{{ route('orders.receipt.download', $order->order_id) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-download mr-1"></i>Unduh PDF
                </a>
                <button onclick="window.print()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-print mr-1"></i>Cetak
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-4 md:py-8">
        <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Receipt Container -->
            <div class="receipt-wrapper bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="receipt p-6" id="receiptContent">
                    <!-- Header -->
                    <div class="receipt-header text-center pb-4 border-b border-dashed border-gray-400 mb-4">
                        <div class="store-name text-lg font-bold mb-2">KOPERASI SMKIUTAMA</div>
                        <div class="store-info text-xs text-gray-600 leading-tight">
                            Kompleks PT.PLN P2B TJBB<br>
                            Krukut, Kec. Limo, Depok<br>
                            Telp: (021) 7530843
                        </div>
                    </div>

                    <!-- Order Info -->
                    <div class="order-info mb-4 pb-4 border-b border-dashed border-gray-400">
                        <div class="info-row flex justify-between mb-2 text-sm">
                            <span class="info-label font-medium">No Order:</span>
                            <span class="info-value font-bold">#{{ substr($order->order_id, 0, 8) }}</span>
                        </div>
                        <div class="info-row flex justify-between mb-2 text-sm">
                            <span class="info-label">Tanggal:</span>
                            <span class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="info-row flex justify-between mb-2 text-sm">
                            <span class="info-label">Kasir:</span>
                            <span class="info-value">{{ Str::limit($order->admin->full_name ?? 'Admin', 20) }}</span>
                        </div>
                        <div class="info-row flex justify-between text-sm">
                            <span class="info-label">Customer:</span>
                            <span class="info-value">{{ Str::limit($order->customer->full_name ?? 'Customer', 20) }}</span>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="items-section mb-4 pb-4 border-b border-dashed border-gray-400">
                        @foreach($order->items as $item)
                        <div class="item mb-3">
                            <div class="item-name text-sm font-semibold mb-1 text-gray-800">
                                {{ Str::limit($item->product->name ?? 'Produk', 30) }}
                            </div>
                            <div class="item-details flex justify-between text-sm text-gray-600 pl-2">
                                <span class="item-qty-price">
                                    {{ $item->quantity }} x Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </span>
                                <span class="item-total font-semibold text-gray-800">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Summary -->
                    <div class="summary mb-4 pb-4 border-b border-dashed border-gray-400">
                        <div class="summary-row flex justify-between mb-2 text-sm">
                            <span class="summary-label">Subtotal:</span>
                            <span class="summary-value">Rp {{ number_format($order->items->sum('subtotal'), 0, ',', '.') }}</span>
                        </div>
                        
                        @if($order->balance_used > 0)
                        <div class="summary-row flex justify-between mb-2 text-sm">
                            <span class="summary-label">Bayar Saldo:</span>
                            <span class="summary-value text-blue-600">Rp {{ number_format($order->balance_used, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($order->cash_amount > 0)
                        <div class="summary-row flex justify-between mb-2 text-sm">
                            <span class="summary-label">Bayar Tunai:</span>
                            <span class="summary-value text-green-600">Rp {{ number_format($order->cash_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        
                        <div class="summary-total flex justify-between text-base font-bold pt-3 mt-3 border-t border-gray-600">
                            <span>TOTAL:</span>
                            <span class="text-lg">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if($order->notes)
                    <div class="notes mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-xs">
                        <strong>Catatan:</strong> {{ $order->notes }}
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="receipt-footer text-center text-sm text-gray-600">
                        <div class="thank-you font-bold mb-3 text-base text-gray-800">TERIMA KASIH</div>
                        <div class="policy mb-2">Barang yang sudah dibeli<br>tidak dapat dikembalikan</div>
                        <div class="timestamp text-xs text-gray-500">
                            {{ now()->format('d/m/Y H:i') }} WIB
                        </div>
                    </div>
                </div>

                <!-- Action Buttons - Hidden when printing -->
                <div class="flex justify-center items-center space-x-4 p-4 border-t no-print bg-gray-50">
                    <a href="{{ route('orders.show', $order->order_id) }}" 
                       class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Detail Pesanan
                    </a>
                    <a href="{{ route('orders.index') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-list mr-1"></i>Semua Pesanan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Styles -->
    <style>
        /* Base Receipt Styles */
        .receipt {
            font-family: 'Courier New', Courier, monospace;
            line-height: 1.4;
        }

        /* Print Styles */
        @media print {
            @page {
                size: 80mm auto;
                margin: 5mm;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            body {
                padding: 0 !important;
                background: white !important;
            }

            /* Hide everything except receipt */
            body * {
                visibility: hidden;
            }

            .receipt-wrapper,
            .receipt-wrapper * {
                visibility: visible;
            }

            /* Receipt container positioning */
            .receipt-wrapper {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 80mm !important;
                max-width: 80mm !important;
                padding: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                background: white !important;
                overflow: visible !important;
            }

            .receipt {
                width: 100% !important;
                max-width: 100% !important;
                padding: 8px !important;
                font-size: 9px !important;
                line-height: 1.2 !important;
                color: black !important;
                background: white !important;
            }

            /* Header styles */
            .store-name {
                font-size: 11px !important;
                font-weight: bold !important;
                margin-bottom: 3px !important;
            }

            .store-info {
                font-size: 8px !important;
                line-height: 1.1 !important;
            }

            /* Content sections */
            .receipt-header,
            .order-info,
            .items-section,
            .summary {
                margin-bottom: 6px !important;
                padding-bottom: 4px !important;
                page-break-inside: avoid;
            }

            /* Info rows */
            .info-row {
                font-size: 8px !important;
                margin-bottom: 1px !important;
            }

            /* Items */
            .item {
                margin-bottom: 3px !important;
            }

            .item-name {
                font-size: 8px !important;
                font-weight: bold !important;
                margin-bottom: 1px !important;
                line-height: 1.1 !important;
            }

            .item-details {
                font-size: 8px !important;
                padding-left: 4px !important;
            }

            /* Summary */
            .summary-row {
                font-size: 8px !important;
                margin-bottom: 1px !important;
            }

            .summary-total {
                font-size: 9px !important;
                font-weight: bold !important;
                padding-top: 3px !important;
                margin-top: 3px !important;
            }

            /* Footer */
            .receipt-footer {
                font-size: 8px !important;
                line-height: 1.1 !important;
                text-align: center !important;
            }

            .thank-you {
                font-size: 9px !important;
                font-weight: bold !important;
                margin-bottom: 2px !important;
            }

            .policy,
            .timestamp {
                font-size: 7px !important;
                line-height: 1.1 !important;
            }

            .notes {
                font-size: 7px !important;
                padding: 2px !important;
                margin-bottom: 4px !important;
                border: 1px solid #ccc !important;
                background: white !important;
            }

            /* Hide non-print elements */
            .no-print {
                display: none !important;
            }

            /* Remove all colors for better print quality */
            * {
                color: black !important;
                background: white !important;
                border-color: #333 !important;
            }

            /* Ensure borders are visible */
            .border-dashed {
                border-style: dashed !important;
                border-color: #333 !important;
            }

            .border-t {
                border-top: 1px solid #333 !important;
            }

            .border-b {
                border-bottom: 1px dashed #333 !important;
            }

            /* Text alignments */
            .text-center { text-align: center !important; }
            .text-right { text-align: right !important; }
            .font-bold { font-weight: bold !important; }
            .font-semibold { font-weight: 600 !important; }
        }

        /* Screen styles - better readability */
        @media screen {
            .receipt-wrapper {
                font-family: 'Courier New', monospace;
            }

            .store-name {
                color: #1f2937;
            }

            .store-info {
                color: #6b7280;
            }

            .item-name {
                color: #374151;
            }

            .item-details {
                color: #6b7280;
            }

            .item-total {
                color: #111827;
            }

            .summary-total {
                color: #1f2937;
            }

            .thank-you {
                color: #111827;
            }

            .policy {
                color: #6b7280;
            }

            .timestamp {
                color: #9ca3af;
            }
        }
    </style>

    {{-- Auto print if requested --}}
    @if(request('print') === 'true')
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
    @endif
</x-app-layout>