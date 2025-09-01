<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-box-open mr-2"></i>{{ __('Kelola Produk') }}
            </h2>
            <a href="{{ route('products.create') }}" class="btn-primary">
                <i class="fas fa-plus mr-2"></i>
                Tambah Produk
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Bagian Filter dan Statistik tidak diubah, masih sama seperti perbaikan sebelumnya --}}
            <div class="glass-effect rounded-2xl p-4 sm:p-6 mb-4 sm:mb-8">
                <form action="{{ route('products.index') }}" method="GET" id="filterForm">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-center">
                        <div class="lg:col-span-2">
                            <input type="text" name="search" placeholder="Cari produk..." class="input-field" value="{{ request('search') }}">
                        </div>
                        <div>
                            <select name="category_id" class="input-field" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->category_id }}" {{ request('category_id') == $category->category_id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <select name="status" class="input-field" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Stok Menipis</option>
                                <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Stok Habis</option>
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="w-full btn-primary justify-center">
                                <i class="fas fa-search mr-2"></i>Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4 sm:mb-8">
                <a href="{{ route('products.index') }}" class="glass-effect rounded-xl p-4 text-center hover:bg-blue-50 transition-colors {{ !request()->has('status') ? 'ring-2 ring-blue-500' : '' }}">
                    <div class="text-2xl sm:text-3xl font-bold text-blue-600 mb-1">{{ $totalProducts ?? 0 }}</div>
                    <div class="text-gray-600 text-sm">Total Produk</div>
                </a>
                <a href="{{ route('products.index', ['status' => 'low_stock']) }}" class="glass-effect rounded-xl p-4 text-center hover:bg-orange-50 transition-colors {{ request('status') == 'low_stock' ? 'ring-2 ring-orange-500' : '' }}">
                    <div class="text-2xl sm:text-3xl font-bold text-orange-600 mb-1">{{ $lowStockProducts ?? 0 }}</div>
                    <div class="text-gray-600 text-sm">Stok Menipis</div>
                </a>
                <a href="{{ route('products.index', ['status' => 'out_of_stock']) }}" class="glass-effect rounded-xl p-4 text-center hover:bg-red-50 transition-colors {{ request('status') == 'out_of_stock' ? 'ring-2 ring-red-500' : '' }}">
                    <div class="text-2xl sm:text-3xl font-bold text-red-600 mb-1">{{ $outOfStockProducts ?? 0 }}</div>
                    <div class="text-gray-600 text-sm">Stok Habis</div>
                </a>
            </div>

            <div class="glass-effect rounded-2xl p-4 sm:p-8">
                <div class="mb-4 sm:mb-6">
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1 sm:mb-2">
                        @if(request('status'))
                            @switch(request('status'))
                                @case('low_stock') Produk Stok Menipis @break
                                @case('out_of_stock') Produk Stok Habis @break
                                @default Daftar Produk Anda
                            @endswitch
                        @else
                            Daftar Produk Anda
                        @endif
                    </h3>
                    <p class="text-gray-600 text-sm sm:text-base">
                        @if(request('status'))
                            Filter: {{ ucwords(str_replace('_', ' ', request('status'))) }}
                        @else
                            Kelola semua produk dalam toko Anda.
                        @endif
                    </p>
                </div>

                @if(session('paid'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">{{ session('paid') }}</div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">{{ session('error') }}</div>
                @endif

                @if(isset($products) && $products->count() > 0)
                    {{-- PERUBAHAN 1: Grid dikembalikan ke 2 kolom untuk mobile, lalu meningkat di layar besar --}}
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6">
                        @foreach($products as $product)
                            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transform transition-all duration-300 hover:scale-105 flex flex-col {{ $product->stock == 0 ? 'opacity-75' : '' }}">
                                <div class="relative aspect-square bg-gray-200">
                                    <img src="{{ $product->image_url ? asset('storage/' . $product->image) : asset('images/placeholder.jpg') }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-full object-contain {{ $product->stock == 0 ? 'grayscale' : '' }}">
                                    
                                    <div class="absolute top-2 left-2">
                                        @if($product->category)
                                            <span class="block text-xs font-semibold px-2 py-1 rounded-full text-white bg-blue-500 shadow-md">
                                                {{ $product->category->name }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="absolute top-2 right-2">
                                        @if($product->stock == 0)
                                            <span class="block text-xs font-semibold px-2 py-1 rounded-full text-white bg-red-600">Habis</span>
                                        @elseif($product->stock < 10)
                                            <span class="block text-xs font-semibold px-2 py-1 rounded-full text-white bg-orange-500">Menipis</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- PERUBAHAN 2: Padding kartu dikecilkan untuk mobile (p-2), dan kembali normal di layar besar (sm:p-4) --}}
                                <div class="p-2 sm:p-4 flex flex-col flex-grow">
                                    <div class="flex-grow">
                                        {{-- PERUBAHAN 3: Ukuran font judul dikecilkan untuk mobile (text-sm), normal di layar besar (sm:text-lg) --}}
                                        <h4 class="font-semibold text-gray-900 text-sm sm:text-lg mb-1 sm:mb-2">{{ $product->name }}</h4>
                                        
                                        {{-- PERUBAHAN 4: Deskripsi disembunyikan di mobile (hidden) agar tidak makan tempat, tampil di layar besar (sm:block) --}}
                                        <p class="text-gray-600 text-xs sm:text-sm mb-3 hidden sm:block">{{ Str::limit($product->description ?? 'Tidak ada deskripsi', 50) }}</p>
                                        
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-2 sm:mb-4">
                                            {{-- PERUBAHAN 5: Ukuran font harga dikecilkan untuk mobile (text-base), normal di layar besar (sm:text-2xl) --}}
                                            <span class="text-base sm:text-2xl font-bold text-blue-600">
                                                Rp {{ number_format($product->price, 0, ',', '.') }}
                                            </span>
                                            <div class="text-left sm:text-right">
                                                <span @class([
                                                    'text-xs sm:text-sm font-medium',
                                                    'text-red-600' => $product->stock == 0,
                                                    'text-orange-600' => $product->stock > 0 && $product->stock < 10,
                                                    'text-green-600' => $product->stock >= 10,
                                                ])>
                                                    Stok: {{ $product->stock ?? 0 }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-t pt-2 sm:pt-4 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                                        <a href="{{ route('products.edit', $product->product_id) }}" class="flex-1 btn-secondary justify-center text-xs sm:text-sm">
                                            <i class="fas fa-pencil-alt mr-1 sm:mr-2"></i>Edit
                                        </a>
                                        <form action="{{ route('products.destroy', $product->product_id) }}" method="POST" class="flex-1" onsubmit="return confirm('Yakin hapus produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-full btn-danger justify-center text-xs sm:text-sm">
                                                <i class="fas fa-trash-alt mr-1 sm:mr-2"></i>Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(method_exists($products, 'links'))
                        <div class="mt-8">{{ $products->appends(request()->query())->links() }}</div>
                    @endif
                @else
                    <div class="text-center py-8 sm:py-16">
                        <i class="fas fa-box-open text-gray-300 text-5xl sm:text-6xl mb-4"></i>
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">
                            @if(request('status')) Tidak ada produk dengan status tersebut @else Belum Ada Produk @endif
                        </h3>
                        <p class="text-gray-600 text-sm sm:text-base mb-4 sm:mb-6">
                            @if(request('status')) Coba ubah filter atau tambahkan produk baru. @else Mulai tambahkan produk pertama Anda untuk memulai berjualan. @endif
                        </p>
                        @if(request('status'))
                            <div class="space-x-2 sm:space-x-4">
                                <a href="{{ route('products.index') }}" class="btn-secondary inline-flex items-center"><i class="fas fa-eye mr-2"></i>Lihat Semua Produk</a>
                                <a href="{{ route('products.create') }}" class="btn-primary inline-flex items-center"><i class="fas fa-plus mr-2"></i>Tambah Produk</a>
                            </div>
                        @else
                            <a href="{{ route('products.create') }}" class="btn-primary inline-flex items-center"><i class="fas fa-plus mr-2"></i>Tambah Produk Pertama</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Blok <style> dan <script> tidak diubah --}}
    <style>
        .glass-effect { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .btn-primary, .btn-secondary, .btn-danger { border: none; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.3s ease; font-weight: 600; font-size: 0.875rem; text-align: center; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .btn-secondary { background: #64748b; }
        .btn-danger { background: #ef4444; }
        .btn-primary:hover, .btn-secondary:hover, .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); color: white; text-decoration: none; }
        .btn-primary:disabled, .btn-secondary:disabled, .btn-danger:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }
        .input-field { width: 100%; padding: 0.65rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; background: rgba(255, 255, 255, 0.9); transition: all 0.3s ease; font-size: 0.875rem; }
        .input-field:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .glass-effect:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); }
        .ring-2 { box-shadow: 0 0 0 2px currentColor; }
        .ring-blue-500 { color: #3b82f6; }
        .ring-orange-500 { color: #f59e0b; }
        .ring-red-500 { color: #ef4444; }
        @media (max-width: 639px) { .btn-primary, .btn-secondary, .btn-danger { font-size: 0.75rem; padding: 0.45rem 0.8rem; } }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.querySelector('select[name="status"]');
            if (statusSelect) { statusSelect.addEventListener('change', function() { document.getElementById('filterForm').submit(); }); }
        });
    </script>
</x-app-layout>