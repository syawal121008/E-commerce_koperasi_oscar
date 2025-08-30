<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>

<!-- Section guru Profile dengan modal -->
<section class="mt-10 p-6 bg-yellow-50 rounded-md border border-yellow-300" x-data="guruProfileModal()">
    @if(!$user->guruProfile)
        <!-- Jika belum punya guru profile - tampilan create -->
        <header>
            <h2 class="text-xl font-semibold text-yellow-800 mb-2">
                Mulai Jual Sekarang!
            </h2>
            <p class="text-yellow-700 mb-4">
                Nikmati keuntungan Program Sukses UMKM baru<br>
                Gratis buka toko, langsung mulai jualan<br>
                Dapatkan dukungan khusus untuk UMKM baru<br>
                Perluas jangkauan tokomu ke luar negeri anti repot
            </p>
        </header>

        <div class="flex items-center mb-4">
            <input 
                id="become_guru" 
                name="become_guru" 
                type="checkbox" 
                class="h-5 w-5 text-yellow-600 focus:ring-yellow-500 border-gray-300 rounded"
                x-model="checked"
            />
            <label for="become_guru" class="ml-3 block text-yellow-800 font-medium">
                Saya ingin mulai jualan dan buka toko sebagai guru
            </label>
        </div>

        <button 
            :disabled="!checked" 
            @click="openModal" 
            class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            Mulai Jual Sekarang!
        </button>
    @else
        <!-- Jika sudah punya guru profile - tampilan edit -->
        <header>
            <h2 class="text-xl font-semibold text-yellow-800 mb-2">
                Profil Penjual Anda
            </h2>
            <p class="text-yellow-700 mb-4">
                Kelola dan perbarui informasi toko Anda<br>
                Pastikan data toko selalu akurat dan terkini<br>
                Status: <span class="font-semibold {{ $user->guruProfile->verified ? 'text-green-600' : 'text-red-600' }}">
                    {{ $user->guruProfile->verified ? 'Terverifikasi' : 'Menunggu Verifikasi' }}
                </span>
            </p>
        </header>

        <div class="mb-4 p-3 bg-white rounded border">
            <h3 class="font-semibold text-gray-800 mb-2">{{ $user->guruProfile->store_name }}</h3>
            <p class="text-sm text-gray-600">{{ $user->guruProfile->store_description ?: 'Belum ada deskripsi' }}</p>
        </div>

        <button 
            @click="openModalEdit" 
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
            Edit Profil Toko
        </button>
    @endif

    <!-- Modal Popup Form -->
    <div
        x-show="showModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        x-cloak
    >
        <div @click.away="closeModal" class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto">
            <header class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-900" x-text="isEditing ? 'Edit Profil Penjual' : 'Buat Profil Penjual'"></h3>
                <button @click="closeModal" class="text-gray-600 hover:text-gray-900 text-2xl leading-none">&times;</button>
            </header>

            <form id="guruProfileForm" @submit.prevent="submitForm" class="space-y-4">
                @csrf

                <div>
                    <label class="block font-semibold mb-1 text-gray-700">
                        Nama Toko <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="store_name"
                        x-model="form.store_name"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                        placeholder="Contoh: Toko Elektronik Jaya"
                    >
                    <template x-if="errors.store_name">
                        <p class="text-red-600 text-sm mt-1" x-text="errors.store_name"></p>
                    </template>
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-gray-700">Deskripsi Toko</label>
                    <textarea
                        name="store_description"
                        x-model="form.store_description"
                        rows="3"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Ceritakan tentang toko Anda..."
                    ></textarea>
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-gray-700">Jenis Usaha</label>
                    <input
                        type="text"
                        name="business_type"
                        x-model="form.business_type"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Contoh: Retail, Wholesale, Dropship"
                    >
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-gray-700">Alamat Usaha</label>
                    <textarea
                        name="business_address"
                        x-model="form.business_address"
                        rows="2"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Alamat lengkap usaha Anda"
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1 text-gray-700">Nomor Rekening</label>
                        <input
                            type="text"
                            name="bank_account"
                            x-model="form.bank_account"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="1234567890"
                        >
                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-gray-700">Nama Bank</label>
                        <input
                            type="text"
                            name="bank_name"
                            x-model="form.bank_name"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Contoh: BCA, Mandiri, BRI"
                        >
                    </div>
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-gray-700">NPWP (Opsional)</label>
                    <input
                        type="text"
                        name="npwp"
                        x-model="form.npwp"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="XX.XXX.XXX.X-XXX.XXX"
                    >
                    <p class="mt-1 text-sm text-gray-500">NPWP diperlukan untuk penjual dengan omzet besar</p>
                </div>

                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" @click="closeModal" class="px-4 py-2 border rounded-md text-gray-700 hover:bg-gray-100">Batal</button>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" x-text="isEditing ? 'Update Profil' : 'Buat Profil Penjual'"></button>
                </div>
            </form>
        </div>
    </div>
</section>

<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
function guruProfileModal() {
    return {
        checked: false,
        showModal: false,
        isEditing: {{ $user->guruProfile ? 'true' : 'false' }},
        guruProfileId: {{ $user->guruProfile ? $user->guruProfile->guru_profile_id : 'null' }},
        form: {
            store_name: '{{ $user->guruProfile ? $user->guruProfile->store_name : "" }}',
            store_description: '{{ $user->guruProfile ? $user->guruProfile->store_description : "" }}',
            business_type: '{{ $user->guruProfile ? $user->guruProfile->business_type : "" }}',
            business_address: '{{ $user->guruProfile ? $user->guruProfile->business_address : "" }}',
            bank_account: '{{ $user->guruProfile ? $user->guruProfile->bank_account : "" }}',
            bank_name: '{{ $user->guruProfile ? $user->guruProfile->bank_name : "" }}',
            npwp: '{{ $user->guruProfile ? $user->guruProfile->npwp : "" }}'
        },
        errors: {},
        
        openModal() {
            if (!this.checked && !this.isEditing) return;
            this.showModal = true;
            this.errors = {};
        },
        
        openModalEdit() {
            this.showModal = true;
            this.errors = {};
        },
        
        closeModal() {
            this.showModal = false;
            if (!this.isEditing) {
                this.resetForm();
                this.checked = false;
            }
        },
        
        resetForm() {
            this.form = {
                store_name: '',
                store_description: '',
                business_type: '',
                business_address: '',
                bank_account: '',
                bank_name: '',
                npwp: ''
            };
            this.errors = {};
        },
        
        async submitForm() {
            this.errors = {};
            console.log('Submitting form...'); // Debug log
            
            try {
                let url, method;
                const formData = new FormData();
                
                // Add CSRF token
                formData.append('_token', '{{ csrf_token() }}');
                
                // Add form data
                Object.keys(this.form).forEach(key => {
                    if (this.form[key]) {
                        formData.append(key, this.form[key]);
                    }
                });
                
                if (this.isEditing) {
                    url = `{{ url('guru_profiles') }}/${this.guruProfileId}`;
                    method = 'POST';
                    formData.append('_method', 'PATCH');
                } else {
                    url = "{{ route('guru_profiles.store') }}";
                    method = 'POST';
                }

                console.log('Sending to:', url); // Debug log
                console.log('Method:', method); // Debug log

                const response = await fetch(url, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });
                
                console.log('Response status:', response.status); // Debug log
                
                const data = await response.json();
                console.log('Response data:', data); // Debug log
                
                if (!response.ok) {
                    if(data.errors){
                        this.errors = {};
                        for (const key in data.errors) {
                            this.errors[key] = data.errors[key][0];
                        }
                    } else if(data.message) {
                        alert(data.message);
                    }
                } else {
                    const message = this.isEditing 
                        ? 'Profil penjual berhasil diperbarui!'
                        : 'Profil penjual berhasil dibuat! Akun Anda akan diupdate ke role guru.';
                    
                    alert(message);
                    this.closeModal();
                    
                    // Reload halaman untuk menampilkan perubahan
                    if (this.isEditing) {
                        window.location.reload();
                    } else {
                        window.location.href = "{{ route('profile.becomeguruPostCreate') }}";
                    }
                }
            } catch (error) {
                console.error('Error:', error); // Debug log
                alert('Terjadi kesalahan: ' + error.message);
            }
        }
    }
}
</script>