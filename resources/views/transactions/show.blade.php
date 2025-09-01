<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Transaksi') }}
            </h2>
            <div class="flex space-x-2">
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-print mr-2"></i>
                    Cetak
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>

            <div class="mb-6">
                @if($transaction->status == 'paid')
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-3"></i>
                            <div>
                                <h4 class="text-green-800 font-semibold">Transaksi Berhasil</h4>
                                <p class="text-green-700 text-sm">Transaksi telah diproses dengan sukses</p>
                            </div>
                        </div>
                    </div>
                    @elseif($transaction->status == 'completed')
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center">
            <i class="fas fa-check-double text-blue-500 mr-3"></i>
            <div>
                <h4 class="text-blue-800 font-semibold">Transaksi Selesai</h4>
                <p class="text-blue-700 text-sm">Transaksi telah diselesaikan</p>
            </div>
        </div>
    </div>
                @elseif($transaction->status == 'pending')
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-clock text-yellow-500 mr-3"></i>
                            <div>
                                <h4 class="text-yellow-800 font-semibold">Transaksi Menunggu</h4>
                                <p class="text-yellow-700 text-sm">Transaksi sedang dalam proses verifikasi</p>
                            </div>
                        </div>
                    </div>
                @elseif($transaction->status == 'cancelled')
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-ban text-red-500 mr-3"></i>
                            <div>
                                <h4 class="text-red-800 font-semibold">Transaksi Dibatalkan</h4>
                                <p class="text-red-700 text-sm">Transaksi telah dibatalkan</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-times-circle text-red-500 mr-3"></i>
                            <div>
                                <h4 class="text-red-800 font-semibold">Transaksi Gagal</h4>
                                <p class="text-red-700 text-sm">Transaksi tidak dapat diproses</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                            <h3 class="text-lg font-semibold text-white flex items-center">
                                <i class="fas fa-receipt mr-2"></i>
                                Informasi Transaksi
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">ID Transaksi</span>
                                        <span class="text-lg font-semibold text-gray-900 font-mono">#{{ $transaction->transaction_id }}</span>
                                    </div>

                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">Jenis Transaksi</span>
                                        <div class="mt-1">
                                            @if($transaction->type == 'topup')
                                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">
                                                    <i class="fas fa-arrow-up mr-1"></i>
                                                    Isi Ulang Saldo
                                                </span>
                                            @elseif($transaction->type == 'payment')
                                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">
                                                    <i class="fas fa-shopping-cart mr-1"></i>
                                                    Pembayaran
                                                </span>
                                            @elseif($transaction->type == 'income')
                                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-purple-100 text-purple-800 rounded-full">
                                                    <i class="fas fa-coins mr-1"></i>
                                                    Pendapatan
                                                </span>
                                            @elseif($transaction->type == 'expense')
                                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-orange-100 text-orange-800 rounded-full">
                                                    <i class="fas fa-arrow-down mr-1"></i>
                                                    Pengeluaran
                                                </span>
                                            @elseif($transaction->type == 'refund')
                                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                    <i class="fas fa-undo mr-1"></i>
                                                    Pengembalian Dana
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-gray-100 text-gray-800 rounded-full">
                                                    <i class="fas fa-question mr-1"></i>
                                                    {{ $transaction->type_label }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">Status</span>
                                        <div class="mt-1">
                                            @if($transaction->status == 'paid')
                                            <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-green-100 text-green-800 rounded-full">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                    Dibayar
                                                </span>
                                                
                                            @elseif($transaction->status == 'pending')
                                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-yellow-100 text-yellow-800 rounded-full">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Menunggu
                                                </span>
                                            @elseif($transaction->status == 'cancelled')
                                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                                                    <i class="fas fa-ban mr-1"></i>
                                                    Dibatalkan
                                                </span>
                                            @elseif($transaction->status == 'failed')
                                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-red-100 text-red-800 rounded-full">
                                                    <i class="fas fa-times-circle mr-1"></i>
                                                    Gagal
                                                </span>
                                            @elseif($transaction->status == 'completed')
                                                <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-blue-100 text-blue-800 rounded-full">
                                                    <i class="fas fa-check-double mr-1"></i>
                                                        Selesai
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">Jumlah</span>
                                        {{-- AWAL PERUBAHAN --}}
                                        @if(in_array(auth()->user()->role, ['admin', 'guru']))
                                            {{-- Tampilan untuk Admin & Guru: Selalu positif dan hijau --}}
                                            <span class="text-2xl font-bold text-green-600">
                                                Rp {{ number_format(abs($transaction->amount), 0, ',', '.') }}
                                            </span>
                                        @else
                                            {{-- Tampilan untuk Customer: Dengan +/- dan warna kondisional --}}
                                            <span class="text-2xl font-bold 
                                                @if($transaction->isCredit()) text-green-600 
                                                @else text-red-600 @endif">
                                                @if($transaction->isCredit()) + @else - @endif
                                                Rp {{ number_format(abs($transaction->amount), 0, ',', '.') }}
                                            </span>
                                        @endif
                                        {{-- AKHIR PERUBAHAN --}}
                                    </div>

                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">Tanggal & Waktu</span>
                                        <span class="text-lg font-semibold text-gray-900">{{ $transaction->created_at->format('d F Y') }}</span>
                                        <span class="text-sm text-gray-600">{{ $transaction->created_at->format('H:i:s') }} WIB</span>
                                    </div>

                                    @if($transaction->reference_id)
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-500 uppercase tracking-wide">Referensi ID</span>
                                        <span class="text-sm font-mono bg-gray-100 px-2 py-1 rounded border">{{ $transaction->reference_id }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if($transaction->description)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-2">Deskripsi</span>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <p class="text-gray-800">{{ $transaction->description }}</p>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                            <h3 class="text-lg font-semibold text-white flex items-center">
                                <i class="fas fa-user mr-2"></i>
                                Informasi Pengguna
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="text-center pb-4 border-b border-gray-200">
                                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-user text-purple-600 text-2xl"></i>
                                    </div>
                                    <h4 class="font-semibold text-gray-900">{{ $transaction->user->full_name ?? 'N/A' }}</h4>
                                    @if(isset($transaction->user->student_id))
                                        <p class="text-sm text-gray-600">{{ $transaction->user->student_id }}</p>
                                    @endif
                                </div>

                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Email:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $transaction->user->email ?? 'N/A' }}</span>
                                    </div>

                                    @if(isset($transaction->user->balance))
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Saldo Saat Ini:</span>
                                        <span class="text-sm font-bold text-blue-600">
                                            Rp {{ number_format($transaction->user->balance, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                            <h3 class="text-lg font-semibold text-white flex items-center">
                                <i class="fas fa-history mr-2"></i>
                                Riwayat Waktu
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-3 h-3 bg-green-500 rounded-full mt-2"></div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">Transaksi Dibuat</p>
                                        <p class="text-sm text-gray-600">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</p>
                                    </div>
                                </div>
                                @if($transaction->updated_at != $transaction->created_at)
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-3 h-3 bg-blue-500 rounded-full mt-2"></div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">Terakhir Diperbarui</p>
                                        <p class="text-sm text-gray-600">{{ $transaction->updated_at->format('d/m/Y H:i:s') }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($transaction->status == 'pending' && auth()->user()->role == 'admin')
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-4">
                            <h3 class="text-lg font-semibold text-white flex items-center">
                                <i class="fas fa-cogs mr-2"></i>
                                Tindakan
                            </h3>
                        </div>
                        <div class="p-6">
                            <div class="space-y-3">
                                <button class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-times mr-2"></i>
                                    Batalkan Transaksi
                                </button>
                                <button class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-check mr-2"></i>
                                    Setujui Transaksi
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            .print\:hidden {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .shadow-sm, .shadow {
                box-shadow: none !important;
            }

            .bg-gradient-to-r {
                background: #4f46e5 !important;
                -webkit-print-color-adjust: exact;
            }
        }

        .transition-colors {
            transition-property: color, background-color, border-color;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 200ms;
        }
    </style>
    @endpush
</x-app-layout>