<?php

namespace App\Exports;

use App\Models\Topup;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TopupsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Topup::with(['user'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'customer'); // Only show customer topups
            })
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($this->filters['student_id'])) {
            $query->where('user_id', $this->filters['student_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Waktu', 
            'ID Topup',
            'Nama Siswa',
            'NIS/ID Siswa',
            'Jumlah (Rp)',
            'Metode',
            'Status',
            'Referensi Pembayaran',
            'Disetujui Oleh',
            'Catatan'
        ];
    }

    /**
     * @param mixed $topup
     * @return array
     */
    public function map($topup): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        // Get status in Indonesian
        $statusMap = [
            'pending' => 'Menunggu',
            'paid' => 'Berhasil',
            'failed' => 'Gagal'
        ];

        // Get method name in Indonesian
        $methodMap = [
            'koperasi' => 'Koperasi',
            'ewallet' => 'E-Wallet',
            'qris' => 'QRIS',
            'scan_qr' => 'Scan QR'
        ];

        return [
            $rowNumber,
            $topup->created_at->format('d/m/Y'),
            $topup->created_at->format('H:i:s'),
            $topup->topup_id,
            $topup->user->full_name ?? 'N/A',
            $topup->user->student_id ?? '-',
            $topup->amount,
            $methodMap[$topup->method] ?? ucfirst($topup->method),
            $statusMap[$topup->status] ?? ucfirst($topup->status),
            $topup->payment_reference,
            $topup->approver->full_name ?? '-',
            $this->getStatusNote($topup)
        ];
    }

    /**
     * Get status note for the topup
     */
    private function getStatusNote($topup): string
    {
        switch ($topup->status) {
            case 'pending':
                return 'Menunggu persetujuan admin';
            case 'paid':
                return 'Top up berhasil diproses';
            case 'failed':
                return 'Top up ditolak atau gagal';
            default:
                return '-';
        }
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Get the highest row and column
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            
            // All cells border and alignment
            'A1:' . $highestColumn . $highestRow => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],

            // Amount column (G) - right align and number format
            'G:G' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0',
                ],
            ],

            // Center align for specific columns
            'A:A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // No
            'B:C' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Date & Time
            'H:I' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]], // Method & Status
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 12,  // Tanggal
            'C' => 10,  // Waktu
            'D' => 15,  // ID Topup
            'E' => 25,  // Nama Siswa
            'F' => 15,  // NIS/ID Siswa
            'G' => 15,  // Jumlah
            'H' => 12,  // Metode
            'I' => 12,  // Status
            'J' => 20,  // Referensi
            'K' => 20,  // Disetujui Oleh
            'L' => 30,  // Catatan
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Rekap Top Up';
    }
}