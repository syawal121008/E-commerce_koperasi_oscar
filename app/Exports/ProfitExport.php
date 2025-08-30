<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProfitExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $productStats;
    protected $summary;

    public function __construct($productStats, $summary)
    {
        $this->productStats = $productStats;
        $this->summary = $summary;
    }

    public function collection()
    {
        return $this->productStats;
    }

    public function headings(): array
    {
        return [
            'Produk',
            'Kategori', 
            'Qty Terjual',
            'Pendapatan (Rp)',
            'Modal (Rp)',
            'Keuntungan (Rp)',
            'Margin (%)'
        ];
    }

    public function map($row): array
    {
        return [
            $row->product_name,
            $row->category_name ?: 'Tanpa Kategori',
            $row->total_quantity,
            $row->total_revenue,
            $row->total_modal,
            $row->total_profit,
            $row->total_revenue > 0 ? round(($row->total_profit / $row->total_revenue) * 100, 2) : 0
        ];
    }

    public function title(): string
    {
        return 'Laporan Keuntungan';
    }
}