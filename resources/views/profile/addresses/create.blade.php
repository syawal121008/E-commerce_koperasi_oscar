<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Alamat Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-8">
                <form action="{{ route('addresses.store') }}" method="POST">
                    @csrf
                    {{-- Form Fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="recipient_name" class="block font-medium text-sm text-gray-700">Nama Penerima</label>
                            <input type="text" name="recipient_name" id="recipient_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                        <div>
                            <label for="phone_number" class="block font-medium text-sm text-gray-700">Nomor Telepon</label>
                            <input type="text" name="phone_number" id="phone_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                    </div>
                    
                    {{-- IndoRegion Dropdowns --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label for="province_id" class="block font-medium text-sm text-gray-700">Provinsi</label>
                            <select name="province_id" id="province_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Pilih Provinsi</option>
                                @foreach($provinces as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="regency_id" class="block font-medium text-sm text-gray-700">Kota/Kabupaten</label>
                            <select name="regency_id" id="regency_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required disabled></select>
                        </div>
                    </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label for="district_id" class="block font-medium text-sm text-gray-700">Kecamatan</label>
                            <select name="district_id" id="district_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required disabled></select>
                        </div>
                        <div>
                            <label for="postal_code" class="block font-medium text-sm text-gray-700">Kode Pos</label>
                            <input type="text" name="postal_code" id="postal_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="full_address" class="block font-medium text-sm text-gray-700">Alamat Lengkap</label>
                        <textarea name="full_address" id="full_address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></textarea>
                    </div>

                    {{-- Map Container --}}
                    <div class="mt-6">
                        <label class="block font-medium text-sm text-gray-700 mb-2">Pin Lokasi di Peta</label>
                        <div id="map" style="height: 300px;" class="rounded-lg border"></div>
                    </div>

                    {{-- Hidden fields --}}
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="address_label" value="Rumah">

                    <div class="flex justify-end mt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                            Simpan Alamat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- Bagian Peta (Tidak berubah) ---
        const map = L.map('map').setView([-2.548926, 118.0148634], 5); // Default: Indonesia
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        let marker = L.marker([-2.548926, 118.0148634]).addTo(map);

        function updateMap(lat, lng, zoom = 15) {
            map.setView([lat, lng], zoom);
            marker.setLatLng([lat, lng]);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
        }

        // --- Bagian Dropdown (Ini yang kita perbaiki) ---
        const provinceSelect = document.getElementById('province_id');
        const regencySelect = document.getElementById('regency_id');
        const districtSelect = document.getElementById('district_id');

        provinceSelect.onchange = function() {
            let provinceId = this.value;
            regencySelect.innerHTML = '<option value="">Memuat...</option>';
            regencySelect.disabled = true;
            districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
            districtSelect.disabled = true;

            if (provinceId) {
                fetch(`/api/regencies?province_id=${provinceId}`)
                    .then(response => response.json())
                    .then(regencies => {
                        regencySelect.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';
                        
                        // === PERBAIKAN UTAMA ADA DI SINI ===
                        // Menggunakan Object.entries() untuk iterasi yang lebih aman
                        Object.entries(regencies).forEach(([id, name]) => {
                            regencySelect.innerHTML += `<option value="${id}">${name}</option>`;
                        });
                        // ===================================

                        regencySelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        regencySelect.innerHTML = '<option value="">Gagal memuat data</option>';
                    });
            }
        };

        regencySelect.onchange = function() {
            let regencyId = this.value;
            districtSelect.innerHTML = '<option value="">Memuat...</option>';
            districtSelect.disabled = true;

            if (regencyId) {
                fetch(`/api/districts?regency_id=${regencyId}`)
                    .then(response => response.json())
                    .then(districts => {
                        districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                        
                        // === PERBAIKAN UTAMA ADA DI SINI JUGA ===
                        Object.entries(districts).forEach(([id, name]) => {
                            districtSelect.innerHTML += `<option value="${id}">${name}</option>`;
                        });
                        // ======================================

                        districtSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        districtSelect.innerHTML = '<option value="">Gagal memuat data</option>';
                    });
            }
        };

        // --- Bagian Geocoding Peta (Tidak berubah) ---
        districtSelect.onchange = function() {
            const provinceText = provinceSelect.options[provinceSelect.selectedIndex].text;
            const regencyText = regencySelect.options[regencySelect.selectedIndex].text;
            const districtText = this.options[this.selectedIndex].text;
            const query = `${districtText}, ${regencyText}, ${provinceText}`;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        updateMap(data[0].lat, data[0].lon);
                    }
                });
        };
    });
</script>
@endpush
</x-app-layout>