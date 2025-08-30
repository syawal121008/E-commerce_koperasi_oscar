<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koperasi SMKIUTAMA</title>
        <link rel="icon" href="{{ asset('storage/images/logo smk.png') }}" type="image/png">
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="min-h-screen bg-gradient-to-br from-slate-100 to-blue-100 flex items-center justify-center p-4">
        <div class="w-full max-w-4xl">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="flex flex-col lg:flex-row">
                    
                    <!-- Left Panel - Features -->
                    <div class="lg:w-1/2 bg-gradient-to-br from-blue-600 to-blue-800 p-10 text-white">
                        <div class="h-full flex flex-col justify-center">
                            <!-- Logo -->
                            <div class="mb-8">
                                <div class="w-14 h-14 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-qrcode text-2xl"></i>
                                </div>
                            </div>
                            
                            <!-- Title -->
                            <h1 class="text-4xl font-bold mb-4">Fitur Kami</h1>
                            <p class="text-blue-100 mb-10 text-lg leading-relaxed">
                                Nikmati pengalaman berbelanja yang mudah dan aman dengan teknologi QR terdepan
                            </p>
                            
                            <!-- Features List -->
                            <div class="space-y-6">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg mb-1">QR Code Pribadi</h3>
                                        <p class="text-blue-100">Dapatkan QR code unik untuk transaksi cepat</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                                        <i class="fas fa-camera"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg mb-1">Pemindai QR</h3>
                                        <p class="text-blue-100">Scan QR code untuk berbelanja dengan mudah</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg mb-1">Dompet Digital</h3>
                                        <p class="text-blue-100">Kelola saldo dan riwayat transaksi</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg mb-1">Keamanan Terjamin</h3>
                                        <p class="text-blue-100">Transaksi aman dengan enkripsi tingkat tinggi</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Panel - Login Form -->
                    <div class="lg:w-1/2 p-10">
                        <div class="h-full flex flex-col justify-center max-w-sm mx-auto">
                            <!-- Header -->
                            <div class="mb-10">
                                <h2 class="text-3xl font-bold text-gray-900 mb-3">
                                    Masuk Untuk Belanja
                                </h2>
                                <p class="text-gray-600">
                                    Akses akun berbelanja dengan QR Anda
                                </p>
                            </div>
                            
                            <!-- Login Form -->
                            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                                @csrf
                                
                                <!-- Email Field -->
                                <div>
                                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Alamat Email
                                    </label>
                                    <div class="relative">
                                        <input 
                                            id="email" 
                                            name="email" 
                                            type="email" 
                                            required
                                            value="{{ old('email') }}"
                                            placeholder="nama@email.com"
                                            class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all @error('email') border-red-300 @enderror"
                                        >
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <i class="fas fa-envelope text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('email')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Password Field -->
                                <div>
                                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Kata Sandi
                                    </label>
                                    <div class="relative">
                                        <input 
                                            id="password" 
                                            name="password" 
                                            type="password" 
                                            required
                                            placeholder="Masukkan kata sandi"
                                            class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all @error('password') border-red-300 @enderror"
                                        >
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <i class="fas fa-lock text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('password')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Remember Me & Forgot Password -->
                                <div class="flex items-center justify-between">
                                    <label class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            name="remember" 
                                            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700">Ingat saya</span>
                                    </label>
                                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                        Lupa kata sandi?
                                    </a>

                                </div>
                                
                                <!-- Login Button -->
                                <button 
                                    type="submit"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 shadow-md hover:shadow-lg"
                                >
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    Masuk
                                </button>
                                
                                <!-- Register Link -->
                                <div class="text-center pt-6 border-t border-gray-200">
                                    <span class="text-sm text-gray-600">Belum punya akun? </span>
                                    <a href="{{ route('register') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-500">
                                        Daftar sekarang
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</body>
</html>