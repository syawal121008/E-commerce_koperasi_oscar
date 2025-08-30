{{-- resources/views/carts/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <i class="fas fa-shopping-cart mr-2"></i>Keranjang Belanja
        </h2>
    </x-slot>

    <div class="container mx-auto px-4 py-6">
        <div class="max-w-7xl mx-auto">
            <!-- Cart Container -->
            <div id="cart-container">
                <!-- Cart items will be populated by JavaScript -->
            </div>

            <!-- Empty Cart State -->
            <div id="empty-cart" class="text-center bg-white rounded-lg shadow-sm p-16 hidden">
                <div class="mb-8">
                    <i class="fas fa-shopping-cart text-6xl text-gray-300"></i>
                </div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Keranjang Belanja Kosong</h2>
                <p class="text-gray-600 mb-8">Belum ada produk yang ditambahkan ke keranjang Anda.</p>
                <a href="{{ route('shop.index') }}" 
                   class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg inline-flex items-center transition-colors">
                    <i class="fas fa-shopping-bag mr-2"></i>Mulai Belanja
                </a>
            </div>

            <!-- Bottom Checkout Bar -->
            <div id="checkout-bar" class="fixed bottom-0 left-0 right-0 bg-white border-t shadow-lg z-40 hidden">
                <div class="container mx-auto px-4">
                    <div class="max-w-7xl mx-auto">
                        <div class="flex items-center justify-between py-4">
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" id="select-all-bottom" class="form-checkbox h-5 w-5 text-blue-500 rounded border-gray-300 focus:ring-blue-500 focus:ring-2">
                                    <span class="ml-2 text-gray-700">Pilih Semua</span>
                                </label>
                                <span class="text-gray-300">|</span>
                                <button type="button" id="delete-selected-bottom" class="text-red-500 hover:text-red-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                    Hapus (<span id="selected-count-bottom">0</span>)
                                </button>
                            </div>

                            <div class="flex items-center space-x-6">
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">Total (<span id="selected-items-count">0</span> item):</div>
                                    <div class="text-xl font-bold text-blue-500" id="total-price">Rp 0</div>
                                </div>
                                <button type="button" 
                                        id="checkout-btn" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-8 py-3 rounded-lg disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                                        disabled>
                                    Checkout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Spacing for fixed bottom bar -->
            <div class="h-20"></div>
        </div>
    </div>

    <!-- Checkout Modal -->
    <div id="checkoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Pilih Metode Pembayaran</h3>
                    <button type="button" id="closeModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="checkoutForm" method="POST">
                    @csrf
                    <div id="selectedItemsContainer"></div>
                    
                    <div class="mb-6">
                        <div class="space-y-3">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="payment_method" value="balance" class="mr-3 text-blue-500">
                                <div class="flex-grow">
                                    <div class="font-medium flex items-center">
                                        <i class="fas fa-wallet text-blue-500 mr-2"></i>
                                        Saldo
                                    </div>
                                    <div class="text-sm text-gray-500">Rp {{ number_format(auth()->user()->balance ?? 0, 0, ',', '.') }}</div>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="payment_method" value="cash" class="mr-3 text-blue-500">
                                <div class="font-medium flex items-center">
                                    <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                                    Tunai
                                </div>
                            </label>
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="payment_method" value="mixed" class="mr-3 text-blue-500">
                                <div class="font-medium flex items-center">
                                    <i class="fas fa-coins text-purple-500 mr-2"></i>
                                    Campuran
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Mixed Payment Details -->
                    <div id="mixedPaymentDetails" class="mb-6 hidden">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Gunakan Saldo</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                    <input type="number" name="balance_used" id="balanceUsed" 
                                           class="w-full border border-gray-300 rounded-lg px-8 py-2 focus:ring-blue-500 focus:border-blue-500"
                                           min="0" max="{{ auth()->user()->balance ?? 0 }}">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Bayar Tunai</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                    <input type="number" name="cash_amount" id="cashAmount" 
                                           class="w-full border border-gray-300 rounded-lg px-8 py-2 bg-gray-50" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-between items-center mb-4 p-4 bg-gray-50 rounded-lg">
                        <span class="font-medium">Total Pembayaran:</span>
                        <span class="font-bold text-blue-500 text-lg" id="modalTotalPrice">Rp 0</span>
                    </div>

                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 rounded-lg transition-colors">
                        <i class="fas fa-credit-card mr-2"></i>Konfirmasi Pembayaran
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- paid/Error Alert -->
    <div id="alertContainer" class="fixed top-4 right-4 z-50"></div>

    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .quantity-controls {
            display: flex;
            border: 1px solid #e5e7eb;
            border-radius: 0.375rem;
            overflow: hidden;
        }
        
        .quantity-controls button {
            border: none;
            background: white;
            padding: 0.5rem 0.75rem;
            color: #6b7280;
            transition: all 0.2s;
        }
        
        .quantity-controls button:hover:not(:disabled) {
            background: #f9fafb;
            color: #374151;
        }
        
        .quantity-controls button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .quantity-controls button:first-child {
            border-right: 1px solid #e5e7eb;
        }
        
        .quantity-controls button:last-child {
            border-left: 1px solid #e5e7eb;
        }

        .form-checkbox:checked {
            background-color: rgb(59, 130, 246);
            border-color: rgb(59, 130, 246);
        }

        .form-checkbox:focus {
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            border-color: rgb(59, 130, 246);
        }

        .cart-item {
            transition: all 0.3s ease;
        }

        .cart-item:hover {
            background-color: #eff6ff;
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .shop-header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }

        .product-card {
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }

        .product-card.selected {
            border-left-color: rgb(59, 130, 246);
            background-color: #eff6ff;
        }

        .price-text {
            color: #3b82f6;
            font-weight: 600;
        }
    </style>

    <script>
        // ============ SHOPEE-STYLE CART SYSTEM ============
        
        // Cart Management Class
        class ShopeeCart {
            constructor() {
                this.items = [];
                this.userBalance = {{ auth()->user()->balance ?? 0 }};
                this.initializeCart();
            }

            async initializeCart() {
                try {
                    await this.fetchCartItems();
                    this.updateCartDisplay();
                } catch (error) {
                    console.error('Error initializing cart:', error);
                    this.showAlert('error', 'Gagal memuat keranjang');
                }
            }

            // Fetch cart items from server
            async fetchCartItems() {
                try {
                    const response = await fetch('/carts', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    if (data.paid) {
                        this.items = data.data.map(item => ({
                            cart_id: item.cart_id,
                            product_id: item.product_id,
                            name: item.product.name,
                            image: item.product.image,
                            unit_price: parseFloat(item.unit_price),
                            quantity: parseInt(item.quantity),
                            subtotal: parseFloat(item.subtotal),
                            stock: item.product.stock,
                            admin_id: item.admin_id,
                            admin_name: item.product.admin ? item.product.admin.full_name : 'Toko Tidak Diketahui',
                            selected: false
                        }));
                    }
                } catch (error) {
                    console.error('Fetch error:', error);
                    this.items = [];
                }
            }

            // Update cart display
            updateCartDisplay() {
                const cartContainer = document.getElementById('cart-container');
                const emptyCart = document.getElementById('empty-cart');
                const checkoutBar = document.getElementById('checkout-bar');

                if (this.items.length === 0) {
                    cartContainer.innerHTML = '';
                    emptyCart.classList.remove('hidden');
                    checkoutBar.classList.add('hidden');
                } else {
                    emptyCart.classList.add('hidden');
                    checkoutBar.classList.remove('hidden');
                    this.renderCartItems();
                }

                this.updateTotals();
            }

            // Render cart items
            renderCartItems() {
                const cartContainer = document.getElementById('cart-container');
                const groupedItems = this.groupItemsByAdmin();

                let html = '<div class="space-y-4">';
                
                Object.entries(groupedItems).forEach(([adminId, items]) => {
                    const adminName = items[0].admin_name || 'Toko Tidak Diketahui';
                    
                    html += `
                        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                            <!-- Shop Header -->
                            <div class="shop-header p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <input type="checkbox" class="shop-checkbox form-checkbox h-5 w-5 text-white border-white rounded focus:ring-white focus:ring-2" data-admin-id="${adminId}">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-store text-white"></i>
                                            <span class="font-medium text-white">${adminName}</span>
                                            <i class="fas fa-chevron-right text-xs text-blue-200"></i>
                                        </div>
                                    </div>
                                    <div class="text-blue-200 text-sm">
                                        <i class="fas fa-shopping-bag mr-1"></i>
                                        ${items.length} produk
                                    </div>
                                </div>
                            </div>

                            <!-- Shop Items -->
                            <div class="divide-y divide-gray-100">
                    `;

                    items.forEach((item, index) => {
                        html += this.renderCartItem(item, index === items.length - 1);
                    });

                    html += `
                            </div>
                        </div>
                    `;
                });

                // Main Controls
                html = `
                    <div class="bg-white rounded-lg shadow-sm border mb-4">
                        <div class="p-4 border-b bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" id="select-all" class="form-checkbox h-5 w-5 text-blue-500 rounded border-gray-300 focus:ring-blue-500 focus:ring-2">
                                        <span class="ml-3 text-gray-700 font-medium">Pilih Semua</span>
                                    </label>
                                    <span class="text-gray-300">|</span>
                                    <button type="button" id="delete-selected" class="text-red-500 hover:text-red-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                                        <i class="fas fa-trash mr-1"></i>Hapus
                                    </button>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <span id="selected-count">0</span> item dipilih
                                </div>
                            </div>
                        </div>
                    </div>
                ` + html + '</div>';

                cartContainer.innerHTML = html;
                this.addEventListeners();
            }

            // Render single cart item
            renderCartItem(item, isLast = false) {
                const imageUrl = item.image ? 
                    `/storage/${item.image}` : 
                    'https://via.placeholder.com/120x120?text=No+Image';

                return `
                    <div class="cart-item product-card p-4 ${item.selected ? 'selected' : ''} ${isLast ? '' : 'border-b border-gray-100'}" 
                         data-item-id="${item.cart_id}" 
                         data-price="${item.unit_price}" 
                         data-stock="${item.stock}">
                        <div class="flex items-start space-x-4">
                            <!-- Checkbox -->
                            <div class="flex items-center pt-2">
                                <input type="checkbox" 
                                       class="item-checkbox form-checkbox h-5 w-5 text-blue-500 rounded border-gray-300 focus:ring-blue-500 focus:ring-2" 
                                       data-item-id="${item.cart_id}"
                                       data-admin-id="${item.admin_id}"
                                       data-price="${item.subtotal}"
                                       ${item.selected ? 'checked' : ''}>
                            </div>

                            <!-- Product Image -->
                            <div class="flex-shrink-0">
                                <img src="${imageUrl}" 
                                     alt="${item.name}" 
                                     class="w-20 h-20 object-cover rounded-lg border-2 border-gray-200"
                                     onerror="this.src='https://via.placeholder.com/120x120?text=No+Image'">
                            </div>

                            <!-- Product Details -->
                            <div class="flex-grow">
                                <div class="flex justify-between items-start">
                                    <div class="flex-grow pr-4">
                                        <h3 class="text-gray-800 font-medium text-base line-clamp-2 mb-2">${item.name}</h3>
                                        <div class="flex items-center space-x-2 mb-3">
                                            <span class="price-text text-lg font-bold">
                                                Rp ${this.formatCurrency(item.unit_price)}
                                            </span>
                                        </div>
                                        <div class="flex items-center text-xs text-gray-500 space-x-4">
                                            <span><i class="fas fa-box mr-1"></i>Stok: ${item.stock}</span>
                                        </div>
                                    </div>

                                    <!-- Delete Button -->
                                    <button type="button" 
                                            class="text-gray-400 hover:text-red-500 p-2 delete-item-btn rounded-full hover:bg-red-50 transition-colors"
                                            data-item-id="${item.cart_id}"
                                            title="Hapus item">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>

                                <!-- Quantity and Subtotal -->
                                <div class="flex items-center justify-between mt-4">
                                    <div class="quantity-controls">
                                        <button type="button" 
                                                class="quantity-btn px-3 py-2 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" 
                                                data-action="decrease"
                                                data-item-id="${item.cart_id}"
                                                ${item.quantity <= 1 ? 'disabled' : ''}>
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <input type="text" 
                                               readonly 
                                               value="${item.quantity}" 
                                               class="quantity-display w-16 text-center border-0 bg-white focus:ring-0 text-gray-800 font-medium py-2"
                                               data-item-id="${item.cart_id}">
                                        <button type="button" 
                                                class="quantity-btn px-3 py-2 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed" 
                                                data-action="increase"
                                                data-item-id="${item.cart_id}"
                                                ${item.quantity >= item.stock ? 'disabled' : ''}>
                                            <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </div>

                                    <div class="text-right">
                                        <div class="price-text font-bold text-lg item-subtotal" data-item-id="${item.cart_id}">
                                            Rp ${this.formatCurrency(item.subtotal)}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            // Group items by admin
            groupItemsByAdmin() {
                const grouped = {};
                this.items.forEach(item => {
                    if (!grouped[item.admin_id]) {
                        grouped[item.admin_id] = [];
                    }
                    grouped[item.admin_id].push(item);
                });
                return grouped;
            }

            // Add event listeners
            addEventListeners() {
                // Item checkboxes
                document.querySelectorAll('.item-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', (e) => {
                        this.toggleSelection(e.target.dataset.itemId);
                        this.updateTotals();
                        this.updateUI();
                    });
                });

                // Shop checkboxes
                document.querySelectorAll('.shop-checkbox').forEach(checkbox => {
                    checkbox.addEventListener('change', (e) => {
                        const adminId = e.target.dataset.adminId;
                        const shopItems = this.items.filter(item => item.admin_id === adminId);
                        shopItems.forEach(item => {
                            item.selected = e.target.checked;
                        });
                        this.updateTotals();
                        this.updateCheckboxes();
                        this.updateUI();
                    });
                });

                // Select all checkboxes
                document.querySelectorAll('#select-all, #select-all-bottom').forEach(checkbox => {
                    checkbox.addEventListener('change', (e) => {
                        this.selectAll(e.target.checked);
                        this.updateTotals();
                        this.updateCheckboxes();
                        this.updateUI();
                    });
                });

                // Quantity buttons
                document.querySelectorAll('.quantity-btn').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const itemId = e.target.closest('button').dataset.itemId;
                        const action = e.target.closest('button').dataset.action;
                        this.updateQuantityByAction(itemId, action);
                    });
                });

                // Delete item buttons
                document.querySelectorAll('.delete-item-btn').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const itemId = e.target.closest('button').dataset.itemId;
                        this.deleteItem(itemId);
                    });
                });

                // Delete selected buttons
                document.querySelectorAll('#delete-selected, #delete-selected-bottom').forEach(button => {
                    button.addEventListener('click', () => {
                        this.deleteSelectedItems();
                    });
                });

                // Checkout button
                const checkoutBtn = document.getElementById('checkout-btn');
                if (checkoutBtn) {
                    checkoutBtn.addEventListener('click', () => {
                        this.showCheckoutModal();
                    });
                }
            }

            // Update quantity by action
            async updateQuantityByAction(itemId, action) {
                const item = this.items.find(item => item.cart_id === itemId);
                if (!item) return;

                const newQuantity = action === 'increase' ? item.quantity + 1 : item.quantity - 1;
                
                try {
                    await this.updateQuantity(itemId, action);
                    this.showAlert('paid', 'Keranjang berhasil diperbarui');
                } catch (error) {
                    this.showAlert('error', error.message);
                }
            }

            // Update item quantity on server
            async updateQuantity(cartId, action) {
                try {
                    const response = await fetch(`/carts/${cartId}`, {
                        method: 'PUT',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            action: action
                        })
                    });

                    const data = await response.json();
                    if (data.paid) {
                        if (data.data) {
                            // Update local item
                            const itemIndex = this.items.findIndex(item => item.cart_id === cartId);
                            if (itemIndex >= 0) {
                                this.items[itemIndex].quantity = data.data.quantity;
                                this.items[itemIndex].subtotal = data.data.subtotal;
                            }
                        } else {
                            // Item was deleted (quantity became 0)
                            this.items = this.items.filter(item => item.cart_id !== cartId);
                        }
                        this.updateCartDisplay();
                    } else {
                        throw new Error(data.message || 'Gagal memperbarui keranjang');
                    }
                } catch (error) {
                    throw new Error(error.message || 'Terjadi kesalahan saat memperbarui keranjang');
                }
            }

            // Toggle item selection
            toggleSelection(cartId) {
                const item = this.items.find(item => item.cart_id === cartId);
                if (item) {
                    item.selected = !item.selected;
                }
            }

            // Select/deselect all items
            selectAll(selected = true) {
                this.items.forEach(item => {
                    item.selected = selected;
                });
            }

            // Delete item
            async deleteItem(cartId) {
                if (!confirm('Hapus item ini dari keranjang?')) return;
                
                try {
                    const response = await fetch(`/carts/${cartId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();
                    if (data.paid) {
                        this.items = this.items.filter(item => item.cart_id !== cartId);
                        this.updateCartDisplay();
                        this.showAlert('paid', 'Item berhasil dihapus dari keranjang');
                    } else {
                        throw new Error(data.message || 'Gagal menghapus item');
                    }
                } catch (error) {
                    this.showAlert('error', error.message || 'Terjadi kesalahan saat menghapus item');
                }
            }

            // Delete selected items
            async deleteSelectedItems() {
                const selectedItems = this.getSelectedItems();
                if (selectedItems.length === 0) return;
                
                if (!confirm(`Hapus ${selectedItems.length} item yang dipilih?`)) return;
                
                try {
                    const cartIds = selectedItems.map(item => item.cart_id);
                    const response = await fetch('/carts/bulk-delete', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            cart_ids: cartIds.join(',')
                        })
                    });

                    const data = await response.json();
                    if (data.paid) {
                        this.items = this.items.filter(item => !item.selected);
                        this.updateCartDisplay();
                        this.showAlert('paid', 'Item terpilih berhasil dihapus');
                    } else {
                        throw new Error(data.message || 'Gagal menghapus item');
                    }
                } catch (error) {
                    this.showAlert('error', error.message || 'Terjadi kesalahan saat menghapus item');
                }
            }

            // Get selected items
            getSelectedItems() {
                return this.items.filter(item => item.selected);
            }

            // Get cart count
            getCount() {
                return this.items.reduce((total, item) => total + item.quantity, 0);
            }

            // Get selected items total
            getSelectedTotal() {
                return this.getSelectedItems().reduce((total, item) => total + item.subtotal, 0);
            }

            // Update totals and UI
            updateTotals() {
                const selectedItems = this.getSelectedItems();
                const selectedCount = selectedItems.length;
                const totalPrice = this.getSelectedTotal();

                // Update UI elements
                const elements = {
                    'selected-count': selectedCount,
                    'selected-count-bottom': selectedCount,
                    'selected-items-count': selectedCount,
                    'total-price': 'Rp ' + this.formatCurrency(totalPrice),
                    'modalTotalPrice': 'Rp ' + this.formatCurrency(totalPrice)
                };

                Object.entries(elements).forEach(([id, value]) => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.textContent = value;
                    }
                });

                // Enable/disable buttons
                const deleteButtons = document.querySelectorAll('#delete-selected, #delete-selected-bottom');
                const checkoutBtn = document.getElementById('checkout-btn');

                deleteButtons.forEach(btn => {
                    btn.disabled = selectedCount === 0;
                });

                if (checkoutBtn) {
                    checkoutBtn.disabled = selectedCount === 0;
                }

                this.updateCheckboxes();
            }

            // Update checkbox states
            updateCheckboxes() {
                const allItemCheckboxes = document.querySelectorAll('.item-checkbox');
                const selectAllCheckboxes = document.querySelectorAll('#select-all, #select-all-bottom');
                
                // Update item checkboxes
                allItemCheckboxes.forEach(checkbox => {
                    const item = this.items.find(item => item.cart_id === checkbox.dataset.itemId);
                    if (item) {
                        checkbox.checked = item.selected;
                    }
                });

                // Update select all checkboxes
                const selectedCount = this.getSelectedItems().length;
                selectAllCheckboxes.forEach(checkbox => {
                    checkbox.checked = allItemCheckboxes.length > 0 && selectedCount === allItemCheckboxes.length;
                    checkbox.indeterminate = selectedCount > 0 && selectedCount < allItemCheckboxes.length;
                });

                // Update shop checkboxes
                document.querySelectorAll('.shop-section, [data-admin-id]').forEach(section => {
                    const adminId = section.dataset.adminId;
                    if (!adminId) return;
                    
                    const shopCheckbox = section.querySelector('.shop-checkbox');
                    const shopItems = this.items.filter(item => item.admin_id === adminId);
                    const selectedInShop = shopItems.filter(item => item.selected);
                    
                    if (shopCheckbox && shopItems.length > 0) {
                        shopCheckbox.checked = selectedInShop.length === shopItems.length;
                        shopCheckbox.indeterminate = selectedInShop.length > 0 && selectedInShop.length < shopItems.length;
                    }
                });
            }

            // Update UI visual states
            updateUI() {
                document.querySelectorAll('.cart-item').forEach(cartItem => {
                    const itemId = cartItem.dataset.itemId;
                    const item = this.items.find(i => i.cart_id === itemId);
                    if (item) {
                        if (item.selected) {
                            cartItem.classList.add('selected');
                        } else {
                            cartItem.classList.remove('selected');
                        }
                    }
                });
            }

            // Update cart count in navigation
            updateCartCount() {
                const cartCount = document.getElementById('cart-count');
                if (cartCount) {
                    cartCount.textContent = this.getCount();
                }
            }

            // Show checkout modal
            showCheckoutModal() {
                const selectedItems = this.getSelectedItems();
                if (selectedItems.length === 0) {
                    this.showAlert('warning', 'Pilih item yang ingin dibeli');
                    return;
                }

                // Populate selected items in modal
                this.populateCheckoutModal(selectedItems);
                
                const modal = document.getElementById('checkoutModal');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            }

            // Populate checkout modal with selected items
            populateCheckoutModal(selectedItems) {
                const container = document.getElementById('selectedItemsContainer');
                if (container) {
                    // Fix: Send as proper array format
                    const cartIds = selectedItems.map(item => item.cart_id);
                    let html = `<input type="hidden" name="cart_ids" value='${JSON.stringify(cartIds)}'>`;
                    container.innerHTML = html;
                }
            }

            // Format currency
            formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID').format(amount);
            }

            // Show alert
            showAlert(type, message) {
                const alertContainer = document.getElementById('alertContainer');
                if (!alertContainer) return;

                const alertTypes = {
                    'paid': { 
                        icon: 'check-circle', 
                        bg: 'bg-green-100', 
                        border: 'border-green-300', 
                        text: 'text-green-800',
                        iconColor: 'text-green-500'
                    },
                    'error': { 
                        icon: 'exclamation-circle', 
                        bg: 'bg-red-100', 
                        border: 'border-red-300', 
                        text: 'text-red-800',
                        iconColor: 'text-red-500'
                    },
                    'warning': { 
                        icon: 'exclamation-triangle', 
                        bg: 'bg-yellow-100', 
                        border: 'border-yellow-300', 
                        text: 'text-yellow-800',
                        iconColor: 'text-yellow-500'
                    }
                };
                
                const config = alertTypes[type] || alertTypes['error'];
                
                const alert = document.createElement('div');
                alert.className = `${config.bg} ${config.border} ${config.text} px-4 py-3 rounded-lg border mb-2 shadow-lg fade-in max-w-sm`;
                alert.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-${config.icon} mr-2 ${config.iconColor}"></i>
                            <span class="text-sm font-medium">${message}</span>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" 
                                class="ml-4 text-lg font-bold hover:opacity-70 ${config.text}">×</button>
                    </div>
                `;
                
                alertContainer.appendChild(alert);
                
                setTimeout(() => {
                    if (alert.parentElement) alert.remove();
                }, 5000);
            }

            // Clear cart
            async clearCart() {
                if (!confirm('Hapus semua item dari keranjang?')) return;
                
                try {
                    const response = await fetch('/carts/clear', {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();
                    if (data.paid) {
                        this.items = [];
                        this.updateCartDisplay();
                        this.showAlert('paid', 'Keranjang berhasil dikosongkan');
                    } else {
                        throw new Error(data.message || 'Gagal mengosongkan keranjang');
                    }
                } catch (error) {
                    this.showAlert('error', error.message || 'Terjadi kesalahan');
                }
            }
        }

        // Initialize cart
        let cart;

        // DOM Content Loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Add meta csrf token if not exists
            if (!document.querySelector('meta[name="csrf-token"]')) {
                const meta = document.createElement('meta');
                meta.name = 'csrf-token';
                meta.content = '{{ csrf_token() }}';
                document.head.appendChild(meta);
            }

            // Initialize cart
            cart = new ShopeeCart();

            // Modal controls
            const closeModal = document.getElementById('closeModal');
            const checkoutModal = document.getElementById('checkoutModal');
            
            if (closeModal && checkoutModal) {
                closeModal.addEventListener('click', function() {
                    checkoutModal.classList.add('hidden');
                    checkoutModal.classList.remove('flex');
                });

                // Close modal when clicking outside
                checkoutModal.addEventListener('click', function(e) {
                    if (e.target === checkoutModal) {
                        checkoutModal.classList.add('hidden');
                        checkoutModal.classList.remove('flex');
                    }
                });
            }

            // Payment method change
            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', updatePaymentMethod);
            });

            // Mixed payment input
            const balanceUsed = document.getElementById('balanceUsed');
            if (balanceUsed) {
                balanceUsed.addEventListener('input', updateMixedPayment);
            }

            // Form submission
            const checkoutForm = document.getElementById('checkoutForm');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
                    
                    if (!paymentMethod) {
                        cart.showAlert('error', 'Pilih metode pembayaran');
                        return;
                    }

                    const selectedItems = cart.getSelectedItems();
                    const totalPrice = cart.getSelectedTotal();
                    const userBalance = {{ auth()->user()->balance ?? 0 }};

                    if (paymentMethod === 'balance' && totalPrice > userBalance) {
                        cart.showAlert('error', 'Saldo tidak mencukupi');
                        return;
                    }

                    if (paymentMethod === 'mixed') {
                        const balanceUsed = parseInt(document.getElementById('balanceUsed').value) || 0;
                        const cashAmount = parseInt(document.getElementById('cashAmount').value) || 0;
                        
                        if (balanceUsed + cashAmount !== totalPrice) {
                            cart.showAlert('error', 'Jumlah pembayaran tidak sesuai dengan total');
                            return;
                        }
                        
                        if (balanceUsed > userBalance) {
                            cart.showAlert('error', 'Saldo tidak mencukupi');
                            return;
                        }
                    }

                    // Disable form
                    const submitBtn = checkoutForm.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';

                    try {
                        // Prepare form data - FIX: Create proper FormData
                        const formData = new FormData();
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                        formData.append('payment_method', paymentMethod);
                        
                        // Fix: Send cart_ids as array
                        const cartIds = selectedItems.map(item => item.cart_id);
                        cartIds.forEach((id, index) => {
                            formData.append(`cart_ids[${index}]`, id);
                        });
                        
                        if (paymentMethod === 'mixed') {
                            formData.append('balance_used', document.getElementById('balanceUsed').value || '0');
                            formData.append('cash_amount', document.getElementById('cashAmount').value || '0');
                        }

                        // Submit order
                        const response = await fetch('/carts/checkout', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await response.json();
                        
                        if (data.paid) {
                            // Remove selected items from local cart
                            cart.items = cart.items.filter(item => !item.selected);
                            cart.updateCartDisplay();
                            
                            // Close modal
                            checkoutModal.classList.add('hidden');
                            checkoutModal.classList.remove('flex');
                            
                            // Show paid message
                            cart.showAlert('paid', data.message || 'Pesanan berhasil dibuat');
                            
                            // Redirect if needed
                            if (data.redirect) {
                                setTimeout(() => {
                                    window.location.href = data.redirect;
                                }, 2000);
                            } else {
                                // Redirect to orders page
                                setTimeout(() => {
                                    window.location.href = '{{ route("orders.index") }}';
                                }, 2000);
                            }
                        } else {
                            cart.showAlert('error', data.message || 'Terjadi kesalahan');
                        }
                    } catch (error) {
                        console.error('Checkout error:', error);
                        cart.showAlert('error', 'Terjadi kesalahan saat memproses pesanan');
                    } finally {
                        // Re-enable form
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-credit-card mr-2"></i>Konfirmasi Pembayaran';
                    }
                });
            }

            // Show session messages
            @if(session('paid'))
                setTimeout(() => {
                    cart?.showAlert('paid', '{{ session('paid') }}');
                }, 100);
            @endif

            @if(session('error'))
                setTimeout(() => {
                    cart?.showAlert('error', '{{ session('error') }}');
                }, 100);
            @endif

            @if($errors->any())
                @foreach($errors->all() as $error)
                    setTimeout(() => {
                        cart?.showAlert('error', '{{ $error }}');
                    }, 100);
                @endforeach
            @endif
        });

        // Update payment method
        function updatePaymentMethod() {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            const mixedDetails = document.getElementById('mixedPaymentDetails');

            if (paymentMethod === 'mixed') {
                mixedDetails.classList.remove('hidden');
                const totalPrice = cart?.getSelectedTotal() || 0;
                const userBalance = {{ auth()->user()->balance ?? 0 }};
                document.getElementById('balanceUsed').value = Math.min(userBalance, totalPrice);
                updateMixedPayment();
            } else {
                mixedDetails.classList.add('hidden');
            }
        }

        // Update mixed payment
        function updateMixedPayment() {
            const balanceUsed = parseInt(document.getElementById('balanceUsed').value) || 0;
            const totalPrice = cart?.getSelectedTotal() || 0;
            const cashAmount = Math.max(0, totalPrice - balanceUsed);
            document.getElementById('cashAmount').value = cashAmount;
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + A to select all
            if ((e.ctrlKey || e.metaKey) && e.key === 'a' && cart) {
                e.preventDefault();
                const selectAllCheckbox = document.getElementById('select-all');
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = !selectAllCheckbox.checked;
                    selectAllCheckbox.dispatchEvent(new Event('change'));
                }
            }
            
            // Delete key to delete selected items
            if (e.key === 'Delete' && cart) {
                const selectedCount = cart.getSelectedItems().length;
                if (selectedCount > 0) {
                    cart.deleteSelectedItems();
                }
            }
        });
    </script>
</x-app-layout>