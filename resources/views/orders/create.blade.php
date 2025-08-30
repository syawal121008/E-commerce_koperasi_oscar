{{-- resources/views/orders/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-lg md:text-xl text-gray-800 leading-tight">
                {{ __('Buat Pesanan') }}
            </h2>
        </div>
    </x-slot>

    @if ($errors->any())
    <div class="alert alert-danger bg-red-100 border border-red-400 text-red-700 px-3 md:px-4 py-3 rounded mb-4 mx-4 md:mx-0">
        <ul class="mb-0 text-sm">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </ul>
    </div>
    @endif

    <div class="py-4 md:py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <form action="{{ route('orders.store') }}" method="POST" id="order-form">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-8">
                    <!-- Order Items -->
                    <div class="lg:col-span-2 order-2 lg:order-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-4 md:p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 md:mb-6">Detail Pesanan</h3>
                                
                                <!-- Selected Product Display (if from product page) -->
                                @if(request('product_id'))
                                    @php
                                        $selectedProduct = $products->firstWhere('product_id', request('product_id'));
                                        $selectedQuantity = request('quantity', 1);
                                    @endphp
                                    
                                    @if($selectedProduct)
                                        <input type="hidden" name="admin_id" value="{{ $selectedProduct->admin_id }}">
                                        <input type="hidden" name="items[0][product_id]" value="{{ $selectedProduct->product_id }}">
                                        <input type="hidden" name="items[0][quantity]" value="{{ $selectedQuantity }}" id="selected-quantity">
                                        
                                        <div class="mb-4 md:mb-6 p-3 md:p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                            <h4 class="font-medium text-blue-800 mb-3">Produk Yang Dipilih:</h4>
                                            <div class="flex flex-col sm:flex-row sm:items-start space-y-3 sm:space-y-0 sm:space-x-4">
                                                <div class="flex-shrink-0 mx-auto sm:mx-0">
                                                    <img src="{{ $selectedProduct->image ? asset('storage/' . $selectedProduct->image) : 'https://via.placeholder.com/80x80?text=No+Image' }}" 
                                                         alt="{{ $selectedProduct->name }}" 
                                                         class="w-20 h-20 object-cover rounded-lg">
                                                </div>
                                                
                                                <div class="flex-grow text-center sm:text-left">
                                                    <h5 class="font-semibold text-gray-800 mb-1">{{ $selectedProduct->name }}</h5>
                                                    <p class="text-gray-600 mb-1">{{ $selectedProduct->formatted_price }}</p>
                                                    <p class="text-sm text-gray-500 mb-1">Penjual: {{ $selectedProduct->admin->full_name }}</p>
                                                    <p class="text-sm text-gray-500 mb-1">NIS/NIP: {{ $selectedProduct->admin->student_id }}</p>
                                                    <p class="text-sm text-gray-500 mb-3">Stok tersedia: {{ $selectedProduct->stock }}</p>
                                                    
                                                    <div>
                                                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                                                        <div class="flex items-center justify-center sm:justify-start space-x-3">
                                                            <div class="flex items-center border rounded-lg">
                                                                <button type="button" onclick="decreaseQuantity()" 
                                                                        class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-l-lg active:bg-gray-200">
                                                                    <i class="fas fa-minus"></i>
                                                                </button>
                                                                <input type="number" id="product-quantity" 
                                                                       value="{{ $selectedQuantity }}" 
                                                                       min="1" max="{{ $selectedProduct->stock }}"
                                                                       class="w-16 md:w-20 px-2 md:px-3 py-2 text-center border-0 focus:ring-0"
                                                                       onchange="updateQuantity()">
                                                                <button type="button" onclick="increaseQuantity()" 
                                                                        class="px-3 py-2 text-gray-600 hover:bg-gray-100 rounded-r-lg active:bg-gray-200">
                                                                    <i class="fas fa-plus"></i>
                                                                </button>
                                                            </div>
                                                            <span class="text-xs md:text-sm text-gray-500">Maksimal {{ $selectedProduct->stock }} unit</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="text-center sm:text-right">
                                                    <p class="font-semibold text-gray-800" id="item-total">
                                                        Rp {{ number_format($selectedProduct->price * $selectedQuantity, 0, ',', '.') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @else
                                    <!-- Manual Product Selection -->
                                    <div class="mb-4 md:mb-6">
                                        <label for="admin_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            Pilih Penjual *
                                        </label>
                                        <select id="admin_id" name="admin_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Pilih Penjual</option>
                                            @foreach($products->groupBy('admin_id') as $adminId => $adminProducts)
                                                @php
                                                    $admin = $adminProducts->first()->admin ?? null;
                                                @endphp
                                                @if($admin)
                                                    <option value="{{ $adminId }}">
                                                        {{ $admin->full_name }} ({{ $admin->student_id }})
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>

                                    <div id="product-selection" class="space-y-4">
                                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center space-y-2 sm:space-y-0">
                                            <h4 class="font-medium text-gray-800">Pilih Produk</h4>
                                            <button type="button" id="add-product-btn" 
                                                    class="bg-green-600 hover:bg-green-700 active:bg-green-800 text-white px-3 py-2 rounded text-sm w-full sm:w-auto" 
                                                    style="display: none;">
                                                <i class="fas fa-plus mr-1"></i>Tambah Produk
                                            </button>
                                        </div>
                                        
                                        <div id="selected-products" class="space-y-4">
                                            <!-- Dynamic product items will be added here -->
                                        </div>
                                    </div>
                                @endif

                                <!-- Customer Information -->
                                <div class="mt-6 pt-6 border-t">
                                    <h4 class="font-medium text-gray-800 mb-4">Informasi Pembeli</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Nama Pembeli
                                            </label>
                                            <input type="text" value="{{ auth()->user()->full_name }}" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-sm" 
                                                   readonly>
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                NIS/NIP
                                            </label>
                                            <input type="text" value="{{ auth()->user()->student_id }}" 
                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-sm" 
                                                   readonly>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                            Catatan (Opsional)
                                        </label>
                                        <textarea id="notes" name="notes" rows="3" 
                                                  placeholder="Catatan untuk penjual..."
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary & Payment -->
                    <div class="lg:col-span-1 order-1 lg:order-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg lg:sticky lg:top-4">
                            <div class="p-4 md:p-6">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4 md:mb-6">Ringkasan Pesanan</h3>
                                
                                @php
                                    $userBalance = auth()->user()->balance;
                                    $initialTotal = 0;
                                    $initialItems = 0;
                                    
                                    if(request('product_id') && $selectedProduct) {
                                        $initialTotal = $selectedProduct->price * $selectedQuantity;
                                        $initialItems = $selectedQuantity;
                                    }
                                @endphp
                                
                                <div class="space-y-3 mb-6 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal (<span id="total-items">{{ $initialItems }}</span> item)</span>
                                        <span id="order-subtotal" class="font-medium">Rp {{ number_format($initialTotal, 0, ',', '.') }}</span>
                                    </div>
                                    
                                    <hr>
                                    
                                    <div class="flex justify-between text-base md:text-lg font-semibold">
                                        <span>Total</span>
                                        <span class="text-blue-600" id="order-total">Rp {{ number_format($initialTotal, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="mb-6">
                                    <h4 class="font-medium text-gray-800 mb-4">Metode Pembayaran</h4>
                                    
                                    <!-- User Balance Info -->
                                    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-blue-800">Saldo Anda:</span>
                                            <span class="font-semibold text-blue-600 text-sm md:text-base">Rp {{ number_format($userBalance, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-2 md:space-y-3">
                                        <div>
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 active:bg-gray-100 cursor-pointer">
                                                <input type="radio" name="payment_method" value="balance" class="mr-3" id="payment-balance">
                                                <div class="flex-grow">
                                                    <span class="font-medium text-sm md:text-base">
                                                        <i class="fas fa-wallet mr-2 text-blue-500"></i>
                                                        Saldo Saya
                                                    </span>
                                                    <p class="text-xs text-gray-500 mt-1">Bayar menggunakan saldo</p>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <div>
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 active:bg-gray-100 cursor-pointer">
                                                <input type="radio" name="payment_method" value="cash" class="mr-3" id="payment-cash">
                                                <div class="flex-grow">
                                                    <span class="font-medium text-sm md:text-base">
                                                        <i class="fas fa-money-bill-wave mr-2 text-green-500"></i>
                                                        Tunai (Cash)
                                                    </span>
                                                    <p class="text-xs text-gray-500 mt-1">Bayar tunai langsung</p>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <div>
                                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 active:bg-gray-100 cursor-pointer">
                                                <input type="radio" name="payment_method" value="mixed" class="mr-3" id="payment-mixed">
                                                <div class="flex-grow">
                                                    <span class="font-medium text-sm md:text-base">
                                                        <i class="fas fa-coins mr-2 text-purple-500"></i>
                                                        Kombinasi (Saldo + Tunai)
                                                    </span>
                                                    <p class="text-xs text-gray-500 mt-1">Gunakan saldo + sisanya tunai</p>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Breakdown (for mixed payment) -->
                                <div id="payment-breakdown" class="hidden mb-6 p-4 border border-purple-200 rounded-lg bg-purple-50">
                                    <h5 class="font-semibold text-purple-800 mb-3 text-sm md:text-base">Rincian Pembayaran</h5>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span>Dari Saldo:</span>
                                            <span id="balance-amount" class="font-medium">Rp 0</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Tunai:</span>
                                            <span id="cash-amount" class="font-medium">Rp 0</span>
                                        </div>
                                        <hr class="my-2">
                                        <div class="flex justify-between font-semibold">
                                            <span>Total:</span>
                                            <span id="total-amount">Rp 0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Warning -->
                                <div id="insufficient-balance-warning" class="hidden mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex flex-col sm:flex-row sm:items-center">
                                        <div class="flex items-start mb-2 sm:mb-0">
                                            <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 mt-0.5"></i>
                                            <span class="text-sm text-yellow-800">Saldo tidak mencukupi. Silakan pilih metode pembayaran lain atau top up saldo.</span>
                                        </div>
                                    </div>
                                    <a href="{{ route('topup.qris') }}" 
                                       class="text-xs text-blue-600 hover:text-blue-800 underline mt-2 inline-block">
                                        <i class="fas fa-plus mr-1"></i>Top Up Saldo
                                    </a>
                                </div>

                                <!-- Order Buttons -->
                                <div class="space-y-3">
                                    <button type="submit" 
                                            class="w-full bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold py-3 px-4 rounded-lg transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed text-sm md:text-base"
                                            id="create-order-btn" 
                                            {{ $initialItems == 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-check-circle mr-2"></i>Buat Pesanan
                                    </button>
                                    
                                    <a href="{{ url()->previous() }}" 
                                       class="w-full bg-gray-600 hover:bg-gray-700 active:bg-gray-800 text-white font-semibold py-3 px-4 rounded-lg transition-colors text-center inline-block text-sm md:text-base">
                                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                                    </a>
                                </div>

                                <div class="mt-6 pt-4 border-t text-xs text-gray-500">
                                    <div class="space-y-2">
                                        <p class="flex items-start">
                                            <i class="fas fa-shield-alt mr-2 mt-0.5 flex-shrink-0"></i>
                                            <span>Pesanan Anda dilindungi kebijakan sekolah</span>
                                        </p>
                                        <p class="flex items-start">
                                            <i class="fas fa-clock mr-2 mt-0.5 flex-shrink-0"></i>
                                            <span>Estimasi siap: Segera setelah konfirmasi</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden inputs for payment calculation -->
                <input type="hidden" name="balance_used" id="balance-used" value="0">
                <input type="hidden" name="cash_amount" id="cash-amount-input" value="0">
                <input type="hidden" name="total_amount" id="total-amount-input" value="{{ $initialTotal }}">
            </form>
        </div>
    </div>

    <!-- Product Template (Hidden) for manual selection -->
    <div id="product-item-template" class="hidden">
        <div class="product-item flex flex-col sm:flex-row sm:items-start space-y-3 sm:space-y-0 sm:space-x-4 p-4 border border-gray-200 rounded-lg">
            <div class="flex-shrink-0 mx-auto sm:mx-0">
                <img src="" alt="" class="product-image w-20 h-20 object-cover rounded-lg">
            </div>
            
            <div class="flex-grow">
                <div class="mb-2">
                    <select name="items[INDEX][product_id]" class="product-select w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" required>
                        <option value="">Pilih Produk</option>
                    </select>
                </div>
                
                <div class="product-info hidden">
                    <h4 class="product-name font-semibold text-gray-800 text-sm md:text-base"></h4>
                    <p class="product-price text-gray-600 mt-1 text-sm"></p>
                    <p class="product-stock text-xs md:text-sm text-gray-500 mt-1"></p>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                            <div class="flex items-center border rounded-lg mx-auto sm:mx-0">
                                <button type="button" class="decrease-qty px-3 py-2 text-gray-600 hover:bg-gray-100 active:bg-gray-200 rounded-l-lg">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" name="items[INDEX][quantity]" value="1" min="1" 
                                       class="quantity-input w-16 md:w-20 px-2 md:px-3 py-2 text-center border-0 focus:ring-0 text-sm" required>
                                <button type="button" class="increase-qty px-3 py-2 text-gray-600 hover:bg-gray-100 active:bg-gray-200 rounded-r-lg">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <span class="max-qty text-xs md:text-sm text-gray-500 text-center sm:text-left"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center sm:text-right">
                <button type="button" class="remove-product text-red-600 hover:text-red-800 active:text-red-900 mb-2 text-sm">
                    <i class="fas fa-trash"></i> Hapus
                </button>
                <p class="item-total font-semibold text-gray-800 text-sm md:text-base">Rp 0</p>
            </div>
        </div>
    </div>

    <script>
    let productIndex = 0;
    let productsData = @json($products);
    const userBalance = {{ $userBalance }};
    
    @if(request('product_id') && $selectedProduct)
    const selectedProductData = @json($selectedProduct);
    @endif
    
    document.addEventListener('DOMContentLoaded', function() {
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        
        // Payment method selection
        paymentMethods.forEach(method => {
            method.addEventListener('change', function() {
                updatePaymentBreakdown();
            });
        });
        
        // Initial payment calculation
        updatePaymentBreakdown();
        
        @if(!request('product_id'))
        // Manual product selection setup
        setupManualSelection();
        @endif
    });

    @if(request('product_id') && $selectedProduct)
    function increaseQuantity() {
        const input = document.getElementById('product-quantity');
        const max = {{ $selectedProduct->stock }};
        const current = parseInt(input.value);
        
        if (current < max) {
            input.value = current + 1;
            updateQuantity();
        }
    }

    function decreaseQuantity() {
        const input = document.getElementById('product-quantity');
        const current = parseInt(input.value);
        
        if (current > 1) {
            input.value = current - 1;
            updateQuantity();
        }
    }

    function updateQuantity() {
        const quantity = parseInt(document.getElementById('product-quantity').value);
        const unitPrice = {{ $selectedProduct->price }};
        const total = quantity * unitPrice;
        
        // Update hidden input
        document.getElementById('selected-quantity').value = quantity;
        
        // Update displays
        document.getElementById('item-total').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        document.getElementById('total-items').textContent = quantity;
        document.getElementById('order-subtotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        document.getElementById('order-total').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        document.getElementById('total-amount-input').value = total;
        
        updatePaymentBreakdown();
    }
    @endif

    function updatePaymentBreakdown() {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        const totalAmount = parseFloat(document.getElementById('total-amount-input').value) || 0;
        const paymentBreakdown = document.getElementById('payment-breakdown');
        const insufficientWarning = document.getElementById('insufficient-balance-warning');
        const createBtn = document.getElementById('create-order-btn');
        
        // Hide all warnings and breakdowns initially
        paymentBreakdown.classList.add('hidden');
        insufficientWarning.classList.add('hidden');
        createBtn.disabled = false;
        createBtn.classList.remove('bg-gray-400');
        createBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        
        if (!selectedPayment || totalAmount === 0) {
            return;
        }
        
        let balanceUsed = 0;
        let cashAmount = 0;
        
        switch (selectedPayment.value) {
            case 'balance':
                if (userBalance >= totalAmount) {
                    balanceUsed = totalAmount;
                    cashAmount = 0;
                } else {
                    // Insufficient balance
                    insufficientWarning.classList.remove('hidden');
                    createBtn.disabled = true;
                    createBtn.classList.add('bg-gray-400');
                    createBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                }
                break;
                
            case 'cash':
                balanceUsed = 0;
                cashAmount = totalAmount;
                break;
                
            case 'mixed':
                balanceUsed = Math.min(userBalance, totalAmount);
                cashAmount = Math.max(0, totalAmount - userBalance);
                
                // Show breakdown
                paymentBreakdown.classList.remove('hidden');
                document.getElementById('balance-amount').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(balanceUsed);
                document.getElementById('cash-amount').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(cashAmount);
                document.getElementById('total-amount').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalAmount);
                break;
        }
        
        // Update hidden inputs
        document.getElementById('balance-used').value = balanceUsed;
        document.getElementById('cash-amount-input').value = cashAmount;
    }

    function setupManualSelection() {
        const adminSelect = document.getElementById('admin_id');
        const addProductBtn = document.getElementById('add-product-btn');
        const selectedProducts = document.getElementById('selected-products');
        
        adminSelect.addEventListener('change', function() {
            const adminId = this.value;
            selectedProducts.innerHTML = '';
            productIndex = 0;
            updateOrderSummary();
            
            if (adminId) {
                addProductBtn.style.display = 'block';
                addProductItem();
            } else {
                addProductBtn.style.display = 'none';
            }
        });
        
        addProductBtn.addEventListener('click', function() {
            addProductItem();
        });
    }

    function addProductItem() {
        const adminId = document.getElementById('admin_id').value;
        if (!adminId) return;
        
        const template = document.getElementById('product-item-template');
        const clone = template.cloneNode(true);
        
        clone.id = '';
        clone.classList.remove('hidden');
        
        // Update indices
        const inputs = clone.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.name) {
                input.name = input.name.replace('INDEX', productIndex);
            }
        });
        
        // Populate product options
        const productSelect = clone.querySelector('.product-select');
        const adminProducts = productsData.filter(p => p.admin_id === adminId);
        
        adminProducts.forEach(product => {
            const option = document.createElement('option');
            option.value = product.product_id;
            option.textContent = product.name;
            option.dataset.price = product.price;
            option.dataset.stock = product.stock;
            option.dataset.image = product.image;
            option.dataset.name = product.name;
            productSelect.appendChild(option);
        });
        
        setupProductItemListeners(clone);
        document.getElementById('selected-products').appendChild(clone);
        productIndex++;
    }

    function setupProductItemListeners(item) {
        const productSelect = item.querySelector('.product-select');
        const productInfo = item.querySelector('.product-info');
        const quantityInput = item.querySelector('.quantity-input');
        const removeBtn = item.querySelector('.remove-product');
        const increaseBtn = item.querySelector('.increase-qty');
        const decreaseBtn = item.querySelector('.decrease-qty');
        
        productSelect.addEventListener('change', function() {
            if (this.value) {
                const option = this.options[this.selectedIndex];
                const price = parseFloat(option.dataset.price);
                const stock = parseInt(option.dataset.stock);
                const image = option.dataset.image;
                const name = option.dataset.name;
                
                item.querySelector('.product-name').textContent = name;
                item.querySelector('.product-price').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
                item.querySelector('.product-stock').textContent = 'Stok tersedia: ' + stock;
                item.querySelector('.max-qty').textContent = 'Maksimal ' + stock + ' unit';
                
                const imgSrc = image ? `/storage/${image}` : 'https://via.placeholder.com/80x80?text=No+Image';
                item.querySelector('.product-image').src = imgSrc;
                item.querySelector('.product-image').alt = name;
                
                quantityInput.max = stock;
                if (parseInt(quantityInput.value) > stock) {
                    quantityInput.value = stock;
                }
                
                productInfo.classList.remove('hidden');
                updateItemTotal(item);
            } else {
                productInfo.classList.add('hidden');
                updateItemTotal(item);
            }
            updateOrderSummary();
        });
        
        quantityInput.addEventListener('change', function() {
            updateItemTotal(item);
            updateOrderSummary();
        });
        
        increaseBtn.addEventListener('click', function() {
            const max = parseInt(quantityInput.max);
            const current = parseInt(quantityInput.value);
            if (current < max) {
                quantityInput.value = current + 1;
                updateItemTotal(item);
                updateOrderSummary();
            }
        });
        
        decreaseBtn.addEventListener('click', function() {
            const current = parseInt(quantityInput.value);
            if (current > 1) {
                quantityInput.value = current - 1;
                updateItemTotal(item);
                updateOrderSummary();
            }
        });
        
        removeBtn.addEventListener('click', function() {
            item.remove();
            updateOrderSummary();
        });
    }

    function updateItemTotal(item) {
        const productSelect = item.querySelector('.product-select');
        const quantityInput = item.querySelector('.quantity-input');
        const itemTotal = item.querySelector('.item-total');
        
        if (productSelect.value) {
            const option = productSelect.options[productSelect.selectedIndex];
            const price = parseFloat(option.dataset.price);
            const quantity = parseInt(quantityInput.value) || 0;
            const total = price * quantity;
            
            itemTotal.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
        } else {
            itemTotal.textContent = 'Rp 0';
        }
    }

    function updateOrderSummary() {
        let totalPrice = 0;
        let totalItems = 0;
        
        @if(request('product_id') && $selectedProduct)
        // For selected product from product page
        const quantity = parseInt(document.getElementById('product-quantity').value) || 0;
        const unitPrice = {{ $selectedProduct->price }};
        totalPrice = unitPrice * quantity;
        totalItems = quantity;
        @else
        // For manual product selection
        const productItems = document.querySelectorAll('.product-item');
        productItems.forEach(item => {
            const productSelect = item.querySelector('.product-select');
            const quantityInput = item.querySelector('.quantity-input');
            
            if (productSelect.value) {
                const option = productSelect.options[productSelect.selectedIndex];
                const price = parseFloat(option.dataset.price);
                const quantity = parseInt(quantityInput.value) || 0;
                
                totalPrice += price * quantity;
                totalItems += quantity;
            }
        });
        @endif
        
        document.getElementById('total-items').textContent = totalItems;
        document.getElementById('order-subtotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalPrice);
        document.getElementById('order-total').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalPrice);
        document.getElementById('total-amount-input').value = totalPrice;
        
        // Enable/disable create order button
        const createBtn = document.getElementById('create-order-btn');
        if (totalItems === 0) {
            createBtn.disabled = true;
            createBtn.classList.add('bg-gray-400');
            createBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        } else {
            createBtn.disabled = false;
            createBtn.classList.remove('bg-gray-400');
            createBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }
        
        updatePaymentBreakdown();
    }

    // Form validation
    document.getElementById('order-form').addEventListener('submit', function(e) {
        // Check payment method
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
        if (!selectedPayment) {
            alert('Mohon pilih metode pembayaran');
            e.preventDefault();
            return;
        }
        
        // Check if has products
        const totalAmount = parseFloat(document.getElementById('total-amount-input').value) || 0;
        if (totalAmount === 0) {
            alert('Mohon pilih minimal satu produk');
            e.preventDefault();
            return;
        }
    });
    </script>
</x-app-layout>