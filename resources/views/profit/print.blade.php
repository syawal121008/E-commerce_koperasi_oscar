<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koperasi SMKIUTAMA</title>
        <link rel="icon" href="{{ asset('storage/images/smk.png') }}" type="image/png">
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; color: #333; }

        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
        .header-left { flex: 1; text-align: left; }
        .header-center { flex: 2; text-align: center; }
        .header-right { flex: 1; }
        .header img { width: 70px; height: auto; }
        .header-center h1 { margin: 0; font-size: 20px; }
        .header-center p { margin: 2px 0; font-size: 12px; color: #555; }

        .summary { margin: 20px 0; display: flex; flex-wrap: wrap; justify-content: space-around; }
        .summary-item { flex: 1 1 22%; background: #f9f9f9; padding: 10px; margin: 5px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .summary-item strong { display: block; margin-bottom: 4px; font-size: 13px; }

        h3 { margin-top: 25px; margin-bottom: 10px; font-size: 15px; border-left: 4px solid #333; padding-left: 8px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; }
        th { background-color: #f1f1f1; font-weight: bold; text-align: center; }
        td { vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        tfoot td { font-weight: bold; background: #f9f9f9; }

        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            <img src="{{ asset('storage/images/smk.png') }}" alt="Logo">
        </div>
        <div class="header-center">
            <h1>LAPORAN KEUNTUNGAN KOPERASI SMKIUTAMA</h1>
            <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            <p>Dicetak pada: {{ now()->format('d M Y H:i') }}</p>
        </div>
        <div class="header-right"></div>
    </div>

    <div class="summary">
        <div class="summary-item">
            <strong>Total Pendapatan:</strong>
            Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
        </div>
        <div class="summary-item">
            <strong>Total Modal:</strong>
            Rp {{ number_format($summary['total_modal'], 0, ',', '.') }}
        </div>
        <div class="summary-item">
            <strong>Total Keuntungan:</strong>
            Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}
        </div>
        <div class="summary-item">
            <strong>Margin Keuntungan:</strong>
            {{ $summary['total_revenue'] > 0 ? number_format(($summary['total_profit'] / $summary['total_revenue']) * 100, 2) : 0 }}%
        </div>
    </div>

    <h3>DETAIL PRODUK</h3>
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th>Kategori</th>
                <th>Qty Terjual</th>
                <th>Pendapatan</th>
                <th>Modal</th>
                <th>Keuntungan</th>
                <th>Margin</th>
            </tr>
        </thead>
        <tbody>
            @forelse($product_stats as $stat)
            <tr>
                <td>{{ $stat->product_name }}</td>
                <td>{{ $stat->category_name ?: 'Tanpa Kategori' }}</td>
                <td class="text-center">{{ number_format($stat->total_quantity, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($stat->total_revenue, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($stat->total_modal, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($stat->total_profit, 0, ',', '.') }}</td>
                <td class="text-center">{{ $stat->total_revenue > 0 ? number_format(($stat->total_profit / $stat->total_revenue) * 100, 1) : 0 }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
