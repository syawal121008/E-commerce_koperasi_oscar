<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Shop - Belanja Online Mudah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gradient-to-br from-blue-50 via-white to-purple-50 min-h-screen">
    <!-- Navigation -->
    <nav class="glass-effect shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                            E-Shop
                        </h1>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-blue-600 transition-colors duration-300">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 transition-colors duration-300">Login</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-primary">Register</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-6">
                    Belanja Online
                    <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                        Mudah & Aman
                    </span>
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Temukan produk berkualitas dengan harga terbaik. Bergabunglah dengan ribuan pelanggan yang puas berbelanja di E-Shop.
                </p>
                <div class="space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary text-lg px-8 py-4">
                            Mulai Belanja
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn-primary text-lg px-8 py-4">
                            Mulai Belanja
                        </a>
                        <a href="{{ route('login') }}" class="bg-white text-gray-800 font-semibold py-4 px-8 rounded-lg border border-gray-300 hover:bg-gray-50 transform transition-all duration-300 hover:scale-105 hover:shadow-lg">
                            Sudah Punya Akun?
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 bg-white/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Mengapa Pilih E-Shop?</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Kami memberikan pengalaman berbelanja online terbaik dengan berbagai keunggulan
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-8 rounded-2xl glass-effect hover:shadow-xl transform transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Pembayaran Aman</h3>
                    <p class="text-gray-600">Sistem pembayaran yang aman dan terpercaya untuk melindungi transaksi Anda</p>
                </div>
                
                <div class="text-center p-8 rounded-2xl glass-effect hover:shadow-xl transform transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-r from-green-500 to-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Pengiriman Cepat</h3>
                    <p class="text-gray-600">Barang sampai dengan cepat ke alamat Anda dengan layanan kurir terpercaya</p>
                </div>
                
                <div class="text-center p-8 rounded-2xl glass-effect hover:shadow-xl transform transition-all duration-300 hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Layanan 24/7</h3>
                    <p class="text-gray-600">Customer service siap membantu Anda kapan saja dan dimana saja</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <div class="glass-effect rounded-3xl p-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">
                    Siap Memulai Belanja?
                </h2>
                <p class="text-gray-600 mb-8 text-lg">
                    Bergabunglah dengan ribuan pelanggan yang sudah merasakan kemudahan berbelanja di E-Shop
                </p>
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-primary text-lg px-8 py-4">
                        Ke Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-primary text-lg px-8 py-4">
                        Daftar Sekarang
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h3 class="text-2xl font-bold mb-4">E-Shop</h3>
                <p class="text-gray-400 mb-6">Platform e-commerce terpercaya untuk kebutuhan belanja online Anda</p>
                <div class="border-t border-gray-800 pt-6">
                    <p class="text-gray-400">&copy; 2025 E-Shop. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>