<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl sm:text-2xl text-gray-800 leading-tight">
                    {{ __('Riwayat Transaksi') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Kelola dan lihat semua transaksi Anda</p>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500 bg-gray-50 px-3 py-2 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <span class="font-medium">{{ $transactions->total() }}</span> transaksi
            </div>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6 lg:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            {{-- Filter Section - Enhanced --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-4 sm:px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-gray-900">Filter Transaksi</h3>
                    </div>
                </div>
                
                <div class="p-4 sm:p-6">
                    <form method="GET" action="{{ route('transactions.index') }}" class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-6">
                            <div class="space-y-2">
                                <label for="type" class="block text-sm font-semibold text-gray-700">Jenis Transaksi</label>
                                <div class="relative">
                                    <select name="type" id="type" class="w-full pl-4 pr-10 py-3 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 appearance-none">
                                        <option value="">Semua Jenis</option>
                                        <option value="topup" {{ request('type') == 'topup' ? 'selected' : '' }}>💰 Top Up</option>
                                        <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>💳 Pembayaran</option>
                                        <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>📈 Pendapatan</option>
                                        <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>📉 Pengeluaran</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="status" class="block text-sm font-semibold text-gray-700">Status</label>
                                <div class="relative">
                                    <select name="status" id="status" class="w-full pl-4 pr-10 py-3 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 appearance-none">
                                        <option value="">Semua Status</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Menunggu</option>
                                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>✅ Berhasil</option>
                                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>🎯 Selesai</option>
                                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>❌ Gagal</option>
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label for="start_date" class="block text-sm font-semibold text-gray-700">Dari Tanggal</label>
                                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" 
                                       class="w-full px-4 py-3 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            </div>

                            <div class="space-y-2">
                                <label for="end_date" class="block text-sm font-semibold text-gray-700">Sampai Tanggal</label>
                                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" 
                                       class="w-full px-4 py-3 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-100">
                            <button type="submit" class="flex-1 sm:flex-none bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Terapkan Filter
                            </button>
                            <a href="{{ route('transactions.export.student', array_merge(['user' => Auth::user()->user_id], request()->query())) }}"
                               class="flex-1 sm:flex-none bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center shadow-sm hover:shadow-md">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download Excel
                            </a>
                            @if(request()->hasAny(['type', 'status', 'start_date', 'end_date']))
                            <a href="{{ route('transactions.index') }}" class="flex-1 sm:flex-none bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg transition-all duration-200 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reset Filter
                            </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- Mobile Card View --}}
            <div class="block sm:hidden space-y-4">
                @forelse($transactions as $transaction)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-200">
                    <div class="p-4 space-y-3">
                        {{-- Header --}}
                        <div class="flex items-start justify-between">
                            <div class="flex items-center space-x-3">
                                @php
                                    // Logika untuk menentukan siapa yang harus ditampilkan
                                    $displayUser = null;
                                    if ($transaction->type == 'income' && $transaction->order && $transaction->order->customer) {
                                        // Jika ini adalah pendapatan dari pesanan, tampilkan PELANGGAN
                                        $displayUser = $transaction->order->customer;
                                    } else {
                                        // Jika tidak, tampilkan pengguna yang tercatat di transaksi (misal: untuk topup)
                                        $displayUser = $transaction->user;
                                    }
                                @endphp

                                @if ($displayUser)
                                    <img class="h-10 w-10 rounded-full border-2 border-gray-100" src="{{ $displayUser->profile_photo_url }}" alt="{{ $displayUser->name }}">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $displayUser->full_name ?? $displayUser->name }}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $displayUser->email }}</div>
                                    </div>
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="text-sm font-medium text-gray-500">Tidak Diketahui</div>
                                @endif
                            </div>
                            
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900">
                                    Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>

                        {{-- Status and Type --}}
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full
                                @if($transaction->type == 'topup') bg-green-100 text-green-800
                                @elseif($transaction->type == 'payment') bg-blue-100 text-blue-800
                                @elseif($transaction->type == 'income') bg-purple-100 text-purple-800
                                @elseif($transaction->type == 'expense') bg-orange-100 text-orange-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ $transaction->type_label }}
                            </span>

                            <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full
                                @if($transaction->status == 'paid' || $transaction->status == 'completed') bg-green-100 text-green-800
                                @elseif($transaction->status == 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                @if($transaction->status == 'paid' || $transaction->status == 'completed')
                                    @if($transaction->type == 'topup')
                                        Berhasil
                                    @else
                                        {{ $transaction->status_label }}
                                    @endif
                                @else
                                    {{ $transaction->status_label }}
                                @endif
                            </span>
                        </div>

                        {{-- Action --}}
                        <div class="pt-2 border-t border-gray-100">
                            <a href="{{ route('transactions.show', $transaction->transaction_id) }}" 
                               class="w-full flex items-center justify-center text-blue-600 hover:text-blue-800 font-semibold text-sm py-2 px-4 rounded-lg hover:bg-blue-50 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-500 mb-2">Tidak ada transaksi</h3>
                    <p class="text-gray-400">Coba ubah filter pencarian Anda</p>
                </div>
                @endforelse
            </div>

            {{-- Desktop Table View --}}
            <div class="hidden sm:block bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">No</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Pengguna / Pelanggan</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Jumlah</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($transactions as $index => $transaction)
                            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 group">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-600">
                                    {{ ($transactions->currentPage() - 1) * $transactions->perPage() + $loop->iteration }}
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @php
                                            // Logika untuk menentukan siapa yang harus ditampilkan
                                            $displayUser = null;
                                            if ($transaction->type == 'income' && $transaction->order && $transaction->order->customer) {
                                                // Jika ini adalah pendapatan dari pesanan, tampilkan PELANGGAN
                                                $displayUser = $transaction->order->customer;
                                            } else {
                                                // Jika tidak, tampilkan pengguna yang tercatat di transaksi (misal: untuk topup)
                                                $displayUser = $transaction->user;
                                            }
                                        @endphp
        
                                        @if ($displayUser)
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full border-2 border-gray-100 group-hover:border-blue-200 transition-colors duration-200" src="{{ $displayUser->profile_photo_url }}" alt="{{ $displayUser->name }}">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ $displayUser->full_name ?? $displayUser->name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $displayUser->email }}
                                                </div>
                                            </div>
                                        @else
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-500">
                                                        Tidak Diketahui
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full
                                        @if($transaction->type == 'topup') bg-green-100 text-green-800 border border-green-200
                                        @elseif($transaction->type == 'payment') bg-blue-100 text-blue-800 border border-blue-200
                                        @elseif($transaction->type == 'income') bg-purple-100 text-purple-800 border border-purple-200
                                        @elseif($transaction->type == 'expense') bg-orange-100 text-orange-800 border border-orange-200
                                        @else bg-gray-100 text-gray-800 border border-gray-200 @endif">
                                        {{ $transaction->type_label }}
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full
                                        @if($transaction->status == 'paid' || $transaction->status == 'completed') bg-green-100 text-green-800 border border-green-200
                                        @elseif($transaction->status == 'pending') bg-yellow-100 text-yellow-800 border border-yellow-200
                                        @else bg-red-100 text-red-800 border border-red-200 @endif">
                                        @if($transaction->status == 'paid' || $transaction->status == 'completed')
                                            @if($transaction->type == 'topup')
                                                Berhasil
                                            @else
                                                {{ $transaction->status_label }}
                                            @endif
                                        @else
                                            {{ $transaction->status_label }}
                                        @endif
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ $transaction->created_at->format('d/m/Y') }}</span>
                                        <span class="text-xs text-gray-400">{{ $transaction->created_at->format('H:i') }}</span>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('transactions.show', $transaction->transaction_id) }}" 
                                       class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold text-sm bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg transition-all duration-200 group-hover:shadow-sm">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-500 mb-2">Tidak ada transaksi ditemukan</h3>
                                        <p class="text-gray-400">Coba ubah filter pencarian Anda untuk melihat lebih banyak hasil</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Enhanced Pagination --}}
            @if($transactions->hasPages())
            <div class="flex justify-center">
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm px-4 py-3">
                    {{ $transactions->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Bottom spacing for mobile --}}
    <div class="pb-6 sm:pb-0"></div>
</x-app-layout>