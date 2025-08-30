<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{-- Change title based on user role --}}
                @if(auth()->user()->role == 'admin')
                    {{ __('Semua Riwayat Top Up') }}
                @else
                    {{ __('Riwayat Top Up Anda') }}
                @endif
            </h2>
            <div class="flex items-center space-x-4">
                <div class="hidden sm:block text-sm text-gray-500 dark:text-gray-400">
                    Total: {{ $topups->total() }} top up
                </div>
                <a href="{{ route('topup.qris') }}" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 shadow-sm">
                    Top Up Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('topup_paid'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <div class="flex items-center justify-between">
                        <span class="block sm:inline">{{ session('topup_paid') }}</span>
                        @if(session('topup_id'))
                            <a href="{{ route('topup.receipt', session('topup_id')) }}" 
                               class="ml-4 bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors">
                                Lihat Bukti
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if($topups->count() > 0)
                        <!-- Desktop Table View -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        {{-- Add Customer Name column for Admin --}}
                                        @if(auth()->user()->role == 'admin')
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Customer</th>
                                        @endif
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Referensi</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Jumlah</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Metode</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($topups as $topup)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-200">
                                            {{-- Add Customer Name data for Admin --}}
                                            @if(auth()->user()->role == 'admin')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $topup->user->full_name ?? 'N/A' }}
                                                </td>
                                            @endif
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                <div class="flex flex-col">
                                                    <span class="font-mono text-blue-600 dark:text-blue-400">{{ $topup->payment_reference }}</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">#{{ $topup->topup_id }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                <span class="font-semibold text-lg">{{ $topup->formatted_amount }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                <div class="flex flex-col">
                                                    <span class="font-medium">{{ $topup->method_name }}</span>
                                                    @if($topup->payment_gateway)
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst($topup->payment_gateway) }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @switch($topup->status)
                                                    @case('pending')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Menunggu</span>
                                                        @break
                                                    @case('success')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Berhasil</span>
                                                        @break
                                                    @case('failed')
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Gagal</span>
                                                        @break
                                                    @default
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">{{ ucfirst($topup->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                <div class="flex flex-col">
                                                    <span>{{ $topup->created_at->format('d M Y') }}</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $topup->created_at->format('H:i') }} WIB</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <a href="{{ route('topup.receipt', $topup->topup_id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition-colors duration-200">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card View -->
                        <div class="block md:hidden space-y-4">
                            @foreach($topups as $topup)
                                <div class="bg-white dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-4">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex-1">
                                            <p class="text-sm font-mono text-blue-600 dark:text-blue-400 font-medium">{{ $topup->payment_reference }}</p>
                                            {{-- Add Customer Name data for Admin --}}
                                            @if(auth()->user()->role == 'admin')
                                                <p class="text-xs font-semibold text-gray-600 dark:text-gray-300">{{ $topup->user->full_name ?? 'N/A' }}</p>
                                            @endif
                                            <p class="text-xs text-gray-500 dark:text-gray-400">#{{ $topup->topup_id }}</p>
                                        </div>
                                        @switch($topup->status)
                                            @case('pending') <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Menunggu</span> @break
                                            @case('success') <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Berhasil</span> @break
                                            @case('failed') <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Gagal</span> @break
                                            @default <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">{{ ucfirst($topup->status) }}</span>
                                        @endswitch
                                    </div>
                                    <div class="mb-4">
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ $topup->formatted_amount }}</p>
                                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                            <span>{{ $topup->method_name }}</span>
                                            @if($topup->payment_gateway)
                                                <span class="mx-2">•</span>
                                                <span>{{ ucfirst($topup->payment_gateway) }}</span>
                                            @endif
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $topup->created_at->format('d M Y, H:i') }} WIB</p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <a href="{{ route('topup.receipt', $topup->topup_id) }}" class="flex-1 text-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">Detail</a>
                                        @if($topup->status === 'success')
                                            <a href="{{ route('topup.receipt.download', $topup->topup_id) }}" class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-md transition-colors duration-200" title="Download PDF">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $topups->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Belum ada riwayat top up</h3>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Mulai dengan melakukan top up pertama Anda.</p>
                            <div class="mt-6">
                                <a href="{{ route('topup.qris') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 shadow-sm">
                                    Top Up Sekarang
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
