{{-- resources/views/admin/users/edit.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg" role="alert">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('admin.users.update', $user->user_id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Full Name -->
                        <div class="mb-4">
                            <label for="full_name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                            <input type="text" 
                                   name="full_name" 
                                   id="full_name" 
                                   value="{{ old('full_name', $user->full_name) }}" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Student ID -->
                        <div class="mb-4">
                            <label for="student_id" class="block text-sm font-medium text-gray-700">NIS/NIP</label>
                            <input type="text" 
                                   name="student_id" 
                                   id="student_id" 
                                   value="{{ old('student_id', $user->student_id) }}" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Role -->
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-gray-700">Peran</label>
                            <select name="role" 
                                    id="role" 
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="{{ \App\Models\User::ROLE_CUSTOMER }}" 
                                        {{ old('role', $user->role) === \App\Models\User::ROLE_CUSTOMER ? 'selected' : '' }}>
                                    Customer
                                </option>
                                <option value="{{ \App\Models\User::ROLE_GURU }}" 
                                        {{ old('role', $user->role) === \App\Models\User::ROLE_GURU ? 'selected' : '' }}>
                                    Guru
                                </option>
                                <option value="{{ \App\Models\User::ROLE_ADMIN }}" 
                                        {{ old('role', $user->role) === \App\Models\User::ROLE_ADMIN ? 'selected' : '' }}>
                                    Admin
                                </option>
                            </select>
                        </div>

                        <!-- Balance -->
                        <div class="mb-4">
                            <label for="balance" class="block text-sm font-medium text-gray-700">Saldo</label>
                            <input type="number" 
                                   name="balance" 
                                   id="balance" 
                                   step="0.01"
                                   min="0"
                                   value="{{ old('balance', $user->balance) }}" 
                                   required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Password -->
                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi Baru (kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- Password Confirmation -->
                        <div class="mb-4">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">konfirmasi Kata Sandi Baru</label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </div>

                        <!-- User Info Display -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <h4 class="font-medium text-gray-900 mb-2">Informasi Pengguna:</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium">ID Pengguna:</span> {{ $user->user_id }}
                                </div>
                                <div>
                                    <span class="font-medium">DIbuat:</span> {{ $user->created_at->format('d M Y H:i') }}
                                </div>
                                <div>
                                    <span class="font-medium">Terakhir Diperbarui:</span> {{ $user->updated_at->format('d M Y H:i') }}
                                </div>
                                <div>
                                    <span class="font-medium">Kode QR:</span> 
                                    @if($user->hasValidQrCode())
                                        <span class="text-green-600">✓ Berhasil Dibuat</span>
                                    @else
                                        <span class="text-red-600">✗ Tidak Ditemukan</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('admin.users.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded inline-flex items-center">
                                ← Kembali Ke Pengguna
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Perbarui Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>