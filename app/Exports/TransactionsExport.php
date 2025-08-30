<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $userId;
    protected $filters; // Tambahkan properti untuk menampung filter

    /**
     * Update constructor untuk menerima array filter.
     *
     * @param string|null $userId
     * @param array $filters
     */
    public function __construct(?string $userId = null, array $filters = [])
    {
        $this->userId = $userId;
        $this->filters = $filters; // Simpan filternya
    }

    /**
     * Update query untuk menerapkan filter yang diterima.
     */
    public function query()
    {
        $query = Transaction::query()->with('user')->orderBy('created_at', 'desc');

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        // Terapkan filter jika ada
        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['start_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['start_date']);
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['end_date']);
        }

        return $query;
    }

    /**
     * (Tidak ada perubahan di sini)
     */
    public function headings(): array
    {
        return [
            'ID Transaksi',
            'Nama Siswa',
            'NIS/ID Siswa',
            'Tanggal',
            'Tipe',
            'Jumlah',
            'Status',
            'Deskripsi',
        ];
    }

    /**
     * (Tidak ada perubahan di sini)
     */
    public function map($transaction): array
    {
        return [
            $transaction->transaction_id,
            $transaction->user ? $transaction->user->full_name : 'N/A',
            $transaction->user ? $transaction->user->student_id : 'N/A',
            $transaction->created_at->format('d-m-Y H:i:s'),
            $transaction->type_label,
            $transaction->amount,
            $transaction->status_label,
            $transaction->description,
        ];
    }
}