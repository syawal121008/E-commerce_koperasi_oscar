<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koperasi SMKIUTAMA</title>
        <link rel="icon" href="{{ asset('storage/images/smk.png') }}" type="image/png">
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="min-h-screen bg-gradient-to-br from-slate-100 to-blue-100 flex items-center justify-center p-4">
        <div class="w-full max-w-4xl">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="flex flex-col lg:flex-row">
                    
                    <!-- Left Panel - Benefits -->
                    <div class="lg:w-1/2 bg-gradient-to-br from-blue-600 to-blue-800 p-10 text-white">
                        <div class="h-full flex flex-col justify-center">
                            <!-- Logo -->
                            <div class="mb-8">
                                <div class="w-14 h-14 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-user-plus text-2xl"></i>
                                </div>
                            </div>
                            
                            <!-- Title -->
                            <h1 class="text-4xl font-bold mb-4">Bergabung Sekarang</h1>
                            <p class="text-blue-100 mb-10 text-lg leading-relaxed">
                                Daftar dan dapatkan QR code pribadi untuk kemudahan berbelanja di Koperasi
                            </p>
                            
                            <!-- Benefits List -->
                            <div class="space-y-6">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                                        <i class="fas fa-qrcode"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg mb-1">QR Code Unik</h3>
                                        <p class="text-blue-100">Dapatkan QR code berdasarkan NIP/NIS Anda</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg mb-1">Pembayaran Mudah</h3>
                                        <p class="text-blue-100">Bayar dengan scan QR code di toko atau menggunakan saldo yang anda punya</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg mb-1">Riwayat Transaksi</h3>
                                        <p class="text-blue-100">Lihat semua riwayat pembelian Anda</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center flex-shrink-0 mt-1">
                                        <i class="fas fa-plus-circle"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg mb-1">Top Up Saldo</h3>
                                        <p class="text-blue-100">Isi saldo kapan saja dengan mudah</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Panel - Register Form -->
                    <div class="lg:w-1/2 p-10">
                        <div class="h-full flex flex-col justify-center max-w-sm mx-auto">
                            <!-- Header -->
                            <div class="mb-10">
                                <h2 class="text-3xl font-bold text-gray-900 mb-3">
                                    Buat Akun Baru
                                </h2>
                                <p class="text-gray-600">
                                    Bergabung dengan Koperasi dan dapatkan QR code pribadi Anda
                                </p>
                            </div>
                            
                            <!-- Register Form -->
                            <form action="{{ route('register') }}" method="POST" class="space-y-5">
                                @csrf
                                
                                <!-- Hidden Role Field (automatically set to customer) -->
                                <input type="hidden" name="role" value="customer">
                                
                                <!-- Full Name Field -->
                                <div>
                                    <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nama Lengkap
                                    </label>
                                    <div class="relative">
                                        <input 
                                            id="full_name" 
                                            name="full_name" 
                                            type="text" 
                                            required
                                            value="{{ old('full_name') }}"
                                            placeholder="Masukkan nama lengkap"
                                            class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all @error('full_name') border-red-300 @enderror"
                                        >
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('full_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Student ID Field -->
                                <div>
                                    <label for="student_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                        NIP/NIS
                                    </label>
                                    <div class="relative">
                                        <input 
                                            id="student_id" 
                                            name="student_id" 
                                            type="text" 
                                            required
                                            value="{{ old('student_id') }}"
                                            placeholder="Masukkan NIP atau NIS"
                                            class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all @error('student_id') border-red-300 @enderror"
                                        >
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <i class="fas fa-id-badge text-gray-400"></i>
                                        </div>
                                    </div>
                                    @error('student_id')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
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
                                
                                <!-- Confirm Password Field -->
                                <div>
                                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Konfirmasi Kata Sandi
                                    </label>
                                    <div class="relative">
                                        <input 
                                            id="password_confirmation" 
                                            name="password_confirmation" 
                                            type="password" 
                                            required
                                            placeholder="Konfirmasi kata sandi"
                                            class="w-full px-4 py-3 pl-11 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
                                        >
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center">
                                            <i class="fas fa-lock text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Register Button -->
                                <button 
                                    type="submit"
                                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 shadow-md hover:shadow-lg"
                                >
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Buat Akun & Generate QR
                                </button>
                                
                                <!-- Login Link -->
                                <div class="text-center pt-6 border-t border-gray-200">
                                    <span class="text-sm text-gray-600">Sudah punya akun? </span>
                                    <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-500">
                                        Masuk di sini
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Info Modal/Alert (Optional - bisa ditambahkan dengan JavaScript) -->
    <div id="qr-info" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                    <i class="fas fa-qrcode text-blue-600 text-xl"></i>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Tentang QR Code Anda</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500 mb-4">
                        Setelah mendaftar, Anda akan mendapatkan QR code unik berdasarkan NIP/NIS yang dapat digunakan untuk:
                    </p>
                    <ul class="text-sm text-gray-600 text-left space-y-1">
                        <li>• Pembayaran di toko</li>
                        <li>• Top-up saldo</li>
                        <li>• Melihat riwayat transaksi</li>
                        <li>• Verifikasi akun</li>
                    </ul>
                </div>
                <div class="items-center px-4 py-3">
                    <button 
                        onclick="document.getElementById('qr-info').classList.add('hidden')"
                        class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700"
                    >
                        Mengerti
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>