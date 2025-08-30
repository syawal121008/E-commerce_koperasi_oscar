{{-- resources/views/shop/show.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
                {{ $product->name }}
            </h2>
            <a href="{{ route('shop.index') }}" class="text-blue-600 hover:text-blue-800 text-sm sm:text-base">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Produk
            </a>
        </div>
    </x-slot>

    {{-- Tambahkan CSRF Token Meta --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                        <!-- Product Images -->
                        <div class="space-y-4">
                            <div class="aspect-square overflow-hidden rounded-lg border">
                                <img id="main-image" 
                                     src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/500x500?text=No+Image' }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-full h-full object-cover">
                            </div>
                            
                            <div class="flex space-x-2 overflow-x-auto">
                                <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/100x100?text=No+Image' }}" 
                                     alt="{{ $product->name }}" 
                                     class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded cursor-pointer border-2 border-blue-500 flex-shrink-0"
                                     onclick="changeMainImage(this.src)">
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="space-y-4 sm:space-y-6">
                            <div>
                                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                                
                                @if($product->category)
                                    <p class="text-sm text-gray-500 mb-4">
                                        <i class="fas fa-tag mr-1"></i>{{ $product->category->name }}
                                    </p>
                                @endif

                                <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4 mb-4">
                                    <span class="text-2xl sm:text-3xl font-bold text-blue-600">
                                        Rp {{ number_format($product->price, 0, ',', '.') }}
                                    </span>
                                    
                                    @if($product->stock > 0)
                                        <span class="bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full inline-flex items-center w-fit">
                                            <i class="fas fa-check-circle mr-1"></i>Tersedia
                                        </span>
                                    @else
                                        <span class="bg-red-100 text-red-800 text-sm px-3 py-1 rounded-full inline-flex items-center w-fit">
                                            <i class="fas fa-times-circle mr-1"></i>Stok Habis
                                        </span>
                                    @endif
                                </div>

                                <p class="text-gray-600 mb-2 text-sm sm:text-base">
                                    <strong>Stok tersedia:</strong> {{ $product->stock }} unit
                                </p>
                            </div>

                            @if($product->description)
                                <div>
                                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-3">Deskripsi Produk</h3>
                                    <div class="prose prose-sm text-gray-600">
                                        {!! nl2br(e($product->description)) !!}
                                    </div>
                                </div>
                            @endif

                            <!-- Purchase Options -->
                            <div class="border-t pt-4 sm:pt-6">
                                <form id="purchase-form" class="space-y-4">
                                    <input type="hidden" name="product_id" value="{{ $product->product_id }}">
                                    <input type="hidden" name="admin_id" value="{{ $product->admin_id }}">
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Jumlah
                                        </label>
                                        <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                                            <div class="flex items-center border rounded-lg w-fit">
                                                <button type="button" 
                                                        onclick="decreaseQuantity()" 
                                                        class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-l-lg">
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                                <input type="number" 
                                                       id="quantity" 
                                                       name="quantity" 
                                                       value="1" 
                                                       min="1" 
                                                       max="{{ $product->stock }}"
                                                       class="w-16 sm:w-20 px-3 py-2 text-center border-0 focus:ring-0">
                                                <button type="button" 
                                                        onclick="increaseQuantity()" 
                                                        class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-r-lg">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                            <span class="text-xs sm:text-sm text-gray-500">
                                                Maksimal {{ $product->stock }} unit
                                            </span>
                                        </div>
                                    </div>

                                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                                        @if($product->stock > 0)
                                            <button type="button" 
                                                    onclick="addToCart()" 
                                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 sm:px-6 rounded-lg transition-colors text-sm sm:text-base">
                                                <i class="fas fa-cart-plus mr-2"></i>Tambah ke Keranjang
                                            </button>
                                            
                                            <button type="button" 
                                                    onclick="buyNow()" 
                                                    class="flex-1 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 sm:px-6 rounded-lg transition-colors text-sm sm:text-base">
                                                <i class="fas fa-shopping-bag mr-2"></i>Beli Sekarang
                                            </button>
                                        @else
                                            <button type="button" 
                                                    disabled
                                                    class="w-full bg-gray-400 text-white font-semibold py-3 px-4 sm:px-6 rounded-lg cursor-not-allowed text-sm sm:text-base">
                                                <i class="fas fa-times-circle mr-2"></i>Stok Habis
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>

                            <!-- Product Features -->
                            <div class="border-t pt-4 sm:pt-6">
                                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Keunggulan</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <i class="fas fa-shield-alt text-green-500"></i>
                                        <span>Garansi Resmi</span>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <i class="fas fa-truck text-blue-500"></i>
                                        <span>Gratis Ongkir</span>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <i class="fas fa-medal text-yellow-500"></i>
                                        <span>Kualitas Terjamin</span>
                                    </div>
                                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                                        <i class="fas fa-headset text-purple-500"></i>
                                        <span>Customer Support</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shopee-Style Reviews Section -->
            <div class="mt-6 sm:mt-8">
                {{-- Bagian Rating dan Review ala Shopee --}}
                <div class="border rounded-lg p-4 sm:p-6 bg-white mb-4 sm:mb-6 shadow-sm">
                    <h3 class="text-lg sm:text-xl font-bold text-gray-800 mb-4 sm:mb-6">Penilaian Produk</h3>
                    
                    {{-- Rating Overview --}}
                    <div class="flex flex-col sm:flex-row sm:items-start space-y-6 sm:space-y-0 sm:space-x-8 mb-6">
                        {{-- Overall Rating --}}
                        <div class="text-center sm:flex-shrink-0">
                            <div class="text-3xl sm:text-5xl font-bold text-red-500 mb-2">{{ number_format($avgRating ?? 0, 1) }}</div>
                            <div class="flex justify-center text-red-400 text-lg sm:text-xl mb-2">
                                @php 
                                    $rating = $avgRating ?? 0;
                                    $fullStars = floor($rating); 
                                    $halfStar = $rating - $fullStars >= 0.5; 
                                @endphp
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $fullStars)
                                        <i class="fas fa-star"></i>
                                    @elseif ($i == $fullStars + 1 && $halfStar)
                                        <i class="fas fa-star-half-alt"></i>
                                    @else
                                        <i class="far fa-star text-gray-300"></i>
                                    @endif
                                @endfor
                            </div>
                            <div class="text-gray-600 text-sm">{{ $reviewsCount ?? 0 }} Review</div>
                        </div>

                        {{-- Rating Distribution --}}
                        <div class="flex-1 space-y-2">
                            @foreach ([5,4,3,2,1] as $star)
                                @php
                                    $count = $ratingsCount[$star] ?? 0;
                                    $percent = ($reviewsCount > 0) ? ($count / $reviewsCount) * 100 : 0;
                                @endphp
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <div class="flex items-center space-x-1 w-12 sm:w-16">
                                        <span class="text-sm">{{ $star }}</span>
                                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                                    </div>
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 overflow-hidden">
                                        <div class="bg-red-500 h-2 transition-all duration-300" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-600 w-8 sm:w-12 text-right">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Filter Buttons - Responsive --}}
                    <div class="flex flex-wrap gap-2 mb-6">
                        <button class="filter-btn active px-3 py-2 border-2 border-red-500 bg-red-50 text-red-600 rounded-md text-xs sm:text-sm font-medium transition-all duration-200" 
                                onclick="filterReviews('all')" data-filter="all">
                            Semua
                        </button>
                        <button class="filter-btn px-3 py-2 border border-gray-300 text-gray-700 rounded-md text-xs sm:text-sm font-medium hover:border-red-500 hover:text-red-600 transition-all duration-200" 
                                onclick="filterReviews('comment')" data-filter="comment">
                            <span class="hidden sm:inline">Dengan Komentar</span>
                            <span class="sm:hidden">Komentar</span>
                            ({{ ($reviews ? $reviews->where('comment', '!=', null)->count() : 0) }})
                        </button>
                        <button class="filter-btn px-3 py-2 border border-gray-300 text-gray-700 rounded-md text-xs sm:text-sm font-medium hover:border-red-500 hover:text-red-600 transition-all duration-200" 
                                onclick="filterReviews('media')" data-filter="media">
                            <span class="hidden sm:inline">Dengan Media</span>
                            <span class="sm:hidden">Media</span>
                            ({{ ($reviews ? $reviews->where('media_path', '!=', null)->count() : 0) }})
                        </button>
                        @foreach ([5,4,3,2,1] as $star)
                            @if(($ratingsCount[$star] ?? 0) > 0)
                                <button class="filter-btn px-3 py-2 border border-gray-300 text-gray-700 rounded-md text-xs sm:text-sm font-medium hover:border-red-500 hover:text-red-600 transition-all duration-200" 
                                        onclick="filterReviews({{ $star }})" data-filter="{{ $star }}">
                                    {{ $star }} <i class="fas fa-star text-yellow-400 text-xs"></i> ({{ $ratingsCount[$star] }})
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Reviews List --}}
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="p-4 sm:p-6">
                        <h4 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Ulasan Pembeli</h4>
                        
                        @if($reviews && $reviews->count() > 0)
                            <div id="reviews-container" class="space-y-4">
                                @foreach($reviews as $review)
                                    <div class="review-item border-b border-gray-100 pb-4 last:border-b-0" 
                                         data-rating="{{ $review->rating }}" 
                                         data-comment="{{ $review->comment ? '1' : '0' }}" 
                                         data-media="{{ $review->media_path ? '1' : '0' }}">
                                        
                                        {{-- User Info and Rating --}}
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex items-center space-x-2 sm:space-x-3">
                                                {{-- User Avatar --}}
                                                <div class="flex-shrink-0">
                                                    <img src="{{ $review->user->profilePhotoUrl() ?? '' }}" 
                                                        alt="Profile Photo" 
                                                        class="w-8 h-8 sm:w-10 sm:h-10 object-cover rounded-full border-2 border-white shadow-sm"
                                                        onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($review->user->full_name ?? 'User') }}&background=e5e7eb&color=374151&size=40';">
                                                </div>
                                                
                                                {{-- User Details --}}
                                                <div>
                                                    <div class="font-medium text-gray-900 text-sm">{{ $review->user->full_name ?? 'Pengguna' }}</div>
                                                    <div class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Rating Stars --}}
                                        <div class="flex items-center space-x-2 mb-3">
                                            <div class="flex text-yellow-400">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if ($i <= $review->rating)
                                                        <i class="fas fa-star text-sm"></i>
                                                    @else
                                                        <i class="far fa-star text-sm text-gray-300"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>

                                        {{-- Review Comment --}}
                                        @if($review->comment)
                                            <div class="text-gray-700 text-sm leading-relaxed mb-3">
                                                {{ $review->comment }}
                                            </div>
                                        @endif

                                        {{-- Review Media - IMPROVED MEDIA DISPLAY --}}
                                        @if($review->media_path)
                                            <div class="mb-3">
                                                @if($review->isImage())
                                                    {{-- Image Display --}}
                                                    <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gray-100 rounded-lg overflow-hidden cursor-pointer hover:opacity-90 transition-opacity"
                                                         onclick="openMediaModal('{{ $review->media_url }}', 'image')">
                                                        <img src="{{ $review->media_url }}" 
                                                             alt="Review image" 
                                                             class="w-full h-full object-cover"
                                                             onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center text-gray-400\'><i class=\'fas fa-image text-xl\'></i></div>'">
                                                    </div>
                                                @elseif($review->isVideo())
                                                    {{-- Video Display --}}
                                                    <div class="relative w-20 h-20 sm:w-24 sm:h-24 bg-black rounded-lg overflow-hidden cursor-pointer group"
                                                         onclick="openMediaModal('{{ $review->media_url }}', 'video')">
                                                        <video class="w-full h-full object-cover" preload="metadata">
                                                            <source src="{{ $review->media_url }}" type="{{ $review->getMimeType() }}">
                                                        </video>
                                                        <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center group-hover:bg-opacity-50 transition-all">
                                                            <i class="fas fa-play text-white text-base sm:text-lg"></i>
                                                        </div>
                                                        <div class="absolute bottom-1 right-1 bg-black bg-opacity-70 text-white text-xs px-1 rounded">
                                                            <i class="fas fa-video"></i>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- Helpful Actions --}}
                                        <div class="flex items-center justify-between text-xs text-gray-500">
                                            <div class="flex items-center space-x-4">
                                                <button class="flex items-center space-x-1 hover:text-red-600 transition-colors">
                                                    <i class="far fa-thumbs-up"></i>
                                                    <span class="hidden sm:inline">Berguna</span>
                                                    <span>({{ $review->helpful_count ?? 0 }})</span>
                                                </button>
                                            </div>
                                            
                                            @if(isset($review->verified_purchase) && $review->verified_purchase)
                                                <div class="flex items-center text-green-600">
                                                    <i class="fas fa-check-circle mr-1"></i>
                                                    <span class="hidden sm:inline">Pembelian Terverifikasi</span>
                                                    <span class="sm:hidden">Verified</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                        @else
                            <div class="text-center py-8 sm:py-12">
                                <i class="fas fa-comments text-gray-300 text-3xl sm:text-4xl mb-4"></i>
                                <p class="text-gray-500 text-base sm:text-lg mb-2">Belum ada ulasan untuk produk ini</p>
                                <p class="text-gray-400 text-sm">Jadilah yang pertama memberikan ulasan!</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Write Review Section --}}
                @auth
                    @if(!$hasReviewed)
                    <div class="bg-white rounded-lg shadow-sm mt-4 sm:mt-6">
                        <div class="p-4 sm:p-6">
                            <h4 class="text-base sm:text-lg font-semibold text-gray-800 mb-4">Tulis Ulasan Anda</h4>
                            
                            @if ($errors->any())
                                <div class="bg-red-100 border border-red-400 text-red-700 px-3 sm:px-4 py-3 rounded mb-4 text-sm">
                                    <ul class="list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (session('paid'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-3 sm:px-4 py-3 rounded mb-4 text-sm">
                                    {{ session('paid') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="bg-red-100 border border-red-400 text-red-700 px-3 sm:px-4 py-3 rounded mb-4 text-sm">
                                    {{ session('error') }}
                                </div>
                            @endif
                            
                            <form action="{{ route('products.reviews.store', $product->product_id) }}" method="POST" enctype="multipart/form-data" class="space-y-4" id="review-form">
                                @csrf
                                
                                {{-- Rating Selection --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Berikan Rating</label>
                                    <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                                        <div class="rating-input flex" data-rating="0">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <i class="far fa-star text-xl sm:text-2xl text-gray-300 cursor-pointer hover:text-yellow-400 transition-colors rating-star" 
                                                   data-rating="{{ $i }}"></i>
                                            @endfor
                                        </div>
                                        <span class="text-xs sm:text-sm text-gray-500 rating-text">Pilih rating</span>
                                    </div>
                                    <input type="hidden" name="rating" id="rating-value" required>
                                </div>
                                
                                {{-- Comment --}}
                                <div>
                                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Ulasan</label>
                                    <textarea name="comment" id="comment" rows="4" required
                                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none text-sm"
                                              placeholder="Bagikan pengalaman Anda dengan produk ini..." value="{{ old('comment') }}"></textarea>
                                    <div class="text-xs text-gray-500 mt-1">Minimum 10 karakter</div>
                                </div>
                                
                                {{-- Media Upload - IMPROVED UPLOAD SECTION --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tambah Foto/Video (Opsional)</label>
                                    <div class="space-y-3">
                                        <div class="flex items-center">
                                            <label for="media" class="cursor-pointer bg-gray-100 hover:bg-gray-200 border-2 border-dashed border-gray-300 rounded-lg px-4 py-6 sm:px-6 sm:py-8 text-center transition-all duration-300 hover:scale-105 file-upload-area w-full sm:w-auto">
                                                <i class="fas fa-camera text-gray-400 text-2xl sm:text-3xl mb-2 sm:mb-3 block"></i>
                                                <div class="text-xs sm:text-sm text-gray-600 font-medium">Klik untuk upload</div>
                                                <div class="text-xs text-gray-500 mt-1 hidden sm:block">atau drag & drop file</div>
                                                <input type="file" name="media" id="media" 
                                                       accept="image/jpeg,image/jpg,image/png,image/gif,image/webp,image/bmp,video/mp4,video/webm,video/ogg,video/mov,video/avi,video/3gp" 
                                                       class="hidden">
                                            </label>
                                        </div>
                                        
                                        {{-- Media Preview Container --}}
                                        <div id="media-preview-container" class="hidden">
                                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 sm:p-4">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="text-sm font-medium text-gray-700">File terpilih:</span>
                                                    <button type="button" onclick="clearMediaUpload()" 
                                                            class="text-red-500 hover:text-red-700 text-sm">
                                                        <i class="fas fa-times"></i> Hapus
                                                    </button>
                                                </div>
                                                <div id="media-preview" class="flex items-center space-x-3">
                                                    <!-- Preview content will be inserted here -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-xs text-gray-500 mt-2">
                                        <div><strong>Format:</strong> JPG, PNG, GIF, WEBP, BMP (gambar) | MP4, WEBM, OGG, MOV, AVI, 3GP (video)</div>
                                        <div><strong>Ukuran maksimal:</strong> 10MB per file</div>
                                    </div>
                                </div>
                                
                                {{-- Submit Button --}}
                                <div class="pt-4">
                                    <button type="submit" 
                                            class="w-full sm:w-auto bg-red-500 hover:bg-red-600 text-white font-semibold py-3 px-6 sm:px-8 rounded-lg transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed text-sm sm:text-base"
                                            id="submit-review" disabled>
                                        <i class="fas fa-paper-plane mr-2"></i>Kirim Ulasan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="bg-blue-50 rounded-lg p-4 sm:p-6 mt-4 sm:mt-6 text-center border border-blue-200">
                        <i class="fas fa-check-circle text-blue-500 text-2xl sm:text-3xl mb-3"></i>
                        <p class="text-blue-700 font-medium mb-2">Anda sudah memberikan ulasan untuk produk ini</p>
                        <p class="text-blue-600 text-sm">Terima kasih atas feedback Anda!</p>
                        
                        @if($userReview)
                        <div class="mt-4 p-3 sm:p-4 bg-white rounded-lg border">
                            <div class="flex justify-center mb-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($i <= $userReview->rating)
                                        <i class="fas fa-star text-yellow-400"></i>
                                    @else
                                        <i class="far fa-star text-gray-300"></i>
                                    @endif
                                @endfor
                            </div>
                            <p class="text-gray-700 text-sm">{{ $userReview->comment }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                @else
                <div class="bg-gray-50 rounded-lg p-4 sm:p-6 mt-4 sm:mt-6 text-center">
                    <i class="fas fa-user-lock text-gray-400 text-2xl sm:text-3xl mb-3"></i>
                    <p class="text-gray-600 mb-3">Masuk untuk menulis ulasan</p>
                    <a href="{{ route('login') }}" class="bg-red-500 hover:bg-red-600 text-white px-6 py-2 rounded-lg transition-colors text-sm sm:text-base">
                        Masuk Sekarang
                    </a>
                </div>
                @endauth
            </div>

            <!-- Related Products - RESPONSIVE SECTION -->
            @if($relatedProducts && $relatedProducts->count() > 0)
                <div class="mt-8 sm:mt-12">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 sm:p-6">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4 sm:mb-6">Produk Terkait</h2>
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4 lg:gap-6">
                                @foreach($relatedProducts as $related)
                                    <div class="related-product-card bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-300 rounded-lg overflow-hidden shadow hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                                        <div class="aspect-square overflow-hidden">
                                            <img src="{{ $related->image ? asset('storage/' . $related->image) : 'https://via.placeholder.com/200x200?text=No+Image' }}" 
                                                 alt="{{ $related->name }}" 
                                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                                        </div>
                                        <div class="related-product-info p-2 sm:p-3 lg:p-4">
                                            <div class="related-product-content">
                                                <h3 class="font-semibold text-gray-900 mb-1 sm:mb-2 line-clamp-2 text-xs sm:text-sm lg:text-base leading-tight min-h-[2rem] sm:min-h-[2.5rem] lg:min-h-[3rem]">
                                                    {{ $related->name }}
                                                </h3>
                                                <p class="text-sm sm:text-base lg:text-lg font-bold text-blue-700 mb-2 sm:mb-3">
                                                    Rp {{ number_format($related->price, 0, ',', '.') }}
                                                </p>
                                                <div class="flex items-center mb-2 sm:mb-3 min-h-[1rem] sm:min-h-[1.25rem]">
                                                    @php
                                                        $rating = $related->avg_rating ?? 4;
                                                    @endphp
                                                    <div class="flex space-x-1 text-yellow-400">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            @if ($i <= $rating)
                                                                <i class="fas fa-star text-xs sm:text-sm"></i>
                                                            @else
                                                                <i class="far fa-star text-xs sm:text-sm"></i>
                                                            @endif
                                                        @endfor
                                                    </div>
                                                    <span class="text-xs sm:text-sm text-gray-600 ml-1 sm:ml-2">({{ $related->reviews_count ?? 0 }})</span>
                                                </div>
                                            </div>
                                            
                                            <div class="related-product-actions flex space-x-1 sm:space-x-2 mt-auto">
                                                <button onclick="addToCartFromRelated('{{ $related->product_id }}', '{{ $related->admin_id }}')" 
                                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-1.5 sm:py-2 px-2 sm:px-3 rounded text-xs sm:text-sm transition-colors font-medium">
                                                    <i class="fas fa-cart-plus"></i>
                                                    <span class="hidden sm:inline ml-1">Keranjang</span>
                                                </button>
                                                <a href="{{ route('shop.products.show', $related->product_id) }}" 
                                                   class="bg-gray-600 hover:bg-gray-700 text-white py-1.5 sm:py-2 px-2 sm:px-3 rounded text-xs sm:text-sm transition-colors flex items-center justify-center">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Media Modal --}}
    <div id="media-modal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center p-4">
        <div class="relative max-w-4xl max-h-full">
            <button onclick="closeMediaModal()" class="absolute top-2 sm:top-4 right-2 sm:right-4 text-white text-xl sm:text-2xl z-10 hover:text-gray-300">
                <i class="fas fa-times"></i>
            </button>
            <div id="media-content" class="max-w-full max-h-full"></div>
        </div>
    </div>

    {{-- Custom Styles --}}
    <style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .prose {
        max-width: none;
    }

    .rating-input .fa-star {
        font-size: 1.5rem !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        user-select: none !important;
    }

    @media (min-width: 640px) {
        .rating-input .fa-star {
            font-size: 2rem !important;
        }
    }

    .rating-input .fa-star.active {
        color: #fbbf24 !important;
    }

    .rating-input .fa-star:hover {
        transform: scale(1.1);
    }

    .filter-btn.active {
        border-color: #ef4444 !important;
        background-color: #fef2f2 !important;
        color: #dc2626 !important;
    }

    .review-item {
        transition: all 0.3s ease;
    }

    .review-item.hidden {
        display: none;
    }

    .processing {
        opacity: 0.6;
        pointer-events: none;
    }

    #media-preview {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .file-upload-area {
        transition: all 0.3s ease;
        min-height: 100px;
    }

    @media (min-width: 640px) {
        .file-upload-area {
            min-width: 200px;
            min-height: 120px;
        }
    }

    .file-upload-area:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .file-upload-area.dragover {
        border-color: #ef4444;
        background-color: #fef2f2;
    }

    .media-preview-item {
        max-width: 120px;
        max-height: 120px;
        border-radius: 8px;
        overflow: hidden;
    }

    @media (min-width: 640px) {
        .media-preview-item {
            max-width: 150px;
            max-height: 150px;
        }
    }

    .media-preview-item img, .media-preview-item video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Related Products Card Improvements */
    .related-product-card {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .related-product-info {
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .related-product-content {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .related-product-actions {
        margin-top: auto;
    }

    /* Mobile optimizations */
    @media (max-width: 640px) {
        .grid-cols-2 > * {
            min-width: 0;
        }
        
        .related-product-card .p-2 {
            padding: 0.375rem;
        }
        
        .related-product-card h3 {
            font-size: 0.75rem;
            line-height: 1rem;
            min-height: 2rem;
        }
        
        .related-product-card .text-sm {
            font-size: 0.75rem;
        }
        
        .related-product-card .text-xs {
            font-size: 0.6875rem;
        }
    }

    /* Extra small screens */
    @media (max-width: 480px) {
        .related-product-card h3 {
            font-size: 0.6875rem;
            line-height: 0.875rem;
            min-height: 1.75rem;
        }
        
        .related-product-actions button,
        .related-product-actions a {
            padding: 0.25rem 0.375rem;
            font-size: 0.625rem;
        }
        
        .file-upload-area {
            min-height: 80px;
            padding: 1rem;
        }
        
        .file-upload-area i {
            font-size: 1.5rem !important;
        }
    }

    /* Enhanced hover effects */
    .related-product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    @media (hover: none) {
        .related-product-card:hover {
            transform: none;
        }
    }

    /* Ensure consistent card heights in each row */
    @supports (display: grid) {
        .grid > .related-product-card {
            align-self: stretch;
        }
    }

    /* Fix for very long product names */
    .related-product-card h3 {
        word-break: break-word;
        hyphens: auto;
    }

    /* Price alignment */
    .related-product-card .text-blue-700 {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Button improvements for touch devices */
    @media (pointer: coarse) {
        .related-product-actions button,
        .related-product-actions a {
            min-height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    }

    /* Responsive text scaling */
    @media (min-width: 1024px) {
        .related-product-card h3 {
            min-height: 3rem;
        }
    }

    @media (min-width: 1280px) {
        .related-product-card h3 {
            min-height: 3.5rem;
        }
    }
    </style>

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    {{-- JavaScript --}}
<script>
    // Global variables
    let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
    let currentRating = 0;
    
    if (!csrfToken) {
        console.error('CSRF token not found');
    }

    // DOM Content Loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing...');
        console.log('CSRF Token available:', !!csrfToken);
        
        if (csrfToken) {
            updateCartCount();
        }
        
        // Initialize all systems
        initializeRatingSystem();
        initializeMediaUpload();
        initializeDragDrop();
        
        // Close modal on click outside
        const mediaModal = document.getElementById('media-modal');
        if (mediaModal) {
            mediaModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeMediaModal();
                }
            });
        }
        
        // Handle form submission
        const reviewForm = document.getElementById('review-form');
        if (reviewForm) {
            reviewForm.addEventListener('submit', function(e) {
                if (!validateReviewForm()) {
                    e.preventDefault();
                    showAlert('error', 'Mohon lengkapi rating dan komentar sebelum mengirim ulasan.');
                }
            });
        }
        
        console.log('All systems initialized');
    });

    // ============ MAIN FUNCTIONS ============

    function changeMainImage(src) {
        document.getElementById('main-image').src = src;
        
        // Update thumbnail borders
        document.querySelectorAll('[onclick*="changeMainImage"]').forEach(img => {
            img.classList.remove('border-blue-500');
            img.classList.add('border-gray-300');
        });
        event.target.classList.remove('border-gray-300');
        event.target.classList.add('border-blue-500');
    }

    function increaseQuantity() {
        const input = document.getElementById('quantity');
        const max = parseInt(input.getAttribute('max'));
        const current = parseInt(input.value);
        
        if (current < max) {
            input.value = current + 1;
        }
    }

    function decreaseQuantity() {
        const input = document.getElementById('quantity');
        const current = parseInt(input.value);
        
        if (current > 1) {
            input.value = current - 1;
        }
    }

    function addToCart() {
        console.log('addToCart function called');
        
        if (!csrfToken) {
            showAlert('error', 'Token keamanan tidak tersedia. Silakan refresh halaman.');
            return;
        }

        const productId = '{{ $product->product_id }}';
        const adminId = '{{ $product->admin_id }}';
        const quantityInput = document.getElementById('quantity');
        const quantity = quantityInput ? quantityInput.value : 1;
        
        const data = {
            product_id: productId,
            admin_id: adminId,
            quantity: parseInt(quantity),
            _token: csrfToken
        };

        console.log('Sending cart data:', data);

        // Disable button during request
        const button = event.target;
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menambahkan...';

        // Menggunakan route carts.store yang baru
        fetch('{{ route("carts.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(async response => {
            console.log('Response status:', response.status);
            
            const responseText = await response.text();
            console.log('Raw response:', responseText);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}, response: ${responseText}`);
            }
            
            try {
                const jsonData = JSON.parse(responseText);
                return jsonData;
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Invalid JSON response from server');
            }
        })
        .then(data => {
            console.log('paid response:', data);
            if (data.paid) {
                showAlert('paid', data.message);
                updateCartCount();
            } else {
                showAlert('error', data.message || 'Gagal menambahkan ke keranjang');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showAlert('error', 'Terjadi kesalahan: ' + error.message);
        })
        .finally(() => {
            // Re-enable button
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }

    function buyNow() {
        const productId = '{{ $product->product_id }}';
        const quantityInput = document.getElementById('quantity');
        const quantity = quantityInput ? quantityInput.value : 1;
        
        // Redirect ke halaman checkout langsung
        window.location.href = `{{ route('orders.create') }}?product_id=${productId}&quantity=${quantity}`;
    }

    function addToCartFromRelated(productId, adminId) {
        if (!csrfToken) {
            showAlert('error', 'Token keamanan tidak tersedia. Silakan refresh halaman.');
            return;
        }

        const data = {
            product_id: productId,
            admin_id: adminId,
            quantity: 1,
            _token: csrfToken
        };

        // Disable button during request
        const button = event.target;
        const originalContent = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Menggunakan route carts.store yang baru
        fetch('{{ route("carts.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.paid) {
                showAlert('paid', data.message);
                updateCartCount();
            } else {
                showAlert('error', data.message || 'Gagal menambahkan ke keranjang');
            }
        })
        .catch(error => {
            console.error('Related product cart error:', error);
            showAlert('error', 'Terjadi kesalahan saat menambahkan ke keranjang');
        })
        .finally(() => {
            // Re-enable button
            button.disabled = false;
            button.innerHTML = originalContent;
        });
    }

    function updateCartCount() {
        // Menggunakan route carts.count yang baru
        fetch('{{ route("carts.count") }}', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const cartCount = document.getElementById('cart-count');
            if (cartCount) {
                cartCount.textContent = data.count || 0;
            }
        })
        .catch(error => console.error('Error updating cart count:', error));
    }

    function showAlert(type, message) {
        console.log('Showing alert:', type, message);
        
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.custom-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert fixed top-4 right-4 z-50 p-3 sm:p-4 rounded-lg shadow-lg max-w-xs sm:max-w-sm transition-all duration-300 ${
            type === 'paid' ? 'bg-green-100 border border-green-400 text-green-700' : 
            'bg-red-100 border border-red-400 text-red-700'
        }`;
        
        alertDiv.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'paid' ? 'check' : 'exclamation'}-circle mr-2"></i>
                <span class="text-sm">${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-lg hover:opacity-70">&times;</button>
            </div>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentElement) {
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (alertDiv.parentElement) {
                        alertDiv.remove();
                    }
                }, 300);
            }
        }, 5000);
    }

    // ============ REVIEW FUNCTIONS ============

    // Filter Reviews
    function filterReviews(type) {
        const reviews = document.querySelectorAll('.review-item');
        const filterBtns = document.querySelectorAll('.filter-btn');
        
        // Update active button
        filterBtns.forEach(btn => {
            btn.classList.remove('active');
            btn.classList.add('border-gray-300', 'text-gray-700');
            btn.classList.remove('border-red-500', 'bg-red-50', 'text-red-600');
        });
        
        const activeBtn = document.querySelector(`[data-filter="${type}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.classList.remove('border-gray-300', 'text-gray-700');
            activeBtn.classList.add('border-red-500', 'bg-red-50', 'text-red-600');
        }
        
        // Filter reviews
        reviews.forEach(review => {
            let show = false;
            
            if (type === 'all') {
                show = true;
            } else if (type === 'comment') {
                show = review.getAttribute('data-comment') === '1';
            } else if (type === 'media') {
                show = review.getAttribute('data-media') === '1';
            } else {
                show = review.getAttribute('data-rating') === type.toString();
            }
            
            if (show) {
                review.classList.remove('hidden');
            } else {
                review.classList.add('hidden');
            }
        });
    }

    // Media Modal Functions
    function openMediaModal(src, type) {
        const modal = document.getElementById('media-modal');
        const content = document.getElementById('media-content');
        
        if (type === 'video') {
            content.innerHTML = `
                <video controls class="max-w-full max-h-full rounded-lg" autoplay>
                    <source src="${src}" type="video/mp4">
                    Browser Anda tidak mendukung video.
                </video>
            `;
        } else {
            content.innerHTML = `<img src="${src}" alt="Review image" class="max-w-full max-h-full rounded-lg">`;
        }
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeMediaModal() {
        const modal = document.getElementById('media-modal');
        const content = document.getElementById('media-content');
        
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
        
        // Stop any playing video
        const video = content.querySelector('video');
        if (video) {
            video.pause();
            video.currentTime = 0;
        }
        
        content.innerHTML = '';
    }

    // ============ RATING SYSTEM ============

    function initializeRatingSystem() {
        console.log('Initializing rating system...');
        
        const ratingInput = document.querySelector('.rating-input');
        const ratingValue = document.getElementById('rating-value');
        const submitBtn = document.getElementById('submit-review');
        const commentTextarea = document.getElementById('comment');
        const ratingText = document.querySelector('.rating-text');
        
        if (!ratingInput) {
            console.log('Rating input not found - probably no review form');
            return;
        }
        
        const stars = ratingInput.querySelectorAll('.rating-star');
        console.log('Found stars:', stars.length);
        
        if (stars.length === 0) {
            console.error('No rating stars found');
            return;
        }
        
        // Add event listeners to each star
        stars.forEach((star, index) => {
            const rating = parseInt(star.getAttribute('data-rating'));
            
            // Mouse enter - preview rating
            star.addEventListener('mouseenter', function() {
                highlightStars(rating);
                updateRatingText(rating);
            });
            
            // Click - set rating
            star.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                setRating(rating);
                console.log('Selected rating:', rating);
                
                // Visual feedback
                this.style.transform = 'scale(1.2)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
            
            // Touch support for mobile
            star.addEventListener('touchend', function(e) {
                e.preventDefault();
                setRating(rating);
            });
        });
        
        // Mouse leave - show current rating
        ratingInput.addEventListener('mouseleave', function() {
            highlightStars(currentRating);
            updateRatingText(currentRating);
        });
        
        // Function to highlight stars
        function highlightStars(count) {
            stars.forEach((star, index) => {
                if ((index + 1) <= count) {
                    // Filled star
                    star.classList.remove('far', 'text-gray-300');
                    star.classList.add('fas', 'active');
                    star.style.color = '#fbbf24';
                } else {
                    // Empty star
                    star.classList.remove('fas', 'active');
                    star.classList.add('far', 'text-gray-300');
                    star.style.color = '#d1d5db';
                }
            });
        }
        
        // Function to set rating
        function setRating(rating) {
            currentRating = rating;
            ratingInput.setAttribute('data-rating', rating);
            
            if (ratingValue) {
                ratingValue.value = rating;
            }
            
            highlightStars(rating);
            updateRatingText(rating);
            checkFormValidity();
            
            console.log('Rating set to:', rating);
        }
        
        // Function to update rating text
        function updateRatingText(rating) {
            if (!ratingText) return;
            
            const ratingLabels = {
                0: 'Pilih rating',
                1: 'Sangat Buruk',
                2: 'Buruk', 
                3: 'Biasa',
                4: 'Bagus',
                5: 'Sangat Bagus'
            };
            
            ratingText.textContent = ratingLabels[rating] || 'Pilih rating';
        }
        
        // Comment validation
        if (commentTextarea) {
            commentTextarea.addEventListener('input', function() {
                checkFormValidity();
            });
        }
        
        // Form validation
        function checkFormValidity() {
            const hasRating = currentRating > 0;
            const hasComment = commentTextarea ? commentTextarea.value.trim().length >= 10 : false;
            const isValid = hasRating && hasComment;
            
            if (submitBtn) {
                submitBtn.disabled = !isValid;
                
                if (isValid) {
                    submitBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                    submitBtn.classList.add('bg-red-500', 'hover:bg-red-600');
                } else {
                    submitBtn.classList.remove('bg-red-500', 'hover:bg-red-600');
                    submitBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                }
            }
        }
        
        // Initial form check
        checkFormValidity();
        
        console.log('Rating system initialized paidfully');
    }

function validateReviewForm() {
        const hasRating = currentRating > 0;
        const comment = document.getElementById('comment');
        const hasComment = comment ? comment.value.trim().length >= 10 : false;
        
        return hasRating && hasComment;
    }

    // ============ MEDIA UPLOAD FUNCTIONS ============

    function initializeMediaUpload() {
        console.log('Initializing media upload...');
        
        const mediaInput = document.getElementById('media');
        if (!mediaInput) {
            console.log('Media input not found - probably no review form');
            return;
        }
        
        mediaInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                console.log('File selected:', file.name, file.type, file.size);
                
                if (validateFile(file)) {
                    previewMedia(file);
                } else {
                    clearMediaUpload();
                }
            }
        });
        
        console.log('Media upload initialized');
    }

    function validateFile(file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp',
            'video/mp4', 'video/webm', 'video/ogg', 'video/mov', 'video/avi', 'video/3gp'
        ];
        
        if (file.size > maxSize) {
            showAlert('error', 'Ukuran file terlalu besar. Maksimal 10MB.');
            return false;
        }
        
        if (!allowedTypes.includes(file.type.toLowerCase())) {
            showAlert('error', 'Format file tidak didukung. Gunakan format gambar atau video yang valid.');
            return false;
        }
        
        return true;
    }

    function previewMedia(file) {
        const container = document.getElementById('media-preview-container');
        const preview = document.getElementById('media-preview');
        
        if (!container || !preview) {
            console.error('Preview containers not found');
            return;
        }
        
        const reader = new FileReader();
        const isVideo = file.type.startsWith('video/');
        
        reader.onload = function(e) {
            const mediaHtml = isVideo 
                ? `<video class="media-preview-item" controls>
                     <source src="${e.target.result}" type="${file.type}">
                     Browser Anda tidak mendukung video.
                   </video>
                   <div class="ml-3">
                     <div class="text-sm font-medium text-gray-900">${file.name}</div>
                     <div class="text-xs text-gray-500">Video • ${formatFileSize(file.size)}</div>
                   </div>`
                : `<img src="${e.target.result}" alt="Preview" class="media-preview-item">
                   <div class="ml-3">
                     <div class="text-sm font-medium text-gray-900">${file.name}</div>
                     <div class="text-xs text-gray-500">Gambar • ${formatFileSize(file.size)}</div>
                   </div>`;
            
            preview.innerHTML = mediaHtml;
            container.classList.remove('hidden');
        };
        
        reader.readAsDataURL(file);
    }

    function clearMediaUpload() {
        const mediaInput = document.getElementById('media');
        const container = document.getElementById('media-preview-container');
        const preview = document.getElementById('media-preview');
        
        if (mediaInput) mediaInput.value = '';
        if (container) container.classList.add('hidden');
        if (preview) preview.innerHTML = '';
        
        console.log('Media upload cleared');
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // ============ DRAG & DROP FUNCTIONS ============

    function initializeDragDrop() {
        console.log('Initializing drag & drop...');
        
        const uploadArea = document.querySelector('.file-upload-area');
        if (!uploadArea) {
            console.log('Upload area not found - probably no review form');
            return;
        }
        
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        // Highlight drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });
        
        // Handle dropped files
        uploadArea.addEventListener('drop', handleDrop, false);
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        function highlight(e) {
            uploadArea.classList.add('dragover');
        }
        
        function unhighlight(e) {
            uploadArea.classList.remove('dragover');
        }
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                const file = files[0];
                const mediaInput = document.getElementById('media');
                
                if (validateFile(file)) {
                    // Create a new FileList with the dropped file
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    mediaInput.files = dataTransfer.files;
                    
                    previewMedia(file);
                    showAlert('paid', 'File berhasil ditambahkan!');
                }
            }
        }
        
        console.log('Drag & drop initialized');
    }

    // ============ KEYBOARD SUPPORT ============

    // Add keyboard support for accessibility
    document.addEventListener('keydown', function(e) {
        // Close modal with Escape key
        if (e.key === 'Escape') {
            const modal = document.getElementById('media-modal');
            if (modal && !modal.classList.contains('hidden')) {
                closeMediaModal();
            }
        }
        
        // Star rating keyboard navigation
        if (e.target.classList.contains('rating-star')) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                e.target.click();
            }
        }
    });

    // ============ UTILITY FUNCTIONS ============

    // Debounce function for search/filter operations
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

    // Format currency for Indonesia
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    // Handle network errors
    function handleNetworkError(error) {
        console.error('Network error:', error);
        
        if (!navigator.onLine) {
            showAlert('error', 'Tidak ada koneksi internet. Periksa koneksi Anda dan coba lagi.');
        } else {
            showAlert('error', 'Terjadi kesalahan jaringan. Silakan coba lagi.');
        }
    }

    // Check if user is online
    window.addEventListener('online', function() {
        console.log('User is online');
        showAlert('paid', 'Koneksi internet tersambung kembali.');
    });

    window.addEventListener('offline', function() {
        console.log('User is offline');
        showAlert('error', 'Koneksi internet terputus.');
    });

    // ============ PERFORMANCE OPTIMIZATIONS ============

    // Lazy load images
    function initializeLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    // Initialize lazy loading when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeLazyLoading);
    } else {
        initializeLazyLoading();
    }

    // ============ ERROR HANDLING ============

    // Global error handler
    window.addEventListener('error', function(e) {
        console.error('Global error:', e.error);
        
        // Don't show alerts for every error, just log them
        if (e.error && e.error.name !== 'ChunkLoadError') {
            // Handle specific errors that users should know about
            if (e.error.message.includes('CSRF')) {
                showAlert('error', 'Sesi keamanan expired. Silakan refresh halaman.');
            }
        }
    });

    // Handle unhandled promise rejections
    window.addEventListener('unhandledrejection', function(e) {
        console.error('Unhandled promise rejection:', e.reason);
        
        // Handle specific promise rejections
        if (e.reason && e.reason.message) {
            if (e.reason.message.includes('fetch')) {
                handleNetworkError(e.reason);
            } else if (e.reason.message.includes('CSRF')) {
                showAlert('error', 'Token keamanan tidak valid. Silakan refresh halaman.');
            }
        }
    });

    // ============ MOBILE OPTIMIZATIONS ============

    // Handle mobile viewport changes
    function handleViewportChange() {
        // Adjust modal sizing on mobile
        const modal = document.getElementById('media-modal');
        if (modal && !modal.classList.contains('hidden')) {
            const content = modal.querySelector('#media-content');
            if (content) {
                const windowHeight = window.innerHeight;
                const windowWidth = window.innerWidth;
                
                if (windowWidth < 640) { // Mobile
                    content.style.maxHeight = `${windowHeight * 0.8}px`;
                    content.style.maxWidth = `${windowWidth * 0.9}px`;
                }
            }
        }
    }

    // Listen for orientation changes on mobile
    window.addEventListener('orientationchange', function() {
        setTimeout(handleViewportChange, 100);
    });

    window.addEventListener('resize', debounce(handleViewportChange, 250));

    // ============ ACCESSIBILITY ENHANCEMENTS ============

    // Add ARIA labels and roles
    function enhanceAccessibility() {
        // Add proper ARIA labels to rating stars
        document.querySelectorAll('.rating-star').forEach((star, index) => {
            star.setAttribute('role', 'button');
            star.setAttribute('tabindex', '0');
            star.setAttribute('aria-label', `Rating ${index + 1} star`);
        });

        // Add ARIA labels to filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.setAttribute('role', 'button');
            if (!btn.hasAttribute('aria-label')) {
                btn.setAttribute('aria-label', `Filter: ${btn.textContent.trim()}`);
            }
        });

        // Add ARIA labels to quantity controls
        const decreaseBtn = document.querySelector('button[onclick="decreaseQuantity()"]');
        const increaseBtn = document.querySelector('button[onclick="increaseQuantity()"]');
        
        if (decreaseBtn) {
            decreaseBtn.setAttribute('aria-label', 'Kurangi jumlah');
        }
        if (increaseBtn) {
            increaseBtn.setAttribute('aria-label', 'Tambah jumlah');
        }
    }

    // Initialize accessibility enhancements
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', enhanceAccessibility);
    } else {
        enhanceAccessibility();
    }

    // ============ FINAL INITIALIZATION ============

    console.log('All JavaScript modules loaded paidfully');

    // Export functions for external use if needed
    window.ProductView = {
        addToCart,
        buyNow,
        addToCartFromRelated,
        updateCartCount,
        filterReviews,
        openMediaModal,
        closeMediaModal,
        showAlert
    };
</script>

</x-app-layout>