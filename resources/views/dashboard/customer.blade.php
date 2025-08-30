<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Beranda') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Welcome Section --}}
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg shadow-lg mb-6 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold">Selamat Datang, {{ auth()->user()->name }}!</h3>
                        <p class="mt-1 opacity-90">Temukan produk terbaik untuk kebutuhan Anda</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm opacity-75">Keranjang</div>
                        <div class="text-2xl font-bold">{{ $data['cart_count'] }} item</div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <a href="{{ route('shop.index') }}" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="text-center">
                        <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Belanja</div>
                    </div>
                </a>
                
                <a href="{{ route('carts.index') }}" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="text-center">
                        <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01"></path>
                            </svg>
                        </div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Keranjang</div>
                    </div>
                </a>
                
                <a href="{{ route('orders.create') }}" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="text-center">
                        <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-full w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Buat Pesanan</div>
                    </div>
                </a>
                
                <a href="{{ route('profile.edit') }}" class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow hover:shadow-md transition-shadow">
                    <div class="text-center">
                        <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full w-12 h-12 mx-auto mb-2 flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Profil</div>
                    </div>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Produk Terbaru --}}
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Produk Terbaru</h4>
                            <a href="{{ route('shop.index') }}" class="text-blue-600 dark:text-blue-400 text-sm hover:underline">Lihat Semua</a>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @forelse ($data['products'] as $product)
                                <div class="group">
                                    <a href="{{ route('shop.product', $product->product_id) }}">
                                        <img src="{{ $product->image_url ?? 'https://via.placeholder.com/200' }}" 
                                             alt="{{ $product->name }}" 
                                             class="w-full h-32 object-cover rounded-lg mb-2 group-hover:opacity-75 transition-opacity">
                                    </a>
                                    <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                        <a href="{{ route('shop.product', $product->product_id) }}" class="hover:text-blue-600">
                                            {{ $product->name }}
                                        </a>
                                    </h5>
                                    <p class="text-sm font-bold text-blue-600 dark:text-blue-400">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </p>
                                </div>
                            @empty
                                <div class="col-span-full text-center py-8 text-gray-500">
                                    Belum ada produk tersedia
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Pesanan Terbaru --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pesanan Terbaru</h4>
                        @forelse ($data['user_orders'] as $order)
                            <div class="border-b dark:border-gray-700 pb-3 mb-3 last:border-b-0 last:mb-0 last:pb-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            #{{ $order->order_id }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $order->created_at->format('d M Y') }}
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($order->status == 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                        @elseif($order->status == 'processing') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @elseif($order->status == 'shipped') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                        @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                        @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center">Belum ada pesanan</p>
                        @endforelse
                    </div>

                    {{-- Kategori Populer --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Kategori Populer</h4>
                        <div class="space-y-2">
                            @forelse ($data['categories'] as $category)
                                <a href="{{ route('shop.category', $category->category_id) }}" 
                                   class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ $category->name }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $category->products_count }} produk
                                    </span>
                                </a>
                            @empty
                                <p class="text-sm text-gray-500 text-center">Belum ada kategori</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>