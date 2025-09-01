<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koperasi SMKIUTAMA - Konfirmasi Password</title>
    <link rel="icon" href="{{ asset('storage/images/smk.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 to-blue-100 flex items-center justify-center p-6">

    <div class="w-full max-w-md">
        <div class="bg-white shadow-xl rounded-2xl p-8">

            <!-- Header -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 mx-auto bg-blue-100 text-blue-600 flex items-center justify-center rounded-full shadow">
                    <i class="fas fa-shield-alt text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mt-4">Konfirmasi Password</h2>
                <p class="text-sm text-gray-600 mt-2">
                    Ini adalah area aman aplikasi. Harap masukkan password Anda untuk melanjutkan.
                </p>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
    @csrf

    <!-- Hidden Email & Token -->
    <input type="hidden" name="token" value="{{ $request->route('token') }}">
    <input type="hidden" name="email" value="{{ $request->email }}">

    <!-- Password -->
    <div>
        <x-input-label for="password" :value="__('Password')" />
        <x-text-input id="password" 
            class="block mt-1 w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200" 
            type="password"
            name="password"
            required autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <!-- Confirm Password -->
    <div>
        <x-input-label for="password_confirmation" :value="__('Konfirmasi Password')" />
        <x-text-input id="password_confirmation" 
            class="block mt-1 w-full border-gray-300 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200" 
            type="password"
            name="password_confirmation"
            required autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>

    <!-- Confirm Button -->
    <div>
        <x-primary-button class="w-full justify-center py-3 text-lg bg-blue-600 hover:bg-blue-700 rounded-lg shadow-md">
            <i class="fas fa-check-circle mr-2"></i>
            {{ __('Konfirmasi') }}
        </x-primary-button>
    </div>
</form>

            <!-- Back to home -->
            <div class="text-center mt-6">
                <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

</body>
</html>
