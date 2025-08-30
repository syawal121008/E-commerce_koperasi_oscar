<table border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th colspan="6" style="text-align: center; font-weight: bold; font-size: 14px;">
                LAPORAN KEUNTUNGAN KOPERASI
            </th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center; font-size: 12px;">
                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} -
                {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; background: #f2f2f2;">Produk</th>
            <th style="font-weight: bold; background: #f2f2f2;">Kategori</th>
            <th style="font-weight: bold; background: #f2f2f2;">Terjual</th>
            <th style="font-weight: bold; background: #f2f2f2;">Total Modal</th>
            <th style="font-weight: bold; background: #f2f2f2;">Total Pemasukan</th>
            <th style="font-weight: bold; background: #f2f2f2;">Keuntungan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($product_stats as $stat)
        <tr>
            <td>{{ $stat->product_name }}</td>
            <td>{{ $stat->category_name ?: '-' }}</td>
            <td style="text-align: center;">{{ number_format($stat->total_quantity, 0, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($stat->total_modal, 0, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($stat->total_revenue, 0, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($stat->total_profit, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight: bold; background: #f9f9f9;">
            <td colspan="2" style="text-align: center;">Total Keseluruhan</td>
            <td style="text-align: center;">{{ number_format($summary['total_quantity'], 0, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($summary['total_modal'], 0, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($summary['total_revenue'], 0, ',', '.') }}</td>
            <td style="text-align: right;">{{ number_format($summary['total_profit'], 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>
