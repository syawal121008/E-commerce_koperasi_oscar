{{-- File: resources/views/components/notification-dropdown.blade.php --}}
{{-- PERBAIKAN: Props sesuai dengan yang dikirim dari navigation --}}
@props(['orders' => collect(), 'count' => 0])

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" 
            class="relative p-2 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 rounded-full transition-colors duration-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M15 17h5l-5 5v-5zM15.707 1.293A1 1 0 0014.293 2.707L18.586 7l-1.793 1.793a1 1 0 101.414 1.414L20 8.414V3a1 1 0 00-1-1h-5.586l1.293-1.293zM3 10a1 1 0 011-1h8a1 1 0 011 1v8a1 1 0 01-1 1H4a1 1 0 01-1-1v-8z"/>
        </svg>
        
        @if($count > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold animate-pulse shadow-sm">
                {{ $count > 99 ? '99+' : $count }}
            </span>
        @endif
    </button>

    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200" 
         x-transition:enter-start="transform opacity-0 scale-95" 
         x-transition:enter-end="transform opacity-100 scale-100" 
         x-transition:leave="transition ease-in duration-150" 
         x-transition:leave-start="transform opacity-100 scale-100" 
         x-transition:leave-end="transform opacity-0 scale-95"
         @click.away="open = false"
         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 py-1 ring-1 ring-black ring-opacity-5 z-50"
         style="display: none;">
        
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">Notifikasi Pesanan</h3>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $count }} Baru
                </span>
            </div>
        </div>

        <div class="max-h-80 overflow-y-auto">
            @forelse($orders as $order)
                <a href="{{ route('orders.show', $order->order_id) }}" 
                   class="block px-4 py-4 hover:bg-gray-50 border-b border-gray-50 transition-colors duration-150 group"
                   @click="open = false">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <img src="{{ $order->customer->profilePhotoUrl() ?? '' }}" 
                                alt="Profile Photo" 
                                class="w-10 h-10 object-cover rounded-full border-2 border-white shadow-sm"
                                onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($order->customer->full_name ?? 'Pelanggan') }}&background=e5e7eb&color=374151&size=40';">
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-700">
                                        {{-- PERBAIKAN UTAMA: Tampilkan nama customer --}}
                                        @if($order->customer)
                                            {{ $order->customer->full_name ?? $order->customer->name ?? 'Nama Tidak Tersedia' }}
                                        @else
                                            <span class="text-red-500">Customer Tidak Ditemukan</span>
                                        @endif
                                    </p>
                                    {{-- Info tambahan untuk identifikasi --}}
                                    @if($order->customer)
                                        <span class="text-xs text-gray-500">
                                            @if($order->customer->student_id)
                                                {{ $order->customer->student_id }}
                                            @endif
                                            @if($order->customer->student_id && $order->customer->email) • @endif
                                            @if($order->customer->email)
                                                {{ $order->customer->email }}
                                            @endif
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400 flex-shrink-0 ml-2">
                                    {{ $order->created_at->diffForHumans() }}
                                </span>
                            </div>
                            
                            <p class="text-xs text-gray-600 mt-1">
                                Pesanan #{{ $order->order_id }}
                            </p>
                            
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-sm font-bold text-green-600">
                                    Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                </p>
                                
                                @if ($order->status == 'pending')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 border border-yellow-200">
                                        <svg class="w-3 h-3 mr-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        Menunggu Pembayaran
                                    </span>
                                @elseif ($order->status == 'paid')
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        <svg class="w-3 h-3 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Telah Dibayar
                                    </span>
                                @endif
                            </div>

                            {{-- PERBAIKAN: Info jumlah item dengan pengecekan yang lebih robust --}}
                            @if($order->items && $order->items->count() > 0)
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $order->items->count() }} item
                                    @if($order->items->count() > 1)
                                        • {{ $order->items->first()->product->name ?? 'Produk' }} 
                                        dan {{ $order->items->count() - 1 }} lainnya
                                    @else
                                        • {{ $order->items->first()->product->name ?? 'Produk' }}
                                    @endif
                                </p>
                            @else
                                <p class="text-xs text-gray-500 mt-1">
                                    Tidak ada item
                                </p>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-4 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <svg class="mx-auto h-16 w-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" 
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-sm font-medium text-gray-900 mb-1">Tidak Ada Notifikasi Baru</p>
                        <p class="text-xs text-gray-500">Saat ini tidak ada pesanan baru.</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if($count > 0)
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                <a href="{{ route('orders.index') }}" 
                   class="block w-full text-center py-2 px-4 text-sm font-medium text-blue-600 hover:text-white hover:bg-blue-600 rounded-md transition-all duration-200 border border-blue-200 hover:border-blue-600"
                   @click="open = false">
                    Lihat Semua Pesanan ({{ $count }})
                </a>
            </div>
        @endif
    </div>
</div>