<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Admin POS - Pesanan') }}
            </h2>
            <div class="flex items-center space-x-2">
                <button id="scan-customer-btn" class="flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl transition-all transform hover:scale-105 shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 12h-4.01M12 12v4m6-6h.01M12 8H8m0 0V4.01M8 8v4m0 0h.01m0-.01h2.99"></path>
                    </svg>
                    Pindai QR Pelanggan
                </button>
            </div>
        </div>
    </x-slot>

    <div id="success-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-8 border-0 w-11/12 md:w-1/2 max-w-md shadow-2xl rounded-3xl bg-white dark:bg-gray-800 transform transition-all">
            <div class="text-center mb-8">
                <div class="w-24 h-24 bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/30 dark:to-green-800/30 rounded-full flex items-center justify-center mx-auto mb-6 animate-pulse">
                    <svg class="w-12 h-12 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-3">Pembayaran Berhasil!</h3>
                <p class="text-gray-600 dark:text-gray-400 text-lg" id="success-message">Pesanan telah berhasil diproses</p>
            </div>
            
            <div class="flex justify-center space-x-4">
                <button onclick="closeSuccessModal()" class="px-8 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all">
                    Tutup
                </button>
                <button onclick="printReceipt()" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all">
                    Cetak Struk
                </button>
            </div>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="p-8 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Daftar Menu</h3>
                            </div>
                            
                            <div class="mb-6">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" id="product-search" placeholder="Cari produk..." 
                                           class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm">
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-3" id="category-tabs">
                                <button class="category-tab active px-6 py-3 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-semibold shadow-lg transform hover:scale-105 transition-all" data-category="all">Semua</button>
                                @foreach($categories as $category)
                                <button class="category-tab px-6 py-3 rounded-full bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold shadow-md border border-gray-200 dark:border-gray-600 transform hover:scale-105 transition-all" data-category="{{ $category->category_id }}">
                                    {{ $category->name }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="p-8">
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6" id="products-grid">
                                @foreach($products as $product)
                                <div class="product-card bg-white dark:bg-gray-700 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer border border-gray-100 dark:border-gray-600 group overflow-hidden" 
                                     data-product-id="{{ $product->product_id }}" 
                                     data-category="{{ $product->category_id }}"
                                     data-name="{{ strtolower($product->name) }}"
                                     data-stock="{{ $product->stock }}"
                                     onclick="addToCart('{{ $product->product_id }}', '{{ $product->name }}', {{ $product->price }}, '{{ $product->admin_id }}')">
                                    <div class="aspect-square overflow-hidden rounded-t-lg">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100 dark:bg-gray-600">
                                                <svg class="w-16 h-16 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-2 text-center">
                                        <h4 class="font-semibold text-xs text-gray-800 dark:text-gray-200 line-clamp-2" style="height: 2.2rem;">{{ $product->name }}</h4>
                                        <p class="font-bold text-sm text-red-500 dark:text-red-400 mt-1">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Stok: <span id="stock-{{ $product->product_id }}">{{ $product->stock }}</span></p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="flex items-center justify-center mt-6 space-x-4" id="pagination-controls">
                                <button id="prev-page-btn" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                                <span class="text-sm text-gray-600 dark:text-gray-400 font-semibold" id="page-info">Halaman 1</span>
                                <button id="next-page-btn" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 h-full overflow-hidden">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-gray-800 dark:to-gray-700">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Pelanggan</h3>
                                <button id="clear-customer-btn" class="text-red-600 hover:text-red-700 text-sm font-semibold px-3 py-1 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">Hapus</button>
                            </div>
                            
                            <div id="customer-info" class="hidden">
                                <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl p-5 mb-4 border border-blue-200 dark:border-blue-700">
                                    <div class="flex justify-center mb-4">
                                        <img id="customer-photo" 
                                             alt="Profile Photo" 
                                             class="w-20 h-20 object-cover rounded-full border-4 border-blue-200 dark:border-blue-600 shadow-lg"
                                             onerror="this.src='https://ui-avatars.com/api/?name=User&background=e5e7eb&color=374151&size=80';">
                                    </div>
                                    
                                    <div class="text-center mb-3">
                                        <span class="font-bold text-blue-900 dark:text-blue-100 text-lg" id="customer-name">-</span>
                                    </div>
                                    
                                    <div class="text-sm text-blue-700 dark:text-blue-300 space-y-2">
                                        <div class="flex justify-between items-center">
                                            <span>NIS/NIP:</span>
                                            <span class="font-semibold" id="customer-id">-</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span>Role:</span>
                                            <span class="font-semibold capitalize px-2 py-1 bg-blue-200 dark:bg-blue-700 rounded-full text-xs" id="customer-role">-</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span>Saldo:</span>
                                            <span class="font-bold text-lg text-green-600 dark:text-green-400" id="customer-balance">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="no-customer" class="text-center py-12">
                                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">Pindai QR untuk memilih pelanggan</p>
                            </div>
                        </div>

                        <div class="flex-1 overflow-hidden">
                            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">Item Pesanan</h3>
                            </div>
                            
                            <div class="px-6 max-h-80 overflow-y-auto custom-scrollbar">
                                <div id="cart-items" class="space-y-4">
                                    </div>
                                
                                <div id="empty-cart" class="text-center py-16">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5M17 13v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">Keranjang masih kosong</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100" id="subtotal">Rp 0</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Pajak (10%):</span>
                                    <span class="font-semibold text-gray-900 dark:text-gray-100" id="tax">Rp 0</span>
                                </div>
                                <div class="flex justify-between text-lg font-bold border-t border-gray-200 dark:border-gray-600 pt-3">
                                    <span class="text-gray-900 dark:text-gray-100">Total:</span>
                                    <span class="text-blue-600 dark:text-blue-400" id="total">Rp 0</span>
                                </div>
                            </div>

                            <div class="mb-6">
                                <div class="grid grid-cols-1 gap-3">
                                    <button id="pay-balance" class="w-full px-6 py-4 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed disabled:transform-none" disabled>
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Bayar dengan Saldo
                                    </button>
                                    <button id="pay-cash" class="w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all disabled:from-gray-400 disabled:to-gray-500 disabled:cursor-not-allowed disabled:transform-none" disabled>
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        Bayar Tunai
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-3">
                                <button id="clear-order" class="w-full px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all" onclick="clearOrder()">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Bersihkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="qr-scanner-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative mx-auto p-8 border-0 w-11/12 md:w-3/4 lg:w-1/2 max-w-2xl shadow-2xl rounded-3xl bg-white dark:bg-gray-800 transform transition-all">
            <div class="text-center mb-8">
                <h3 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-3">Pindai QR Pelanggan</h3>
                <p class="text-gray-600 dark:text-gray-400 text-lg">Arahkan kamera ke kode QR pelanggan</p>
            </div>
            
            <div class="text-center mb-6">
                <div id="qr-reader" class="mx-auto border-4 border-blue-200 dark:border-blue-700 rounded-2xl shadow-lg" style="width: 100%; max-width: 400px;"></div>
                <div id="scanner-status" class="mt-4 text-sm text-gray-500 font-medium px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg inline-block">Memulai kamera...</div>
            </div>

            <div class="flex justify-center space-x-4">
                <button onclick="closeQRScanner()" class="px-8 py-3 bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white rounded-xl font-semibold shadow-lg transform hover:scale-105 transition-all">
                    Batal
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script>
            // Variabel global
            let currentCustomer = null;
            let cartItems = [];
            let html5QrCode = null;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Map untuk menyimpan stok produk saat ini
            const productStocks = {};

            // Variabel untuk paginasi
            let currentPage = 1;
            let productsPerPage;
            let allProducts = [];
            let filteredProducts = [];

            document.addEventListener('DOMContentLoaded', function () {
                // Tentukan jumlah produk per halaman berdasarkan ukuran layar
                productsPerPage = window.innerWidth >= 1024 ? 12 : 8;

                // Inisialisasi stok dan ambil semua produk
                document.querySelectorAll('.product-card').forEach(card => {
                    const productId = card.dataset.productId;
                    const stock = parseInt(card.dataset.stock);
                    productStocks[productId] = stock;
                    allProducts.push(card);
                });

                // Inisialisasi produk yang difilter dan tampilkan halaman pertama
                filteredProducts = [...allProducts];
                displayProducts();
                
                initializeEventListeners();
            });

            function initializeEventListeners() {
                // Tab kategori
                document.querySelectorAll('.category-tab').forEach(tab => {
                    tab.addEventListener('click', function() {
                        document.querySelectorAll('.category-tab').forEach(t => {
                            t.classList.remove('active', 'from-blue-500', 'to-blue-600', 'text-white');
                            t.classList.add('bg-white', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
                        });
                        this.classList.add('active', 'from-blue-500', 'to-blue-600', 'text-white');
                        this.classList.remove('bg-white', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300');
                        filterAndPaginateProducts();
                    });
                });

                // Pencarian produk
                document.getElementById('product-search').addEventListener('input', filterAndPaginateProducts);

                // Kontrol paginasi
                document.getElementById('prev-page-btn').addEventListener('click', () => changePage(-1));
                document.getElementById('next-page-btn').addEventListener('click', () => changePage(1));

                // Scanner QR - Langsung mulai scan tanpa perlu klik dua kali
                document.getElementById('scan-customer-btn').addEventListener('click', function() {
                    openQRScanner();
                    // Langsung mulai scanning
                    setTimeout(startQRScanning, 500);
                });
                
                document.getElementById('clear-customer-btn').addEventListener('click', clearCustomer);

                // Tombol pembayaran
                document.getElementById('pay-balance').addEventListener('click', () => processPayment('balance'));
                document.getElementById('pay-cash').addEventListener('click', () => processPayment('cash'));
            }

            function filterAndPaginateProducts() {
                const searchTerm = document.getElementById('product-search').value.toLowerCase();
                const activeCategory = document.querySelector('.category-tab.active').dataset.category || 'all';

                filteredProducts = allProducts.filter(product => {
                    const name = product.dataset.name;
                    const category = product.dataset.category;
                    const matchesSearch = !searchTerm || name.includes(searchTerm);
                    const matchesCategory = activeCategory === 'all' || category === activeCategory;
                    return matchesSearch && matchesCategory;
                });

                currentPage = 1;
                displayProducts();
            }

            function displayProducts() {
                const productsGrid = document.getElementById('products-grid');
                productsGrid.innerHTML = ''; // Kosongkan grid

                const startIndex = (currentPage - 1) * productsPerPage;
                const endIndex = startIndex + productsPerPage;
                const productsToShow = filteredProducts.slice(startIndex, endIndex);

                if (productsToShow.length === 0 && currentPage > 1) {
                    currentPage--;
                    displayProducts();
                    return;
                }

                productsToShow.forEach(product => {
                    productsGrid.appendChild(product);
                });

                updatePaginationControls();
            }

            function updatePaginationControls() {
                const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
                const prevBtn = document.getElementById('prev-page-btn');
                const nextBtn = document.getElementById('next-page-btn');
                const pageInfo = document.getElementById('page-info');

                pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages === 0 ? 1 : totalPages}`;
                prevBtn.disabled = currentPage === 1;
                nextBtn.disabled = currentPage === totalPages || filteredProducts.length === 0;
            }

            function changePage(change) {
                const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
                const newPage = currentPage + change;

                if (newPage >= 1 && newPage <= totalPages) {
                    currentPage = newPage;
                    displayProducts();
                }
            }

            function openQRScanner() {
                document.getElementById('qr-scanner-modal').classList.remove('hidden');
                if (!html5QrCode) {
                    html5QrCode = new Html5Qrcode("qr-reader");
                }
            }

            function closeQRScanner() {
                document.getElementById('qr-scanner-modal').classList.add('hidden');
                if (html5QrCode && html5QrCode.isScanning) {
                    html5QrCode.stop();
                }
            }

            function startQRScanning() {
                const scannerStatus = document.getElementById("scanner-status");

                scannerStatus.textContent = "Arahkan kamera ke Kode QR...";

                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
                    // Berhenti memindai setelah berhasil
                    html5QrCode.stop().then(() => {
                        handleCustomerQR(decodedText);
                        closeQRScanner();
                    });
                }).catch(err => {
                    scannerStatus.innerHTML = `<span class="text-red-500">Error: ${err}</span>`;
                });
            }

            function handleCustomerQR(qrData) {
                fetch('{{ route("api.qr.profile") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ qr_data: qrData })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        setCustomer(data.data.profile);
                        showAlert('Pelanggan berhasil dipilih!', 'success');
                    } else {
                        showAlert(data.message || 'QR tidak valid', 'error');
                    }
                })
                .catch(error => {
                    showAlert('Error memindai QR: ' + error.message, 'error');
                });
            }

            function setCustomer(customer) {
                currentCustomer = customer;
                
                // Tampilkan info pelanggan
                document.getElementById('customer-info').classList.remove('hidden');
                document.getElementById('no-customer').classList.add('hidden');
                
                // Isi data pelanggan
                document.getElementById('customer-name').textContent = customer.full_name;
                document.getElementById('customer-id').textContent = customer.student_id || '-';
                document.getElementById('customer-role').textContent = customer.role || 'customer';
                document.getElementById('customer-balance').textContent = customer.formatted_balance;
                
                // Set foto profil
                const profilePhoto = document.getElementById('customer-photo');
                if (customer.profile_photo_url) {
                    profilePhoto.src = customer.profile_photo_url;
                } else {
                    profilePhoto.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(customer.full_name)}&background=e5e7eb&color=374151&size=80`;
                }
                profilePhoto.onerror = function() {
                    this.src = `https://ui-avatars.com/api/?name=${encodeURIComponent(customer.full_name)}&background=e5e7eb&color=374151&size=80`;
                };
                
                updatePaymentButtons();
            }

            function clearCustomer() {
                currentCustomer = null;
                
                // Sembunyikan info pelanggan
                document.getElementById('customer-info').classList.add('hidden');
                document.getElementById('no-customer').classList.remove('hidden');
                
                updatePaymentButtons();
            }

            function addToCart(productId, productName, productPrice, adminId) {
                if (!currentCustomer) {
                    showAlert('Silakan pilih pelanggan terlebih dahulu!', 'error');
                    return;
                }

                const productCurrentStock = productStocks[productId];
                const existingItem = cartItems.find(item => item.productId === productId);
                const currentQuantityInCart = existingItem ? existingItem.quantity : 0;
                
                // Periksa apakah stok mencukupi
                if (productCurrentStock <= 0) {
                    showAlert('Stok produk sudah habis!', 'error');
                    return;
                }

                if (existingItem) {
                    existingItem.quantity += 1;
                    existingItem.subtotal = existingItem.quantity * existingItem.price;
                } else {
                    cartItems.push({
                        productId: productId,
                        name: productName,
                        price: productPrice,
                        quantity: 1,
                        subtotal: productPrice,
                        adminId: adminId
                    });
                }
                
                // Kurangi stok di tampilan
                updateStockDisplay(productId, -1);
                
                updateCartDisplay();
                updateOrderSummary();
                updatePaymentButtons();
                
                // Animasi feedback
                showAlert(`${productName} ditambahkan ke keranjang`, 'success');
            }

            function updateCartDisplay() {
                const cartContainer = document.getElementById('cart-items');
                const emptyCart = document.getElementById('empty-cart');
                
                if (cartItems.length === 0) {
                    cartContainer.innerHTML = '';
                    emptyCart.classList.remove('hidden');
                    return;
                }
                
                emptyCart.classList.add('hidden');
                cartContainer.innerHTML = cartItems.map(item => `
                    <div class="w-full p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex-1">
                            <h5 class="font-semibold text-gray-900 dark:text-gray-100 text-sm mb-1">${item.name}</h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Rp ${item.price.toLocaleString()} / item</p>
                        </div>

                        <div class="flex items-center space-x-3">
                            <button onclick="updateQuantity('${item.productId}', -1)" class="w-8 h-8 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-full text-xs font-bold shadow-lg transform hover:scale-110 transition-all">-</button>
                            <span class="w-8 text-center text-sm font-bold bg-white dark:bg-gray-800 px-2 py-1 rounded-lg shadow-sm">${item.quantity}</span>
                            <button onclick="updateQuantity('${item.productId}', 1)" class="w-8 h-8 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white rounded-full text-xs font-bold shadow-lg transform hover:scale-110 transition-all">+</button>
                        </div>

                        <div class="text-right sm:ml-4">
                            <p class="font-bold text-gray-900 dark:text-gray-100 text-sm">Rp ${item.subtotal.toLocaleString()}</p>
                        </div>
                    </div>
                `).join('');
            }

            function updateQuantity(productId, change) {
                const item = cartItems.find(item => item.productId === productId);
                if (!item) return;

                const productCurrentStock = productStocks[productId];

                // Periksa stok saat menambah
                if (change > 0 && productCurrentStock <= 0) {
                    showAlert('Stok produk sudah habis!', 'error');
                    return;
                }
                
                item.quantity += change;
                item.subtotal = item.quantity * item.price;
                
                // Perbarui stok di tampilan
                updateStockDisplay(productId, -change);
                
                if (item.quantity <= 0) {
                    cartItems = cartItems.filter(item => item.productId !== productId);
                    showAlert('Item dihapus dari keranjang', 'info');
                }
                
                updateCartDisplay();
                updateOrderSummary();
                updatePaymentButtons();
            }

            function updateStockDisplay(productId, change) {
                const stockElement = document.getElementById(`stock-${productId}`);
                if (stockElement) {
                    let currentStock = parseInt(stockElement.textContent);
                    currentStock += change;
                    stockElement.textContent = currentStock;
                    productStocks[productId] = currentStock; // Perbarui data stok di map
                }
            }

            function updateOrderSummary() {
                const subtotal = cartItems.reduce((sum, item) => sum + item.subtotal, 0);
                const tax = subtotal * 0.1;
                const total = subtotal + tax;
                
                document.getElementById('subtotal').textContent = `Rp ${subtotal.toLocaleString()}`;
                document.getElementById('tax').textContent = `Rp ${Math.round(tax).toLocaleString()}`;
                document.getElementById('total').textContent = `Rp ${Math.round(total).toLocaleString()}`;
            }

            function updatePaymentButtons() {
                const balanceBtn = document.getElementById('pay-balance');
                const cashBtn = document.getElementById('pay-cash');
                
                const hasCustomer = currentCustomer !== null;
                const hasItems = cartItems.length > 0;
                const canPay = hasCustomer && hasItems;
                
                balanceBtn.disabled = !canPay;
                cashBtn.disabled = !canPay;
            }

            function processPayment(method) {
                if (!currentCustomer || cartItems.length === 0) {
                    showAlert('Pelanggan dan item harus dipilih!', 'error');
                    return;
                }
                
                const subtotal = cartItems.reduce((sum, item) => sum + item.subtotal, 0);
    const tax = subtotal * 0.1;
    const total = subtotal + tax; // <-- Hapus Math.round()
                
                if (method === 'balance' && currentCustomer.balance < total) {
                    showAlert('Saldo pelanggan tidak mencukupi!', 'error');
                    return;
                }
                
                // Siapkan data pesanan untuk POS
                const orderData = {
        customer_id: currentCustomer.user_id,
        items: cartItems.map(item => ({
            product_id: item.productId,
            quantity: item.quantity
        })),
        payment_method: method,
        // Kirim nilai total yang presisi, bisa berupa float/desimal
        total_amount: parseFloat(total.toFixed(2)), 
        notes: `POS Order - ${method === 'balance' ? 'Paid by Balance' : 'Cash Payment'}`
    };
                
                // Proses pesanan via endpoint POS
                fetch('{{ route("admin.pos.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const paymentMethodText = method === 'balance' ? 'Saldo' : 'Tunai';
                        showSuccessModal(`Pembayaran berhasil dengan ${paymentMethodText}! Total: Rp ${total.toLocaleString()}`);
                        clearOrder();
                        
                        // Perbarui saldo pelanggan jika dibayar dengan saldo
                        if (method === 'balance' && data.data.customer_new_balance !== undefined) {
                            currentCustomer.balance = data.data.customer_new_balance;
                            document.getElementById('customer-balance').textContent = 
                                `Rp ${currentCustomer.balance.toLocaleString()}`;
                        }
                    } else {
                        showAlert(data.message || 'Gagal memproses pembayaran', 'error');
                    }
                })
                .catch(error => {
                    showAlert('Error: ' + error.message, 'error');
                });
            }

            function clearOrder() {
                // Kembalikan stok yang ada di keranjang
                cartItems.forEach(item => {
                    updateStockDisplay(item.productId, item.quantity);
                });
                
                cartItems = [];
                updateCartDisplay();
                updateOrderSummary();
                updatePaymentButtons();
            }

            function showSuccessModal(message) {
                document.getElementById('success-message').textContent = message;
                document.getElementById('success-modal').classList.remove('hidden');
            }

            function closeSuccessModal() {
                document.getElementById('success-modal').classList.add('hidden');
            }

            function printReceipt() {
                // Implementasi fungsi cetak
                window.print();
                closeSuccessModal();
            }

            function showAlert(message, type) {
                // Buat elemen alert
                const alertDiv = document.createElement('div');
                alertDiv.className = `fixed top-6 right-6 z-50 p-4 rounded-xl shadow-2xl transition-all duration-300 transform translate-x-full max-w-sm`;
                
                if (type === 'success') {
                    alertDiv.className += ' bg-gradient-to-r from-green-500 to-green-600 text-white';
                    alertDiv.innerHTML = `
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="font-semibold">${message}</span>
                        </div>
                    `;
                } else if (type === 'error') {
                    alertDiv.className += ' bg-gradient-to-r from-red-500 to-red-600 text-white';
                    alertDiv.innerHTML = `
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="font-semibold">${message}</span>
                        </div>
                    `;
                } else {
                    alertDiv.className += ' bg-gradient-to-r from-blue-500 to-blue-600 text-white';
                    alertDiv.innerHTML = `
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="font-semibold">${message}</span>
                        </div>
                    `;
                }
                
                document.body.appendChild(alertDiv);
                
                // Animasi masuk
                setTimeout(() => {
                    alertDiv.classList.remove('translate-x-full');
                }, 100);
                
                // Auto hapus setelah 4 detik
                setTimeout(() => {
                    alertDiv.classList.add('translate-x-full');
                    setTimeout(() => {
                        if (document.body.contains(alertDiv)) {
                            document.body.removeChild(alertDiv);
                        }
                    }, 300);
                }, 4000);
            }
        </script>
    @endpush

    @push('styles')
    <style>
        .category-tab.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
            color: white !important;
        }
        
        .product-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .product-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        #qr-reader {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 1rem;
        }

        @media (prefers-color-scheme: dark) {
            #qr-reader {
                background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
            }
        }

        /* Custom scrollbar untuk keranjang */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e3a8a);
        }

        /* Animasi untuk kartu produk */
        @keyframes slideInUp {
            from { 
                opacity: 0; 
                transform: translateY(30px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        .product-card {
            animation: slideInUp 0.4s ease-out;
        }

        /* Efek loading */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        /* Efek untuk tombol pembayaran */
        .payment-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .payment-btn:not(:disabled):hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        /* Efek glassmorphism */
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Animasi untuk modal */
        .modal-enter {
            animation: modalEnter 0.3s ease-out;
        }

        @keyframes modalEnter {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(-10px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Efek hover untuk kategori */
        .category-tab:not(.active):hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        /* Line clamp untuk nama produk */
        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        /* Gradient border */
        .gradient-border {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2px;
            border-radius: 1rem;
        }

        .gradient-border-inner {
            background: white;
            border-radius: calc(1rem - 2px);
        }

        /* Custom focus states */
        .focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
        }

        /* Smooth transitions untuk semua elemen */
        * {
            transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }
    </style>
    @endpush
</x-app-layout>