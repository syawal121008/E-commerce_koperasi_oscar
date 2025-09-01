<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-box-open mr-2 text-blue-600"></i>Pesanan Pending
            <span class="text-sm text-gray-500 ml-2">({{ $orders->total() }} pesanan)</span>
        </h2>
    </x-slot>

    {{-- Kita gunakan Alpine.js untuk state management modal --}}
    <div x-data="{ modalOpen: false, selectedOrder: null }">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 text-gray-900">

                        @if(session('paid'))
                            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded-md" role="alert">
                                <p class="font-bold">Berhasil 🎉</p>
                                <p>{{ session('paid') }}</p>
                            </div>
                        @endif

                        {{-- Filter & Search Form --}}
                        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
                            {{-- Form Pencarian Pelanggan --}}
                            <form action="{{ route('orders.pending') }}" method="GET" class="w-full md:w-1/3" onsubmit="return false;">
                                <div class="relative">
                                    <input type="text" name="search" placeholder="Cari nama pelanggan..." 
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            value="{{ request('search') }}"
                                            @keydown.enter.prevent
                                            x-ref="searchForm"
                                            onkeyup="if(event.key !== 'Enter') this.form.submit()">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-search text-gray-400"></i>
                                    </div>
                                </div>
                            </form>

                            {{-- Filter Status --}}
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('orders.pending') }}" 
                                   class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200 
                                          {{ !request('search') ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    <i class="fas fa-clock mr-1"></i>
                                    Semua Pending ({{ $orders->total() }})
                                </a>
                            </div>
                        </div>

                        {{-- Tampilan Tabel (Desktop & Tablet) --}}
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="min-w-full bg-white rounded-lg overflow-hidden">
                                <thead class="bg-blue-600 text-white">
                                    <tr>
                                        <th class="py-3 px-4 text-left">ID Pesanan</th>
                                        <th class="py-3 px-4 text-left">Pelanggan</th>
                                        <th class="py-3 px-4 text-left">Tanggal</th>
                                        <th class="py-3 px-4 text-center">Status</th>
                                        <th class="py-3 px-4 text-right">Total Harga</th>
                                        <th class="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-700">
                                    @forelse($orders as $order)
                                        <tr class="hover:bg-blue-50 border-b border-gray-200 transition-colors duration-150">
                                            <td class="py-4 px-4 text-left font-mono text-sm text-gray-600">#{{ $order->order_id }}</td>
                                            <td class="py-4 px-4">
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-bold mr-3">
                                                        {{ substr($order->customer->full_name ?? 'U', 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-medium text-gray-800">{{ $order->customer->full_name ?? 'Tidak Ada' }}</div>
                                                        <div class="text-sm text-gray-500">{{ $order->customer->email ?? '' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-4 px-4 text-sm text-gray-600">
                                                <div>{{ $order->created_at->format('d/m/Y') }}</div>
                                                <div class="text-gray-500">{{ $order->created_at->format('H:i') }}</div>
                                            </td>
                                            <td class="py-4 px-4 text-center">
                                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-200 text-blue-800 animate-pulse">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    Menunggu Pembayaran
                                                </span>
                                            </td>
                                            <td class="py-4 px-4 text-right">
                                                <div class="font-semibold text-lg text-blue-700">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                                                <div class="text-xs text-gray-500">
                                                    @if($order->payment_method === 'balance')
                                                        <i class="fas fa-wallet mr-1"></i>Saldo
                                                    @elseif($order->payment_method === 'cash')
                                                        <i class="fas fa-money-bill mr-1"></i>Tunai
                                                    @elseif($order->payment_method === 'mixed')
                                                        <i class="fas fa-coins mr-1"></i>Campuran
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-4 px-4 text-center">
                                                <div class="flex justify-center items-center space-x-2">
                                                    {{-- Tombol Lihat Detail --}}
                                                    <a href="{{ route('orders.show', $order->order_id) }}" 
                                                       class="text-gray-600 hover:text-blue-600 p-2 hover:bg-blue-100 rounded-full transition-colors" 
                                                       title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    {{-- Tombol Ubah Status --}}
                                                    <button @click="modalOpen = true; selectedOrder = {{ $order }}" 
                                                            class="text-gray-600 hover:text-green-600 p-2 hover:bg-green-100 rounded-full transition-colors" 
                                                            title="Ubah Status">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                    {{-- Tombol Batalkan --}}
                                                    <form action="{{ route('orders.cancel', $order->order_id) }}" method="POST" class="inline"
                                                          onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" 
                                                                class="text-gray-600 hover:text-red-600 p-2 hover:bg-red-100 rounded-full transition-colors" 
                                                                title="Batalkan Pesanan">
                                                            <i class="fas fa-times-circle"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="py-12 px-4 text-center">
                                                <div class="flex flex-col items-center justify-center text-gray-500">
                                                    <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                                                    <h3 class="text-lg font-medium mb-2">Tidak Ada Pesanan Pending</h3>
                                                    <p class="text-sm">Semua pesanan sudah diproses atau belum ada pesanan baru.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Tampilan Kartu (Mobile) --}}
                        <div class="sm:hidden space-y-4">
                            @forelse($orders as $order)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 shadow-sm">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="font-bold text-sm text-blue-800">#{{ $order->order_id }}</div>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-200 text-blue-800 animate-pulse">
                                            <i class="fas fa-clock mr-1"></i>
                                            Menunggu Pembayaran
                                        </span>
                                    </div>
                                    <div class="border-t border-blue-200 pt-2">
                                        <div class="mb-1">
                                            <div class="text-xs text-gray-500">Pelanggan</div>
                                            <div class="font-medium text-sm">{{ $order->customer->full_name ?? 'Tidak Ada' }}</div>
                                        </div>
                                        <div class="mb-1">
                                            <div class="text-xs text-gray-500">Tanggal</div>
                                            <div class="font-medium text-sm">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                        </div>
                                        <div class="mb-1">
                                            <div class="text-xs text-gray-500">Total</div>
                                            <div class="font-bold text-lg text-blue-800">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="text-xs text-gray-500">Metode Pembayaran</div>
                                            <div class="font-medium text-sm">
                                                @if($order->payment_method === 'balance')
                                                    <i class="fas fa-wallet mr-1"></i>Saldo
                                                @elseif($order->payment_method === 'cash')
                                                    <i class="fas fa-money-bill mr-1"></i>Tunai
                                                @elseif($order->payment_method === 'mixed')
                                                    <i class="fas fa-coins mr-1"></i>Campuran
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-end space-x-2 mt-4">
                                        {{-- Tombol Lihat Detail --}}
                                        <a href="{{ route('orders.show', $order->order_id) }}" 
                                           class="text-gray-600 hover:text-blue-600 p-2 hover:bg-blue-100 rounded-full transition-colors" 
                                           title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {{-- Tombol Ubah Status --}}
                                        <button @click="modalOpen = true; selectedOrder = {{ $order }}" 
                                                class="text-gray-600 hover:text-green-600 p-2 hover:bg-green-100 rounded-full transition-colors" 
                                                title="Ubah Status">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                        {{-- Tombol Batalkan --}}
                                        <form action="{{ route('orders.cancel', $order->order_id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="text-gray-600 hover:text-red-600 p-2 hover:bg-red-100 rounded-full transition-colors" 
                                                    title="Batalkan Pesanan">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="py-12 px-4 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                                        <h3 class="text-lg font-medium mb-2">Tidak Ada Pesanan Pending</h3>
                                        <p class="text-sm">Semua pesanan sudah diproses atau belum ada pesanan baru.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        {{-- Pagination --}}
                        @if($orders->hasPages())
                            <div class="mt-8">
                                {{ $orders->appends(request()->query())->links() }}
                            </div>
                        @endif

                        {{-- Summary Stats --}}
                        @if($orders->count() > 0)
                            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex flex-col sm:flex-row items-center justify-between">
                                    <div class="flex items-center text-blue-800 mb-2 sm:mb-0">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        <span class="font-medium">{{ $orders->total() }} pesanan menunggu pembayaran</span>
                                    </div>
                                    <div class="text-blue-700 font-semibold">
                                        Total: Rp {{ number_format($orders->sum('total_price'), 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Ubah Status --}}
        <div x-show="modalOpen" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" 
             style="display: none;">
            
            <div @click.away="modalOpen = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4 transform transition-all scale-100 duration-300">
                <h3 class="text-lg font-semibold mb-4 text-blue-800 flex items-center">
                    <i class="fas fa-edit mr-2"></i> Ubah Status Pesanan 
                    <span x-text="'#' + selectedOrder?.order_id" class="font-mono text-blue-600 ml-2"></span>
                </h3>
                
                {{-- Form akan di-submit ke route yang sudah kita buat --}}
                <form :action="`/orders/${selectedOrder?.order_id}/update-status`" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Pilih Status Baru</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="pending" :selected="selectedOrder?.status === 'pending'">
                                Menunggu Pembayaran
                            </option>
                            <option value="paid" :selected="selectedOrder?.status === 'paid'">
                                Sudah Dibayar
                            </option>
                            <option value="cancelled" :selected="selectedOrder?.status === 'cancelled'">
                                Dibatalkan
                            </option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" 
                                @click="modalOpen = false" 
                                class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors">
                            Batal
                        </button>
                        <button type="submit" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors flex items-center">
                            <i class="fas fa-save mr-1"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>