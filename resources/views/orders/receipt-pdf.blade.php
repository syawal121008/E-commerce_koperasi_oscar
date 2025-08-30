<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Struk #{{ substr($order->order_id, 0, 8) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.2;
            color: #000;
            background: #fff;
            width: 80mm;
            max-width: 80mm;
        }

        .receipt {
            width: 100%;
            padding: 8px;
            margin: 0;
        }

        /* Header Styles */
        .receipt-header {
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #333;
        }

        .store-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 3px;
            letter-spacing: 0.3px;
        }

        .store-info {
            font-size: 8px;
            line-height: 1.1;
            color: #333;
        }

        /* Order Info */
        .order-info {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #333;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 1px;
            font-size: 8px;
        }

        .info-label {
            display: table-cell;
            width: 35%;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            width: 65%;
            text-align: right;
            vertical-align: top;
        }

        /* Items Section */
        .items-section {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #333;
        }

        .item {
            margin-bottom: 4px;
        }

        .item-name {
            font-size: 8px;
            margin-bottom: 1px;
            font-weight: bold;
            word-wrap: break-word;
            line-height: 1.1;
        }

        .item-details {
            display: table;
            width: 100%;
            font-size: 8px;
            color: #333;
            padding-left: 6px;
        }

        .item-qty-price {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }

        .item-total {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
            font-weight: bold;
        }

        /* Summary Section */
        .summary {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #333;
        }

        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 1px;
            font-size: 8px;
        }

        .summary-label {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .summary-value {
            display: table-cell;
            width: 50%;
            text-align: right;
            vertical-align: top;
        }

        .summary-total {
            font-size: 9px;
            font-weight: bold;
            padding-top: 3px;
            margin-top: 3px;
            border-top: 1px solid #000;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            font-size: 8px;
            line-height: 1.1;
        }

        .thank-you {
            font-weight: bold;
            margin-bottom: 2px;
            font-size: 9px;
        }

        .footer-note {
            margin-bottom: 1px;
        }

        .timestamp {
            margin-top: 4px;
            font-size: 7px;
            color: #666;
        }

        /* Utilities */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 1px; }
        .mb-2 { margin-bottom: 2px; }
        .mb-3 { margin-bottom: 3px; }

        /* Prevent page breaks within elements */
        .receipt-header,
        .order-info,
        .items-section,
        .summary,
        .receipt-footer {
            page-break-inside: avoid;
        }

        /* Ensure consistent spacing */
        p {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Header -->
        <div class="receipt-header">
            <div class="store-name">KOPERASI SMKIUTAMA</div>
            <div class="store-info">
                <div>Kompleks PT.PLN P2B TJBB</div>
                <div>Krukut, Kec. Limo, Depok</div>
                <div>Telp: (021) 7530843</div>
            </div>
        </div>

        <!-- Order Info -->
        <div class="order-info">
            <div class="info-row">
                <span class="info-label">No Order:</span>
                <span class="info-value">#{{ substr($order->order_id, 0, 8) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal:</span>
                <span class="info-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Kasir:</span>
                <span class="info-value">{{ Str::limit($order->admin->full_name ?? 'Admin', 18, '') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Customer:</span>
                <span class="info-value">{{ Str::limit($order->customer->full_name ?? 'Customer', 18, '') }}</span>
            </div>
        </div>

        <!-- Items -->
        <div class="items-section">
            @foreach($order->items as $item)
            <div class="item">
                <div class="item-name">
                    {{ Str::limit($item->product->name ?? 'Produk', 26, '') }}
                </div>
                <div class="item-details">
                    <span class="item-qty-price">
                        {{ $item->quantity }} x {{ number_format($item->unit_price, 0, ',', '.') }}
                    </span>
                    <span class="item-total">
                        {{ number_format($item->subtotal, 0, ',', '.') }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Summary -->
        <div class="summary">
            <div class="summary-row">
                <span class="summary-label">Subtotal:</span>
                <span class="summary-value">{{ number_format($order->items->sum('subtotal'), 0, ',', '.') }}</span>
            </div>
            
            @if($order->balance_used > 0)
            <div class="summary-row">
                <span class="summary-label">Bayar Saldo:</span>
                <span class="summary-value">{{ number_format($order->balance_used, 0, ',', '.') }}</span>
            </div>
            @endif

            @if($order->cash_amount > 0)
            <div class="summary-row">
                <span class="summary-label">Bayar Tunai:</span>
                <span class="summary-value">{{ number_format($order->cash_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            
            <div class="summary-row summary-total">
                <span class="summary-label">TOTAL:</span>
                <span class="summary-value">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
            </div>
        </div>

        @if($order->notes)
        <div style="margin-bottom: 8px; padding: 3px; border: 1px solid #ccc; font-size: 7px;">
            <strong>Catatan:</strong> {{ Str::limit($order->notes, 60) }}
        </div>
        @endif

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="thank-you">TERIMA KASIH</div>
            <div class="footer-note">Barang yang sudah dibeli</div>
            <div class="footer-note">tidak dapat dikembalikan</div>
            <div class="timestamp">{{ now()->format('d/m/Y H:i') }} WIB</div>
        </div>
    </div>
</body>
</html>