{{-- resources/views/shop/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg md:text-xl text-gray-800 leading-tight">
            <i class="fas fa-store mr-2"></i>Koperasi
        </h2>
    </x-slot>

    <div class="py-6 md:py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Search and Filter Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 md:mb-6">
                <div class="p-4 md:p-6">
                    <form action="{{ route('shop.index') }}" method="GET" class="flex flex-col gap-3 md:flex-row md:gap-4" id="filterForm">
                        <div class="flex-1">
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Cari produk..." 
                                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="md:w-40">
                            <select name="category" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->category_id }}" {{ request('category') == $category->category_id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:w-40">
                            <select name="sort" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                                <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Harga Terendah</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>

                    <!-- Active Filters Display -->
                    @if(request('search') || request('category') || request('sort', 'newest') != 'newest')
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm text-gray-600 font-medium">Filter aktif:</span>
                            
                            @if(request('search'))
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                    <i class="fas fa-search text-xs"></i>
                                    "{{ request('search') }}"
                                    <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="ml-1 hover:text-blue-600">
                                        <i class="fas fa-times text-xs"></i>
                                    </a>
                                </span>
                            @endif

                            @if(request('category'))
                                @php
                                    $selectedCategory = $categories->where('category_id', request('category'))->first();
                                @endphp
                                @if($selectedCategory)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                        <i class="fas fa-tag text-xs"></i>
                                        {{ $selectedCategory->name }}
                                        <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}" class="ml-1 hover:text-green-600">
                                            <i class="fas fa-times text-xs"></i>
                                        </a>
                                    </span>
                                @endif
                            @endif

                            @if(request('sort', 'newest') != 'newest')
                                @php
                                    $sortLabels = [
                                        'price_asc' => 'Harga Terendah',
                                        'price_desc' => 'Harga Tertinggi', 
                                        'name_asc' => 'Nama A-Z'
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-medium">
                                    <i class="fas fa-sort text-xs"></i>
                                    {{ $sortLabels[request('sort')] ?? request('sort') }}
                                    <a href="{{ request()->fullUrlWithQuery(['sort' => null]) }}" class="ml-1 hover:text-purple-600">
                                        <i class="fas fa-times text-xs"></i>
                                    </a>
                                </span>
                            @endif

                            <!-- Clear All Filters -->
                            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-800 hover:bg-red-200 rounded-full text-xs font-medium transition-colors">
                                <i class="fas fa-times-circle text-xs"></i>
                                Hapus Semua
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Banner -->
            <div class="w-full max-w-[1200px] mx-auto">
                <img src="{{ asset('storage/images/Benner KoperasiHD.png') }}" alt="Banner Koperasi SMKIUTAMA" class="w-full max-h-[298px] object-cover rounded-lg shadow-md" />
            </div>
            <br>


            @if($products->count() > 0)
                <!-- Products Grid - Responsive with Equal Heights -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 md:gap-6">
                    @foreach($products as $product)
                        <div class="product-card bg-white rounded-lg shadow-md overflow-hidden transform hover:-translate-y-1 transition-all duration-300 hover:shadow-lg">
                            <!-- Product Image -->
                            <div class="relative aspect-square overflow-hidden">
                                <a href="{{ route('shop.products.show', $product->product_id) }}">
                                    <img src="{{ $product->image_url ? asset($product->image_url) : 'https://via.placeholder.com/200x200?text=No+Image' }}" 
                                         alt="{{ $product->name }}" 
                                         class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                </a>
                                @if($product->category)
                                <span class="absolute top-1 left-1 md:top-2 md:left-2 bg-blue-600 text-white text-xs font-semibold px-1.5 py-0.5 md:px-2 md:py-1 rounded-full">
                                    <span class="hidden sm:inline">{{ $product->category->name }}</span>
                                    <span class="sm:hidden">{{ substr($product->category->name, 0, 3) }}</span>
                                </span>
                                @endif
                                @if($product->stock <= 0)
                                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                                    <span class="text-white text-xs font-bold">HABIS</span>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Product Info - Flex Container for Equal Heights -->
                            <div class="product-info p-2 sm:p-3 md:p-4">
                                <!-- Product Content -->
                                <div class="product-content">
                                    <!-- Product Name -->
                                    <h3 class="font-semibold text-xs sm:text-sm md:text-base text-gray-800 mb-1 sm:mb-2 line-clamp-2 leading-tight min-h-[2.5rem] sm:min-h-[3rem]">
                                        {{ $product->name }}
                                    </h3>
                                    
                                    <!-- Rating - Responsive visibility -->
                                    <div class="hidden sm:flex items-center mb-1 sm:mb-2 min-h-[1.25rem]">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= round($product->avg_rating))
                                                <i class="fas fa-star text-yellow-400 text-xs"></i>
                                            @else
                                                <i class="far fa-star text-gray-300 text-xs"></i>
                                            @endif
                                        @endfor
                                        <span class="text-xs text-gray-500 ml-1">({{ $product->reviews_count }})</span>
                                    </div>
                                    
                                    <!-- Price -->
                                    <div class="mb-2 sm:mb-3">
                                        <span class="text-sm sm:text-base md:text-lg font-bold text-blue-600 block">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </span>
                                        @if($product->stock <= 0)
                                            <div class="text-xs text-red-500 font-medium mt-0.5">Stok Habis</div>
                                        @elseif($product->stock <= 5)
                                            <div class="text-xs text-orange-500 font-medium mt-0.5">Stok: {{ $product->stock }}</div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Action Button - Always at bottom -->
                                <div class="product-actions">
                                    <a href="{{ route('shop.products.show', $product->product_id) }}" 
                                       class="w-full block bg-gray-800 text-white py-1.5 sm:py-2 px-2 sm:px-3 rounded-lg text-center text-xs sm:text-sm font-semibold hover:bg-gray-900 transition-colors">
                                        <span class="hidden sm:inline">Lihat Detail</span>
                                        <span class="sm:hidden">Detail</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-6 md:mt-8">
                    {{ $products->withQueryString()->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-lg shadow-sm p-8 md:p-12 text-center">
                    <i class="fas fa-box-open text-4xl md:text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg md:text-xl font-semibold text-gray-800 mb-2">Produk Tidak Ditemukan</h3>
                    <p class="text-sm md:text-base text-gray-600 mb-4">
                        @if(request('search') || request('category') || request('sort'))
                            Tidak ada produk yang sesuai dengan kriteria pencarian Anda.
                        @else
                            Belum ada produk yang tersedia saat ini.
                        @endif
                    </p>
                    <div class="space-y-2">
                        @if(request('search') || request('category') || request('sort'))
                            <a href="{{ route('shop.index') }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium mr-2">
                                Lihat Semua Produk
                            </a>
                            <button onclick="history.back()" class="inline-block px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm font-medium">
                                Kembali
                            </button>
                        @else
                            <a href="{{ route('shop.index') }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                Refresh Halaman
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        /* Line clamp utility */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Equal height product cards */
        .product-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-info {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .product-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-actions {
            margin-top: auto;
        }

        /* Better responsive spacing */
        @media (max-width: 640px) {
            .grid-cols-2 > * {
                min-width: 0;
            }
            
            /* Compact spacing for mobile */
            .gap-3 {
                gap: 0.5rem;
            }
        }

        /* Extra small screens optimization */
        @media (max-width: 480px) {
            .product-card .p-2 {
                padding: 0.375rem;
            }
            
            .product-card h3 {
                font-size: 0.6875rem;
                line-height: 0.875rem;
                min-height: 1.75rem;
            }
            
            .product-card .text-sm {
                font-size: 0.75rem;
            }
            
            .product-card .text-xs {
                font-size: 0.625rem;
            }
        }

        /* Very small screens (< 360px) */
        @media (max-width: 359px) {
            .product-card h3 {
                font-size: 0.625rem;
                line-height: 0.75rem;
                min-height: 1.5rem;
            }
            
            .product-card .text-sm {
                font-size: 0.6875rem;
            }
            
            .product-actions a {
                padding: 0.25rem 0.5rem;
                font-size: 0.625rem;
            }
        }

        /* Enhanced hover effects */
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        @media (hover: none) {
            .product-card:hover {
                transform: none;
            }
        }

        /* Image loading optimization */
        .product-card img {
            transition: opacity 0.3s ease-in-out;
        }

        .product-card img[src*="placeholder"] {
            background: linear-gradient(45deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        }

        /* Button improvements for touch devices */
        @media (pointer: coarse) {
            .product-actions a {
                min-height: 2.25rem;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* Ensure consistent card heights in each row */
        @supports (display: grid) {
            .grid > .product-card {
                align-self: stretch;
            }
        }

        /* Fix for very long product names */
        .product-card h3 {
            word-break: break-word;
            hyphens: auto;
        }

        /* Price alignment */
        .product-card .text-blue-600 {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Responsive text scaling */
        @media (min-width: 1024px) {
            .product-card h3 {
                min-height: 3.5rem;
            }
        }

        /* Filter badges styling */
        .inline-flex .fa-times:hover {
            transform: scale(1.1);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Progressive image loading
            const images = document.querySelectorAll('.product-card img');
            
            images.forEach(img => {
                if (img.complete) {
                    img.style.opacity = '1';
                } else {
                    img.style.opacity = '0';
                    img.addEventListener('load', function() {
                        this.style.opacity = '1';
                    });
                }
            });

            // Touch device optimization
            if ('ontouchstart' in window) {
                document.body.classList.add('touch-device');
                
                // Disable hover effects on touch devices
                const style = document.createElement('style');
                style.textContent = `
                    @media (hover: none) {
                        .hover\\:scale-105:hover {
                            transform: scale(1);
                        }
                        .hover\\:-translate-y-1:hover {
                            transform: translateY(0);
                        }
                    }
                `;
                document.head.appendChild(style);
            }

            // Enhanced form handling
            const filterForm = document.getElementById('filterForm');
            const searchInput = filterForm.querySelector('input[name="search"]');
            
            // Auto-submit on Enter for search
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterForm.submit();
                }
            });

            // Clear individual filters with animation
            const filterBadges = document.querySelectorAll('.inline-flex .fa-times');
            filterBadges.forEach(badge => {
                badge.parentElement.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.style.transform = 'scale(0.8)';
                    this.style.opacity = '0.5';
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 150);
                });
            });

            // Responsive grid adjustment
            function adjustGrid() {
                const container = document.querySelector('.grid');
                if (!container) return;
                
                const containerWidth = container.offsetWidth;
                const cardMinWidth = 150;
                const gap = 12;
                
                if (containerWidth < 640) {
                    const cols = Math.floor(containerWidth / (cardMinWidth + gap));
                    container.style.gridTemplateColumns = `repeat(${Math.max(2, cols)}, minmax(0, 1fr))`;
                }
            }

            // Initial adjustment and resize listener
            adjustGrid();
            window.addEventListener('resize', debounce(adjustGrid, 250));

            // Loading state for form submissions
            const selectElements = filterForm.querySelectorAll('select');
            selectElements.forEach(select => {
                select.addEventListener('change', function() {
                    this.style.opacity = '0.6';
                    this.style.pointerEvents = 'none';
                    
                    // Add loading indicator
                    const loadingSpinner = document.createElement('div');
                    loadingSpinner.innerHTML = '<i class="fas fa-spinner fa-spin text-blue-600"></i>';
                    loadingSpinner.className = 'absolute inset-0 flex items-center justify-center bg-white bg-opacity-75';
                    
                    const container = this.parentElement;
                    container.style.position = 'relative';
                    container.appendChild(loadingSpinner);
                });
            });

            // Debounce function
            function debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }

            // URL state management for better UX
            if (window.history && window.history.replaceState) {
                // Clean up URL parameters that are empty
                const url = new URL(window.location);
                let hasChanges = false;
                
                ['search', 'category', 'sort'].forEach(param => {
                    if (url.searchParams.get(param) === '' || 
                        (param === 'sort' && url.searchParams.get(param) === 'newest')) {
                        url.searchParams.delete(param);
                        hasChanges = true;
                    }
                });
                
                if (hasChanges) {
                    window.history.replaceState({}, '', url.toString());
                }
            }

            // Smooth scroll to products after filter change
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search') || urlParams.has('category') || urlParams.has('sort')) {
                const productsGrid = document.querySelector('.grid');
                if (productsGrid) {
                    setTimeout(() => {
                        productsGrid.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'start',
                            inline: 'nearest' 
                        });
                    }, 100);
                }
            }

            // Add visual feedback for active filters
            const activeFilters = document.querySelectorAll('[name="category"], [name="sort"]');
            activeFilters.forEach(filter => {
                if (filter.value && filter.value !== '' && 
                    !(filter.name === 'sort' && filter.value === 'newest')) {
                    filter.style.borderColor = '#3B82F6';
                    filter.style.borderWidth = '2px';
                }
            });

            // Search input enhancement
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const currentValue = this.value.trim();
                
                // Add visual feedback
                if (currentValue.length > 0) {
                    this.style.borderColor = '#3B82F6';
                    this.style.borderWidth = '2px';
                } else {
                    this.style.borderColor = '#D1D5DB';
                    this.style.borderWidth = '1px';
                }

                // Optional: Auto-search after user stops typing (uncomment if desired)
                // searchTimeout = setTimeout(() => {
                //     if (currentValue.length >= 2 || currentValue.length === 0) {
                //         filterForm.submit();
                //     }
                // }, 1000);
            });
        });
    </script>
</x-app-layout>