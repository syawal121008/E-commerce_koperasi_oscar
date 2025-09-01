<x-app-layout>
    {{-- Page Header --}}
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                <i class="fas fa-receipt mr-3 text-indigo-600"></i>
                {{ __('Daftar Pesanan') }}
            </h2>
            <div class="mt-3 md:mt-0 text-sm text-gray-500">
                Total: {{ $orders->total() }} pesanan
            </div>
        </div>
    </x-slot>

    <div x-data="{ modalOpen: false, selectedOrder: null }">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                {{-- Success & Error Alerts --}}
                @if(session('paid'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-300 text-green-800 rounded-lg flex items-center" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('paid') }}</span>
                    </div>
                @endif

                {{-- Main Content Card --}}
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:p-8 bg-white border-b border-gray-200">

                        {{-- Search and Filter Form --}}
                        <form action="{{ route('orders.index') }}" method="GET" class="mb-8">
                            @if(Auth::user()->role !== 'customer')
                                {{-- Admin Layout: Search + Status + Buttons --}}
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                    {{-- Search Input --}}
                                    <div class="sm:col-span-2 lg:col-span-2">
                                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Pelanggan</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </div>
                                            <input type="text" name="search" id="search" class="block w-full pl-10 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" value="{{ request('search') }}" placeholder="Cari berdasarkan nama pelanggan...">
                                        </div>
                                    </div>

                                    {{-- Status Filter --}}
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter Status</label>
                                        <select name="status" id="status" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Semua Status</option>
                                            <option value="pending" @selected(request('status') == 'pending')>Belum Dibayar</option>
                                            <option value="paid" @selected(request('status') == 'paid')>Dibayar</option>
                                            <option value="completed" @selected(request('status') == 'completed')>Selesai</option>
                                            <option value="cancelled" @selected(request('status') == 'cancelled')>Dibatalkan</option>
                                        </select>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="flex items-end space-x-3">
                                        <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Filter
                                        </button>
                                        <a href="{{ route('orders.index') }}" class="w-full justify-center inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            @else
                                {{-- Customer Layout: Status Filter on the left --}}
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                    {{-- Status Filter --}}
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Filter Status</label>
                                        <select name="status" id="status" onchange="this.form.submit()" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Semua Status</option>
                                            <option value="pending" @selected(request('status') == 'pending')>Belum Dibayar</option>
                                            <option value="paid" @selected(request('status') == 'paid')>Dibayar</option>
                                            <option value="completed" @selected(request('status') == 'completed')>Selesai</option>
                                            <option value="cancelled" @selected(request('status') == 'cancelled')>Dibatalkan</option>
                                        </select>
                                    </div>

                                    {{-- Empty space --}}
                                    <div></div>

                                    {{-- Action Buttons --}}
                                    <div class="flex items-end space-x-3">
                                        <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Filter
                                        </button>
                                        <a href="{{ route('orders.index') }}" class="w-full justify-center inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150">
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </form>

                        {{-- Orders Table / List --}}
                        <div class="overflow-x-auto">
                            <div class="min-w-full">
                                {{-- Desktop Table Head --}}
                                <div class="hidden md:block">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    @if(Auth::user()->role == 'customer')
                                                        Admin
                                                    @else
                                                        Pelanggan
                                                    @endif
                                                </th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Dibuat</th>
                                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @forelse($orders as $order)
                                            <tr class="hover:bg-gray-50 transition-colors duration-150">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-700">#{{ $order->order_id }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    @if(Auth::user()->role == 'customer')
                                                        {{ $order->admin->full_name ?? 'N/A' }}
                                                    @else
                                                        {{ $order->customer->full_name ?? 'N/A' }}
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span @class([
                                                        'px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                        'bg-yellow-100 text-yellow-800' => $order->status === 'pending',
                                                        'bg-green-100 text-green-800' => $order->status === 'paid' || $order->status === 'completed',
                                                        'bg-red-100 text-red-800' => $order->status === 'cancelled',
                                                    ])>
                                                        @switch($order->status)
                                                            @case('pending') Belum Dibayar @break
                                                            @case('completed') Selesai @break
                                                            @case('paid') Dibayar @break
                                                            @case('cancelled') Dibatalkan @break
                                                            @default {{ ucfirst($order->status) }}
                                                        @endswitch
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                    <div class="flex items-center justify-center space-x-4">
                                                        <a href="{{ route('orders.show', $order->order_id) }}" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                            </svg>
                                                        </a>
                                                        @if(Auth::user()->role !== 'customer' && !in_array($order->status, ['completed', 'cancelled']))
                                                            <button @click="modalOpen = true; selectedOrder = {{ $order->toJson() }}" class="text-green-600 hover:text-green-900" title="Ubah Status">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                                    <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                                    <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                                                </svg>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-6 py-24 text-center text-gray-500">
                                                        <div class="flex flex-col items-center">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                            </svg>
                                                            <p class="mt-2 font-medium">Tidak ada pesanan yang ditemukan.</p>
                                                            @if(request()->has('search') || request()->has('status'))
                                                                <p class="text-sm text-gray-400 mt-1">Coba ubah filter atau kata kunci pencarian Anda.</p>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Mobile Card View --}}
                                <div class="md:hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @forelse($orders as $order)
                                    <div class="bg-white p-4 rounded-lg shadow-md border border-gray-200">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <p class="text-lg font-bold text-indigo-700">#{{ $order->order_id }}</p>
                                                <p class="text-sm text-gray-600">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                            </div>
                                            <span @class([
                                                'px-2.5 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full',
                                                'bg-yellow-100 text-yellow-800' => $order->status === 'pending',
                                                'bg-green-100 text-green-800' => $order->status === 'paid' || $order->status === 'completed',
                                                'bg-red-100 text-red-800' => $order->status === 'cancelled',
                                            ])>
                                                @switch($order->status)
                                                    @case('pending') Belum Dibayar @break
                                                    @case('completed') Selesai @break
                                                    @case('paid') Dibayar @break
                                                    @case('cancelled') Dibatalkan @break
                                                    @default {{ ucfirst($order->status) }}
                                                @endswitch
                                            </span>
                                        </div>
                                        
                                        <div class="border-t border-gray-200 pt-4">
                                            <div class="flex justify-between text-sm mb-2">
                                                <span class="text-gray-500 font-medium">
                                                    @if(Auth::user()->role == 'customer')
                                                        Admin:
                                                    @else
                                                        Pelanggan:
                                                    @endif
                                                </span>
                                                <span class="text-gray-800 font-semibold">
                                                    @if(Auth::user()->role == 'customer')
                                                        {{ $order->admin->full_name ?? 'N/A' }}
                                                    @else
                                                        {{ $order->customer->full_name ?? 'N/A' }}
                                                    @endif
                                                </span>
                                            </div>
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-500 font-medium">Total Harga:</span>
                                                <span class="text-gray-800 font-bold">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                            </div>
                                        </div>

                                        <div class="mt-4 pt-4 border-t border-gray-200 flex justify-end items-center space-x-4">
                                            <a href="{{ route('orders.show', $order->order_id) }}" class="text-blue-600 hover:text-blue-900 flex items-center text-sm font-medium">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                                    <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                                </svg>
                                                Detail
                                            </a>
                                            @if(Auth::user()->role !== 'customer' && !in_array($order->status, ['completed', 'cancelled']))
                                                <button @click="modalOpen = true; selectedOrder = {{ $order->toJson() }}" class="text-green-600 hover:text-green-900 flex items-center text-sm font-medium">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                                        <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" />
                                                        <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" />
                                                    </svg>
                                                    Ubah Status
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    @empty
                                        <div class="col-span-1 sm:col-span-2 py-24 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                                </svg>
                                                <p class="mt-2 font-medium">Tidak ada pesanan yang ditemukan.</p>
                                                @if(request()->has('search') || request()->has('status'))
                                                    <p class="text-sm text-gray-400 mt-1">Coba ubah filter atau kata kunci pencarian Anda.</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $orders->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal for Status Update --}}
        <div x-show="modalOpen" 
             x-transition:enter="transition ease-out duration-300" 
             x-transition:enter-start="opacity-0 scale-95" 
             x-transition:enter-end="opacity-100 scale-100" 
             x-transition:leave="transition ease-in duration-200" 
             x-transition:leave-start="opacity-100 scale-100" 
             x-transition:leave-end="opacity-0 scale-95" 
             class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" 
             style="display: none;">
            
            <div @click.away="modalOpen = false" 
                 class="bg-white rounded-lg shadow-2xl w-full max-w-md transform transition-all">
                
                {{-- Modal Header --}}
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Ubah Status Pesanan</h3>
                            <p class="text-gray-600 text-sm mt-1">
                                Order ID: <span x-text="'#' + (selectedOrder?.order_id || '')" class="font-mono text-indigo-600"></span>
                            </p>
                        </div>
                        <button @click="modalOpen = false" 
                                class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                {{-- Modal Body --}}
                <form :action="`/orders/${selectedOrder?.order_id}/update-status`" method="POST" class="p-6">
                    @csrf
                    @method('PATCH')

                    {{-- Current Order Info --}}
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h4 class="font-medium text-gray-700 mb-3">Informasi Pesanan</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Pelanggan:</span>
                                <span class="font-medium" x-text="selectedOrder?.customer?.full_name || 'N/A'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Total Harga:</span>
                                <span class="font-semibold text-green-600" x-text="selectedOrder ? 'Rp ' + new Intl.NumberFormat('id-ID').format(selectedOrder.total_price) : 'N/A'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Status Saat Ini:</span>
                                <span class="font-medium" x-text="
                                    selectedOrder?.status === 'pending' ? 'Belum Dibayar' :
                                    selectedOrder?.status === 'paid' ? 'Dibayar' :
                                    selectedOrder?.status === 'completed' ? 'Selesai' :
                                    selectedOrder?.status === 'cancelled' ? 'Dibatalkan' : 'N/A'
                                "></span>
                            </div>
                        </div>
                    </div>

                    {{-- Status Selection --}}
                    <div class="mb-6">
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-3">
                            Pilih Status Baru
                        </label>
                        <select name="status" 
                                id="status" 
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="pending" :selected="selectedOrder?.status === 'pending'">Belum Dibayar</option>
                            <option value="paid" :selected="selectedOrder?.status === 'paid'">Dibayar</option>
                            <option value="completed" :selected="selectedOrder?.status === 'completed'">Selesai</option>
                            <option value="cancelled" :selected="selectedOrder?.status === 'cancelled'">Dibatalkan</option>
                        </select>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 space-y-3 space-y-reverse sm:space-y-0">
                        <button type="button" 
                                @click="modalOpen = false" 
                                class="w-full sm:w-auto justify-center inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150">
                            Batal
                        </button>
                        <button type="submit" 
                                class="w-full sm:w-auto justify-center inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>