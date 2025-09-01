{{-- Mengambil data notifikasi --}}
@php
    $user = auth()->user();
    $userRole = $user?->role ?? '';

    // Inisialisasi variabel untuk menampung data notifikasi
    $newOrdersCount = 0;
    $newOrders = collect(); // Gunakan collection kosong sebagai default

    // Hanya jalankan query jika user adalah admin dan user terautentikasi
    if ($userRole === 'admin' && $user) {
        // Tentukan status yang ingin ditampilkan dalam notifikasi
        $statuses = ['pending', 'paid'];
        
        // Panggil helper dengan parameter status yang kita inginkan
        $newOrdersCount = \App\Helpers\NotificationHelper::getOrdersByStatusCount($user->user_id, $statuses);
        $newOrders = \App\Helpers\NotificationHelper::getOrdersByStatus($user->user_id, $statuses, 5); // Ambil 5 notifikasi terbaru
    }
@endphp

<nav x-data="{ 
    open: false, 
    reportOpen: false,
    userDropdownOpen: false,
    managementOpen: false,
    orderOpen: false
}" class="bg-white/70 backdrop-blur-md border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 lg:space-x-3">
                        <x-application-logo class="block h-8 w-8 lg:h-10 lg:w-10 fill-current text-gray-600" />
                        <span class="text-lg lg:text-xl font-bold text-gray-800 hidden sm:block truncate">Koperasi SMKIUTAMA</span>
                    </a>
                </div>

                {{-- Desktop Navigation (lg and up) --}}
                <div class="hidden lg:flex lg:items-center lg:space-x-6 lg:ml-8">
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            Dashboard
                        </x-nav-link>

                        @if ($userRole === 'admin')
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                                Pengguna
                            </x-nav-link>
                            <x-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                                Kategori
                            </x-nav-link>
                            <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                                Produk
                            </x-nav-link>

                            {{-- Dropdown Pesanan untuk Admin --}}
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="relative inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:border-gray-300 transition-all duration-150 ease-in-out">
                                        <span class="pr-5">Pesanan</span>
                                        @if($newOrdersCount > 0)
                                            <span class="absolute -top-1 right-0 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold animate-pulse min-w-[20px]">
                                                {{ $newOrdersCount > 99 ? '99+' : $newOrdersCount }}
                                            </span>
                                        @endif
                                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('admin.pos')">
                                        Buat Pesanan (POS)
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('orders.index')">
                                        Daftar Pesanan
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                            
                            <x-nav-link :href="route('topup.scan')" :active="request()->routeIs('topup.scan')">
                                Isi Saldo
                            </x-nav-link>

                            {{-- Dropdown Laporan untuk Admin --}}
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:border-gray-300 transition-all duration-150 ease-in-out">
                                        Laporan
                                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('supervisor.topups')">
                                        Laporan Saldo
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('supervisor.transactions')">
                                        Laporan Transaksi
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('supervisor.profit')">
                                        Laporan Keuntungan
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                        
                        @elseif ($userRole === 'guru')
                            {{-- Dropdown Laporan untuk Guru --}}
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-600 hover:text-gray-800 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:border-gray-300 transition-all duration-150 ease-in-out">
                                        Laporan
                                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </x-slot>
                                <x-slot name="content">
                                    <x-dropdown-link :href="route('supervisor.topups')">
                                        Laporan Saldo
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('supervisor.transactions')">
                                        Laporan Transaksi
                                    </x-dropdown-link>
                                    <x-dropdown-link :href="route('supervisor.profit')">
                                        Laporan Keuntungan
                                    </x-dropdown-link>
                                </x-slot>
                            </x-dropdown>
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                                Pengguna
                            </x-nav-link>
                        @else
                            <x-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.*')">
                                Belanja
                            </x-nav-link>
                            <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                                Riwayat
                            </x-nav-link>
                            <x-nav-link :href="route('carts.index')" :active="request()->routeIs('cart.*')">
                                Keranjang
                            </x-nav-link>
                            <x-nav-link :href="route('topup.index')" :active="request()->routeIs('topup.*')">
                                Isi Saldo
                            </x-nav-link>
                        @endif
                    @endauth
                </div>

                {{-- Tablet Navigation (md to lg) --}}
                <div class="hidden md:flex lg:hidden md:items-center md:space-x-2 md:ml-4">
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-sm px-2 py-1">
                            Dashboard
                        </x-nav-link>

                        @if ($userRole === 'admin')
                            {{-- Compact Management Dropdown for tablets --}}
                            <div class="relative" x-data="{ managementOpen: false }">
                                <button @click="managementOpen = !managementOpen" 
                                        class="inline-flex items-center px-2 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-md transition-all">
                                    <span class="text-xs">Kelola</span>
                                    <svg class="ml-1 h-3 w-3 transform transition-transform" :class="{'rotate-180': managementOpen}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="managementOpen" 
                                     x-transition:enter="transition ease-out duration-200" 
                                     x-transition:enter-start="opacity-0 scale-95" 
                                     x-transition:enter-end="opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-75" 
                                     x-transition:leave-start="opacity-100 scale-100" 
                                     x-transition:leave-end="opacity-0 scale-95"
                                     @click.away="managementOpen = false"
                                     class="absolute top-full mt-2 w-40 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                    <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Pengguna</a>
                                    <a href="{{ route('categories.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Kategori</a>
                                    <a href="{{ route('products.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Produk</a>
                                    <a href="{{ route('topup.scan') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Isi Saldo</a>
                                </div>
                            </div>
                            
                            {{-- Compact Order Dropdown for tablets --}}
                            <div class="relative" x-data="{ orderOpen: false }">
                                <button @click="orderOpen = !orderOpen" 
                                        class="relative inline-flex items-center px-2 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-md transition-all">
                                    <span class="text-xs pr-4">Pesanan</span>
                                    @if($newOrdersCount > 0)
                                        <span class="absolute -top-1 right-0 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center font-bold animate-pulse min-w-[16px]">
                                            {{ $newOrdersCount > 9 ? '9+' : $newOrdersCount }}
                                        </span>
                                    @endif
                                    <svg class="ml-1 h-3 w-3 transform transition-transform" :class="{'rotate-180': orderOpen}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="orderOpen" 
                                     x-transition:enter="transition ease-out duration-200" 
                                     x-transition:enter-start="opacity-0 scale-95" 
                                     x-transition:enter-end="opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-75" 
                                     x-transition:leave-start="opacity-100 scale-100" 
                                     x-transition:leave-end="opacity-0 scale-95"
                                     @click.away="orderOpen = false"
                                     class="absolute top-full mt-2 w-44 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                    <a href="{{ route('admin.pos') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Buat Pesanan (POS)</a>
                                    <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Daftar Pesanan</a>
                                </div>
                            </div>

                            {{-- Compact Report Dropdown for tablets --}}
                            <div class="relative" x-data="{ reportOpen: false }">
                                <button @click="reportOpen = !reportOpen" 
                                        class="inline-flex items-center px-2 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-md transition-all">
                                    <span class="text-xs">Laporan</span>
                                    <svg class="ml-1 h-3 w-3 transform transition-transform" :class="{'rotate-180': reportOpen}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="reportOpen" 
                                     x-transition:enter="transition ease-out duration-200" 
                                     x-transition:enter-start="opacity-0 scale-95" 
                                     x-transition:enter-end="opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-75" 
                                     x-transition:leave-start="opacity-100 scale-100" 
                                     x-transition:leave-end="opacity-0 scale-95"
                                     @click.away="reportOpen = false"
                                     class="absolute top-full mt-2 w-44 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                    <a href="{{ route('supervisor.topups') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Laporan Saldo</a>
                                    <a href="{{ route('supervisor.transactions') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Laporan Transaksi</a>
                                    <a href="{{ route('supervisor.profit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Laporan Keuntungan</a>
                                </div>
                            </div>

                        @elseif ($userRole === 'guru')
                            {{-- Compact Report Dropdown for Guru on tablets --}}
                            <div class="relative" x-data="{ reportOpen: false }">
                                <button @click="reportOpen = !reportOpen" 
                                        class="inline-flex items-center px-2 py-1 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 rounded-md transition-all">
                                    <span class="text-xs">Laporan</span>
                                    <svg class="ml-1 h-3 w-3 transform transition-transform" :class="{'rotate-180': reportOpen}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="reportOpen" 
                                     x-transition:enter="transition ease-out duration-200" 
                                     x-transition:enter-start="opacity-0 scale-95" 
                                     x-transition:enter-end="opacity-100 scale-100" 
                                     x-transition:leave="transition ease-in duration-75" 
                                     x-transition:leave-start="opacity-100 scale-100" 
                                     x-transition:leave-end="opacity-0 scale-95"
                                     @click.away="reportOpen = false"
                                     class="absolute top-full mt-2 w-44 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                                    <a href="{{ route('supervisor.topups') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Laporan Saldo</a>
                                    <a href="{{ route('supervisor.transactions') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Laporan Transaksi</a>
                                    <a href="{{ route('supervisor.profit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Laporan Keuntungan</a>
                                </div>
                            </div>
                            <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" class="text-sm px-2 py-1">
                                Pengguna
                            </x-nav-link>
                        @else
                            <x-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.*')" class="text-sm px-2 py-1">
                                Belanja
                            </x-nav-link>
                            <x-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')" class="text-sm px-2 py-1">
                                Riwayat
                            </x-nav-link>
                            <x-nav-link :href="route('carts.index')" :active="request()->routeIs('cart.*')" class="text-sm px-2 py-1">
                                Keranjang
                            </x-nav-link>
                            <x-nav-link :href="route('topup.index')" :active="request()->routeIs('topup.*')" class="text-sm px-2 py-1">
                                Saldo
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            {{-- User Dropdown & Notifications (Desktop & Tablet) --}}
            <div class="hidden md:flex md:items-center md:space-x-2">
                @auth
                    {{-- PERBAIKAN: Notification Dropdown untuk Admin dengan props yang tepat --}}
                    @if($userRole === 'admin')
                        <x-notification-dropdown :orders="$newOrders" :count="$newOrdersCount" />
                    @endif

                    <div class="relative" x-data="{ userDropdownOpen: false }">
                        <button @click="userDropdownOpen = !userDropdownOpen" class="inline-flex items-center pl-1 pr-3 py-1 border border-transparent text-sm font-medium rounded-full text-gray-600 bg-white/50 hover:text-gray-800 hover:bg-white/70 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">

                        {{-- Foto Profil Pengguna --}}
                        <img class="w-8 h-8 rounded-full object-cover" 
                            src="{{ Auth::user()->profilePhotoUrl() }}" 
                            alt="Foto Profil"
                            onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->full_name) }}&background=e5e7eb&color=374151&size=32'; this.onerror=null;">

                        {{-- Nama Pengguna (tampil di layar besar) --}}
                        <div class="hidden lg:block ml-2 truncate">{{ Auth::user()->full_name }}</div>

                        {{-- Ikon Panah Dropdown --}}
                        <svg class="ml-2 h-4 w-4 transform transition-transform" :class="{'rotate-180': userDropdownOpen}" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                        <div x-show="userDropdownOpen" 
                             x-transition:enter="transition ease-out duration-200" 
                             x-transition:enter-start="opacity-0 scale-95" 
                             x-transition:enter-end="opacity-100 scale-100" 
                             x-transition:leave="transition ease-in duration-75" 
                             x-transition:leave-start="opacity-100 scale-100" 
                             x-transition:leave-end="opacity-0 scale-95"
                             @click.away="userDropdownOpen = false"
                             class="absolute right-0 top-full mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-200">
                            @if ($userRole === 'customer')
                                <a href="{{ route('transactions.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Rekap Transaksi</a>
                            @elseif ($userRole === 'guru')
                                <a href="{{ route('supervisor.transactions') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Rekap Transaksi</a>
                            @elseif ($userRole === 'admin')
                                <a href="{{ route('admin.transactions') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Semua Transaksi</a>
                            @endif
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Akun Saya</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Keluar</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900 px-3 py-2">Masuk</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md transition">Daftar</a>
                    @endif
                @endauth
            </div>

            {{-- Mobile Menu Button --}}
            <div class="flex items-center md:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div :class="{'block': open, 'hidden': !open}" class="hidden md:hidden bg-white/90 backdrop-blur-sm z-50">
        @auth
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-responsive-nav-link>

                @if ($userRole === 'admin')
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                        Pengguna
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('categories.index')" :active="request()->routeIs('categories.*')">
                        Kategori
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                        Produk
                    </x-responsive-nav-link>
                    
                    {{-- Dropdown Pesanan untuk Admin (Mobile) --}}
                    <div class="relative w-full" x-data="{ mobileOrderOpen: false }">
                        <button @click="mobileOrderOpen = !mobileOrderOpen" 
                                class="block w-full pl-3 pr-4 py-2 text-left text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out border-l-4 border-transparent">
                            <div class="flex items-center justify-between w-full">
                                <span class="flex items-center">
                                    <span>Pesanan</span>
                                    @if($newOrdersCount > 0)
                                        <span class="ml-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold flex-shrink-0 min-w-[20px]">
                                            {{ $newOrdersCount > 99 ? '99+' : $newOrdersCount }}
                                        </span>
                                    @endif
                                </span>
                                <svg class="h-4 w-4 transform transition-transform duration-200" :class="{'rotate-180': mobileOrderOpen}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                        <div x-show="mobileOrderOpen" x-collapse.duration.300ms class="mt-1 space-y-1 bg-gray-50/50">
                            <x-responsive-nav-link :href="route('admin.pos')" :active="request()->routeIs('admin.pos.*')">
                                <span class="ml-4">• Buat Pesanan (POS)</span>
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.index')">
                                <span class="ml-4">• Daftar Pesanan</span>
                            </x-responsive-nav-link>
                        </div>
                    </div>

                    <x-responsive-nav-link :href="route('topup.scan')" :active="request()->routeIs('topup.scan')">
                        Isi Saldo
                    </x-responsive-nav-link>
                    
                    {{-- Dropdown Laporan untuk Admin (Mobile) --}}
                    <div class="relative w-full" x-data="{ mobileReportOpen: false }">
                        <button @click="mobileReportOpen = !mobileReportOpen" 
                                class="block w-full pl-3 pr-4 py-2 text-left text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out border-l-4 border-transparent">
                            <div class="flex items-center justify-between w-full">
                                <span>Laporan</span>
                                <svg class="h-4 w-4 transform transition-transform duration-200" :class="{'rotate-180': mobileReportOpen}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                        <div x-show="mobileReportOpen" x-collapse.duration.300ms class="mt-1 space-y-1 bg-gray-50/50">
                            <x-responsive-nav-link :href="route('supervisor.topups')" :active="request()->routeIs('supervisor.topups')">
                                <span class="ml-4">• Laporan Saldo</span>
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('supervisor.transactions')" :active="request()->routeIs('supervisor.transactions')">
                                <span class="ml-4">• Laporan Transaksi</span>
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('supervisor.profit')" :active="request()->routeIs('supervisor.profit')">
                                <span class="ml-4">• Laporan Keuntungan</span>
                            </x-responsive-nav-link>
                        </div>
                    </div>
                @elseif ($userRole === 'guru')
                    {{-- Dropdown Laporan untuk Guru (Mobile) --}}
                    <div class="relative w-full" x-data="{ mobileReportOpen: false }">
                        <button @click="mobileReportOpen = !mobileReportOpen" 
                                class="block w-full pl-3 pr-4 py-2 text-left text-base font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out border-l-4 border-transparent">
                            <div class="flex items-center justify-between w-full">
                                <span>Laporan</span>
                                <svg class="h-4 w-4 transform transition-transform duration-200" :class="{'rotate-180': mobileReportOpen}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                        <div x-show="mobileReportOpen" x-collapse.duration.300ms class="mt-1 space-y-1 bg-gray-50/50">
                            <x-responsive-nav-link :href="route('supervisor.topups')" :active="request()->routeIs('supervisor.topups')">
                                <span class="ml-4">• Laporan Saldo</span>
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('supervisor.transactions')" :active="request()->routeIs('supervisor.transactions')">
                                <span class="ml-4">• Laporan Transaksi</span>
                            </x-responsive-nav-link>
                            <x-responsive-nav-link :href="route('supervisor.profit')" :active="request()->routeIs('supervisor.profit')">
                                <span class="ml-4">• Laporan Keuntungan</span>
                            </x-responsive-nav-link>
                        </div>
                    </div>
                    <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                        Pengguna
                    </x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.*')">
                        Belanja
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('orders.index')" :active="request()->routeIs('orders.*')">
                        Riwayat
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('carts.index')" :active="request()->routeIs('cart.*')">
                        Keranjang
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('topup.index')" :active="request()->routeIs('topup.*')">
                        Isi Saldo
                    </x-responsive-nav-link>
                @endif
            </div>

            {{-- Mobile User Info --}}
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                    <div class="font-medium text-xs text-blue-600 uppercase">{{ Auth::user()->getRoleName() ?? '' }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    @if ($userRole === 'customer')
                        <x-responsive-nav-link :href="route('transactions.index')">
                            Rekap Transaksi
                        </x-responsive-nav-link>
                    @elseif ($userRole === 'guru')
                        <x-responsive-nav-link :href="route('supervisor.transactions')">
                            Rekap Transaksi
                        </x-responsive-nav-link>
                    @elseif ($userRole === 'admin')
                        <x-responsive-nav-link :href="route('admin.transactions')">
                            Semua Transaksi
                        </x-responsive-nav-link>
                    @endif

                    <x-responsive-nav-link :href="route('profile.edit')">
                        Akun Saya
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                            Keluar
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            {{-- Mobile Guest Navigation --}}
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('login')">
                    Masuk
                </x-responsive-nav-link>
                @if (Route::has('register'))
                    <x-responsive-nav-link :href="route('register')">
                        Daftar
                    </x-responsive-nav-link>
                @endif
            </div>
        @endauth
    </div>
</nav>