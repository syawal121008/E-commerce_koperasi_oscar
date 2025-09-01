<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profil') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Profile Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <!-- Header with Cover -->
                <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-32 relative">
                    <div class="absolute bottom-0 left-6 transform translate-y-1/2">
                        <img src="{{ $user->profilePhotoUrl() }}" 
                             alt="Foto Profil" 
                             class="w-24 h-24 object-cover rounded-full border-4 border-white shadow-lg"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->full_name) }}&background=e5e7eb&color=374151&size=96';">
                    </div>
                    <div class="absolute top-4 right-4 flex space-x-2">
                        <button onclick="openEditModal()" 
                                class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition duration-200 backdrop-blur-sm">
                            <i class="fas fa-edit mr-2"></i>Sunting Profil
                        </button>
                        <button onclick="openPasswordModal()" 
                                class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition duration-200 backdrop-blur-sm">
                            <i class="fas fa-key mr-2"></i>Ganti Kata Sandi
                        </button>
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="pt-16 pb-6 px-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $user->full_name }}</h1>
                            <p class="text-gray-600 mb-1">
                                <i class="fas fa-envelope mr-2"></i>{{ $user->email }}
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                                        <i class="fas fa-check-circle mr-1"></i>Terverifikasi
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                                        <i class="fas fa-exclamation-circle mr-1"></i>Belum Terverifikasi
                                    </span>
                                @endif
                            </p>
                            <p class="text-gray-600">
                                <i class="fas fa-user-tag mr-2"></i>{{ $user->role_name }}
                            </p>
                        </div>
                    </div>

                    <!-- Profile Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- User Information -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-id-card mr-2 text-blue-500"></i>Informasi Akun
                            </h3>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <span class="font-medium text-gray-600">ID Pengguna:</span>
                                    <span class="text-gray-800 block">{{ $user->user_id }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">ID Siswa:</span>
                                    <span class="text-gray-800 block">{{ $user->student_id ?? 'Belum diatur' }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-600">Bergabung:</span>
                                    <span class="text-gray-800 block">{{ $user->created_at->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Balance Information -->
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                            <h3 class="text-lg font-semibold text-green-800 mb-3 flex items-center">
                                <i class="fas fa-wallet mr-2 text-green-600"></i>Saldo
                            </h3>
                            <div class="text-2xl font-bold text-green-700">
                                Rp {{ number_format($user->balance, 0, ',', '.') }}
                            </div>
                            <p class="text-sm text-green-600 mt-1">Saldo aktif</p>
                        </div>

                        <!-- QR Code -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-qrcode mr-2 text-purple-500"></i>Kode QR
                            </h3>
                            @if($user->qr_code)
                                <div class="text-center">
                                    <img src="{{ Storage::url($user->qr_code) }}" 
                                         alt="Kode QR" 
                                         class="w-20 h-20 mx-auto border rounded">
                                    <p class="text-xs text-gray-500 mt-2">Pindai untuk transaksi</p>
                                </div>
                            @else
                                <p class="text-gray-500 text-sm">Kode QR belum tersedia</p>
                            @endif
                        </div>
                    </div>

                    <!-- Activity Stats (Optional) -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-blue-600">0</div>
                            <p class="text-blue-800 text-sm">Total Transaksi</p>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-purple-600">{{ $user->created_at->diffForHumans() }}</div>
                            <p class="text-purple-800 text-sm">Anggota Sejak</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-orange-600">{{ ucfirst($user->role_name) }}</div>
                            <p class="text-orange-800 text-sm">Status</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Back to Dashboard -->
            <div class="mt-6 text-center">
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dasbor
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                <!-- Modal Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Sunting Profil</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <!-- Status Messages -->
                    @if (session('status') && session('status') !== 'password-updated')
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded" id="statusMessage">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded" id="infoMessage">
                            {{ session('info') }}
                        </div>
                    @endif

                    @if ($errors->any() && !$errors->updatePassword->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" id="errorMessage">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
                        @csrf
                        @method('PUT')

                        <!-- Current Profile Photo -->
                        <div class="mb-6 text-center">
                            <p class="text-sm text-gray-600 mb-3"><strong>Foto Profil Saat Ini:</strong></p>
                            <img src="{{ $user->profilePhotoUrl() }}" 
                                 alt="Foto Profil Saat Ini" 
                                 class="w-20 h-20 object-cover rounded-full border shadow mx-auto"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->full_name) }}&background=e5e7eb&color=374151&size=80';">
                        </div>

                        <!-- Profile Photo Upload -->
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">
                                Foto Profil Baru
                                <span class="text-sm text-gray-500 font-normal">(Opsional - JPG, JPEG, PNG, GIF, maks 2MB)</span>
                            </label>
                            <input type="file" 
                                   name="profile_photo" 
                                   id="profile_photo" 
                                   accept="image/jpeg,image/png,image/jpg,image/gif" 
                                   class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                            
                            @error('profile_photo')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror

                            <!-- Preview -->
                            <div class="mt-3" id="previewContainer" style="display: none;">
                                <p class="text-sm text-gray-600 mb-2"><strong>Pratinjau:</strong></p>
                                <img id="preview" class="w-32 h-32 object-cover rounded-full border shadow">
                            </div>
                        </div>

                        <!-- Full Name -->
                        <div class="mb-4">
                            <label class="block text-gray-700 font-semibold mb-2">
                                Nama Lengkap 
                                <span class="text-sm text-gray-500 font-normal">(Opsional)</span>
                            </label>
                            <input type="text" 
                                   name="full_name" 
                                   value="{{ old('full_name') }}" 
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   maxlength="255"
                                   placeholder="Masukkan nama lengkap baru atau kosongkan jika tidak ingin mengubah">
                            @error('full_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-6">
                            <label class="block text-gray-700 font-semibold mb-2">
                                Email 
                                <span class="text-sm text-gray-500 font-normal">(Opsional)</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                   maxlength="255"
                                   placeholder="Masukkan email baru atau kosongkan jika tidak ingin mengubah">
                            <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah email. Jika diubah, Anda perlu verifikasi ulang.</p>
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button type="button" 
                                    onclick="closeEditModal()"
                                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-200">
                                Batal
                            </button>
                            <button type="button" 
                                    onclick="resetForm()"
                                    class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-200">
                                Atur Ulang
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>Simpan
                            </button>
                        </div>
                    </form>

                    <!-- Information -->
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <p class="text-sm text-blue-700">
                            <i class="fas fa-info-circle mr-2"></i>
                            Semua kolom bersifat opsional. Kosongkan kolom yang tidak ingin diubah.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="passwordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <!-- Modal Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-key mr-2 text-red-500"></i>Ganti Kata Sandi
                    </h3>
                    <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="p-6">
                    <!-- Password Status Messages -->
                    @if (session('status') === 'password-updated')
                        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded" id="passwordStatusMessage">
                            <i class="fas fa-check-circle mr-2"></i>Kata sandi berhasil diubah!
                        </div>
                    @endif

                    @if ($errors->updatePassword->any())
                        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded" id="passwordErrorMessage">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->updatePassword->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Header Info -->
                    <div class="mb-6">
                        <p class="text-sm text-gray-600">
                            Pastikan akun Anda menggunakan kata sandi yang panjang dan acak untuk tetap aman.
                        </p>
                    </div>

                    <form action="{{ route('password.update') }}" method="POST" id="passwordForm">
                        @csrf
                        @method('PUT')

                        <!-- Current Password -->
                        <div class="mb-4">
                            <label for="update_password_current_password" class="block text-gray-700 font-semibold mb-2">
                                Kata Sandi Saat Ini
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="update_password_current_password" 
                                       name="current_password" 
                                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 pr-10" 
                                       autocomplete="current-password"
                                       required>
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword('update_password_current_password')">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="update_password_current_password_icon"></i>
                                </button>
                            </div>
                            @error('current_password', 'updatePassword')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- New Password -->
                        <div class="mb-4">
                            <label for="update_password_password" class="block text-gray-700 font-semibold mb-2">
                                Kata Sandi Baru
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="update_password_password" 
                                       name="password" 
                                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 pr-10" 
                                       autocomplete="new-password"
                                       required>
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword('update_password_password')">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="update_password_password_icon"></i>
                                </button>
                            </div>
                            @error('password', 'updatePassword')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-6">
                            <label for="update_password_password_confirmation" class="block text-gray-700 font-semibold mb-2">
                                Konfirmasi Kata Sandi Baru
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       id="update_password_password_confirmation" 
                                       name="password_confirmation" 
                                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 pr-10" 
                                       autocomplete="new-password"
                                       required>
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword('update_password_password_confirmation')">
                                    <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="update_password_password_confirmation_icon"></i>
                                </button>
                            </div>
                            @error('password_confirmation', 'updatePassword')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                            <button type="button" 
                                    onclick="closePasswordModal()"
                                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition duration-200">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200">
                                <i class="fas fa-save mr-2"></i>Ubah Kata Sandi
                            </button>
                        </div>
                    </form>

                    <!-- Security Information -->
                    <div class="mt-4 p-3 bg-yellow-50 rounded-lg">
                        <p class="text-sm text-yellow-700">
                            <i class="fas fa-shield-alt mr-2"></i>
                            Gunakan kata sandi yang kuat dengan kombinasi huruf besar, huruf kecil, angka, dan karakter khusus.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Modal Functions
        function openEditModal() {
            document.getElementById('editModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function openPasswordModal() {
            document.getElementById('passwordModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            
            // Reset form password setelah modal ditutup
            document.getElementById('passwordForm').reset();
        }

        // Close modals when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        document.getElementById('passwordModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePasswordModal();
            }
        });

        // ESC key to close modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
                closePasswordModal();
            }
        });

        // Password visibility toggle
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '_icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Photo preview
        document.getElementById('profile_photo').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('previewContainer');
            const preview = document.getElementById('preview');
            
            if (file) {
                // Validate file size
                if (file.size > 2048000) { // 2MB
                    alert('Ukuran berkas terlalu besar! Maksimal 2MB.');
                    e.target.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Format berkas tidak didukung! Gunakan JPG, JPEG, PNG, atau GIF.');
                    e.target.value = '';
                    previewContainer.style.display = 'none';
                    return;
                }
                
                // Preview image
                const reader = new FileReader();
                reader.onload = function () {
                    preview.src = reader.result;
                    previewContainer.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.style.display = 'none';
            }
        });

        function resetForm() {
            if (confirm('Yakin ingin mengatur ulang formulir? Semua perubahan yang belum disimpan akan hilang.')) {
                document.getElementById('profileForm').reset();
                document.getElementById('previewContainer').style.display = 'none';
            }
        }

        // Auto-show modals based on errors or messages
        document.addEventListener('DOMContentLoaded', function() {
            @if ($errors->updatePassword->any())
                openPasswordModal();
            @elseif (session('status') === 'password-updated')
                // Tampilkan pesan sukses sebentar kemudian tutup modal
                openPasswordModal();
                setTimeout(function() {
                    closePasswordModal();
                }, 3000); // Tutup modal setelah 3 detik
            @elseif (($errors->any() && !$errors->updatePassword->any()) || (session('status') && session('status') !== 'password-updated') || session('info'))
                openEditModal();
            @endif
        });

        // Auto-hide paid/info messages
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const statusMessage = document.getElementById('statusMessage');
                const infoMessage = document.getElementById('infoMessage');
                const passwordStatusMessage = document.getElementById('passwordStatusMessage');
                
                if (statusMessage) {
                    statusMessage.style.display = 'none';
                }
                
                if (infoMessage) {
                    infoMessage.style.display = 'none';
                }

                if (passwordStatusMessage) {
                    passwordStatusMessage.style.display = 'none';
                }
            }, 5000);
        });
    </script>
</x-app-layout>