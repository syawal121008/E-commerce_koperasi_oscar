<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Top Up</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Gaya kustom untuk halaman dan pencetakan */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6; /* Latar belakang abu-abu muda */
        }

        /* Gaya untuk mencetak struk */
        @media print {
            body * {
                visibility: hidden;
            }
            .print-area, .print-area * {
                visibility: visible;
                /* Pastikan semua elemen di dalam print-area tidak tersembunyi */
            }
            .print-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
            /* Pastikan gradien dan warna dicetak */
            .bg-gradient-to-r {
                background: #3B82F6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .bg-green-100 { background-color: #d1fae5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .text-green-800 { color: #065f46 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .bg-yellow-100 { background-color: #fef3c7 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .text-yellow-800 { color: #92400e !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .bg-red-100 { background-color: #fee2e2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .text-red-800 { color: #991b1b !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .bg-gray-50 { background-color: #f9fafb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .bg-blue-50 { background-color: #eff6ff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .bg-purple-50 { background-color: #f5f3ff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .border { border-color: #d1d5db !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .border-green-200 { border-color: #a7f3d0 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .border-blue-200 { border-color: #bfdbfe !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .border-purple-200 { border-color: #e9d5ff !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            
            /* --- [MODIFIKASI KUAT V3] ATURAN CSS AGAR PAS SATU HALAMAN & TETAP BAGUS --- */
            @page {
                size: A4;
                margin: 5mm !important; /* Memberi sedikit margin minimal 5mm di semua sisi */
            }
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background-color: #fff !important; /* Pastikan latar belakang putih saat dicetak */
                min-width: unset !important; /* Pastikan tidak ada lebar minimal yang mengganggu */
                overflow: hidden; /* Sembunyikan overflow jika terjadi */
            }

            .py-12 {
                padding-top: 0.5rem !important;
                padding-bottom: 0.5rem !important;
            }
            .max-w-4xl {
                max-width: 100% !important; /* Gunakan lebar penuh yang tersedia */
                margin-left: auto !important;
                margin-right: auto !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            .print-area {
                box-shadow: none !important; /* Hapus bayangan saat dicetak */
                border-radius: 0 !important; /* Hapus border-radius */
                overflow: visible !important; /* Pastikan konten tidak terpotong */
                /* Hapus transform: scale() dan width: calc() */
            }

            /* Penyesuaian font-size dan line-height untuk menghemat ruang vertikal */
            h1 { font-size: 2rem !important; line-height: 1.1 !important; margin-bottom: 0.5rem !important; } /* Mengurangi h1 */
            p { font-size: 0.875rem !important; line-height: 1.2 !important; } /* Mengurangi font paragraph */
            label { font-size: 0.75rem !important; line-height: 1.1 !important; } /* Mengurangi font label */
            .text-lg { font-size: 1rem !important; line-height: 1.2 !important; } /* Mengurangi text-lg menjadi 1rem (default browser) */
            .text-sm { font-size: 0.75rem !important; line-height: 1.1 !important; } /* Mempertahankan text-sm */
            .text-xs { font-size: 0.65rem !important; line-height: 1 !important; } /* Memperkecil text-xs */

            /* Mengurangi padding dan margin secara lebih agresif */
            .p-8 {
                padding: 0.5rem !important; /* Padding area utama */
            }
            .p-6 {
                padding: 0.5rem !important; /* Padding di dalam setiap kartu informasi */
            }
            .py-3 {
                padding-top: 0.25rem !important;
                padding-bottom: 0.25rem !important;
            }
            .px-6 {
                padding-left: 0.75rem !important;
                padding-right: 0.75rem !important;
            }

            .mb-2 { margin-bottom: 0.25rem !important; }
            .mb-4 { margin-bottom: 0.5rem !important; }
            .mb-6 { margin-bottom: 0.75rem !important; }
            .mt-1 { margin-top: 0.25rem !important; }
            .mt-8 { margin-top: 0.75rem !important; }
            .mt-12 { margin-top: 1rem !important; }

            .pb-2 { padding-bottom: 0.25rem !important; }
            .pt-6 { padding-top: 0.5rem !important; }

            .space-y-8 > * + * {
                margin-top: 0.75rem !important; /* Jarak antar blok informasi */
            }
            .space-y-3 > * + * {
                margin-top: 0.5rem !important; /* Jarak antar item di Riwayat Transaksi */
            }
            .gap-4 {
                gap: 0.5rem !important; /* Jarak antar kolom di grid */
            }
            
            /* Pastikan elemen flex mengecil jika perlu */
            .flex {
                flex-shrink: 1 !important;
                flex-wrap: wrap !important; /* Memungkinkan wrap jika konten terlalu panjang */
            }
            .grid {
                display: grid !important; /* Pastikan grid tetap berfungsi */
                grid-template-columns: 1fr !important; /* Ubah ke satu kolom untuk responsif */
            }
            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important; /* Coba pertahankan 2 kolom jika muat */
            }
            .md\:grid-cols-2 > div {
                min-width: 0 !important; /* Memungkinkan div mengecil */
            }

            /* Mengurangi tinggi icon jika perlu */
            svg {
                width: 0.9rem !important;
                height: 0.9rem !important;
            }
            /* --- [AKHIR MODIFIKASI KUAT V3] --- */
        }
    </style>
</head>
<body class="dark:bg-gray-900">

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg print-area">
                
                <div class="bg-gradient-to-r from-blue-600 to-purple-700 p-6 text-white">
                    <div class="text-center">
                        <h1 class="text-3xl font-bold mb-2">BUKTI ISI SALDO</h1>
                        <p class="text-blue-100">Struk Transaksi</p>
                    </div>
                </div>

                <div class="p-8">
                    <div class="flex justify-center mb-6">
                        @if($topup->status == 'success')
                            <span class="inline-flex items-center px-6 py-3 rounded-full text-sm font-bold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                ISI SALDO BERHASIL
                            </span>
                        @elseif($topup->status == 'pending')
                            <span class="inline-flex items-center px-6 py-3 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                </svg>
                                MENUNGGU PERSETUJUAN
                            </span>
                        @else
                            <span class="inline-flex items-center px-6 py-3 rounded-full text-sm font-bold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                ISI SALDO GAGAL
                            </span>
                        @endif
                    </div>

                    <div class="space-y-8">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-600 pb-2">
                                Informasi Siswa
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nama Lengkap</label>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $topup->user->full_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">NIS/NIP</label>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $topup->user->student_id ?? '-' }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Peran</label>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($topup->user->role_name) }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $topup->user->email }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border border-blue-200 dark:border-blue-700">
                            <h3 class="text-lg font-semibold mb-4 text-blue-800 dark:text-blue-200 border-b border-blue-200 dark:border-blue-700 pb-2">
                                Detail Transaksi
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Referensi Pembayaran</label>
                                    <p class="text-lg font-mono font-bold text-blue-700 dark:text-blue-300 bg-white dark:bg-gray-800 px-3 py-1 rounded border">{{ $topup->payment_reference }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Jumlah Top Up</label>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $topup->formatted_amount }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Metode Pembayaran</label>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $topup->method_name }}</p>
                                </div>
                                @if($topup->payment_gateway)
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Gerbang Pembayaran</label>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($topup->payment_gateway) }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-600 pb-2">
                                Informasi Waktu
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Dibuat</label>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $topup->created_at->format('d F Y, H:i:s') }} WIB
                                    </p>
                                </div>
                                @if($topup->updated_at != $topup->created_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">
                                        @if($topup->status == 'success')
                                            Tanggal Disetujui
                                        @elseif($topup->status == 'failed')
                                            Tanggal Ditolak
                                        @else
                                            Terakhir Diperbarui
                                        @endif
                                    </label>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                        {{ $topup->updated_at->format('d F Y, H:i:s') }} WIB
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>

                        @if($topup->approver)
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-6 border border-green-200 dark:border-green-700">
                            <h3 class="text-lg font-semibold mb-4 text-green-800 dark:text-green-200 border-b border-green-200 dark:border-green-700 pb-2">
                                Informasi Persetujuan
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Disetujui Oleh</label>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $topup->approver->full_name }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400">Peran</label>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($topup->approver->role_name) }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($topup->transaction)
<div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700">
    <h3 class="text-lg font-semibold mb-4 text-purple-800 dark:text-purple-200 border-b border-purple-200 dark:border-purple-700 pb-2">
        Riwayat Transaksi
    </h3>
    <div class="space-y-3">
        <div class="flex justify-between items-center">
            <span class="text-gray-600 dark:text-gray-400">ID Transaksi:</span>
            <span class="font-mono text-purple-700 dark:text-purple-300">{{ $topup->transaction->transaction_id ?? 'N/A' }}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-gray-600 dark:text-gray-400">Status Transaksi:</span>
            <span class="font-semibold 
                @if(in_array($topup->transaction->status ?? $topup->status, ['success', 'paid', 'completed'])) text-green-600 dark:text-green-400
                @elseif(($topup->transaction->status ?? $topup->status) == 'pending') text-yellow-600 dark:text-yellow-400
                @else text-red-600 dark:text-red-400
                @endif">
                @if(in_array($topup->transaction->status ?? $topup->status, ['success', 'paid', 'completed']))
                    Berhasil
                @elseif(($topup->transaction->status ?? $topup->status) == 'pending')
                    Menunggu
                @else
                    Gagal
                @endif
            </span>
        </div>
        <div class="flex justify-between items-start">
            <span class="text-gray-600 dark:text-gray-400">Deskripsi:</span>
            <span class="text-right text-gray-900 dark:text-gray-100 max-w-md">{{ $topup->transaction->description ?? 'Top up sebesar ' . $topup->formatted_amount }}</span>
        </div>
    </div>
</div>
@else
<div class="bg-purple-50 dark:bg-purple-900/20 rounded-xl p-6 border border-purple-200 dark:border-purple-700">
    <h3 class="text-lg font-semibold mb-4 text-purple-800 dark:text-purple-200 border-b border-purple-200 dark:border-purple-700 pb-2">
        Riwayat Transaksi
    </h3>
    <div class="space-y-3">
        <div class="flex justify-between items-center">
            <span class="text-gray-600 dark:text-gray-400">Referensi Top Up:</span>
            <span class="font-mono text-purple-700 dark:text-purple-300">{{ $topup->payment_reference }}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-gray-600 dark:text-gray-400">Status:</span>
            <span class="font-semibold 
                @if($topup->status == 'success') text-green-600 dark:text-green-400
                @elseif($topup->status == 'pending') text-yellow-600 dark:text-yellow-400
                @else text-red-600 dark:text-red-400
                @endif">
                @if($topup->status == 'success')
                    Berhasil
                @elseif($topup->status == 'pending')
                    Menunggu
                @else
                    Gagal
                @endif
            </span>
        </div>
        <div class="flex justify-between items-start">
            <span class="text-gray-600 dark:text-gray-400">Deskripsi:</span>
            <span class="text-right text-gray-900 dark:text-gray-100 max-w-md">Top up sebesar {{ $topup->formatted_amount }}</span>
        </div>
    </div>
</div>
@endif
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center no-print">
                        <button onclick="window.print()" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-md transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            Cetak Bukti
                        </button>
                        @php
                            $role = auth()->user()->role ?? '';
                        @endphp

                        @if($role === 'customer')
                            <a href="{{ route('topup.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow-md transition-all duration-200 text-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Kembali
                            </a>
                        @elseif($role === 'admin' || $role === 'guru')
                            <a href="{{ route('supervisor.topups') }}" class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow-md transition-all duration-200 text-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Kembali
                            </a>
                        @endif
                    </div>

                    <div class="mt-12 pt-6 border-t border-gray-200 dark:border-gray-600 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Bukti ini digenerate secara otomatis pada {{ now()->format('d F Y, H:i:s') }} WIB
                        </p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                            &copy; {{ date('Y') }} Sistem Koperasi SMKIUTAMA - Semua hak dilindungi
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>