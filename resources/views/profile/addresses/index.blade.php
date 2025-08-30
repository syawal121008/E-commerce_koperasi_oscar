{{-- resources/views/profile/addresses/index.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Daftar Alamat Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 text-right">
                <a href="{{ route('addresses.create') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">
                    + Tambah Alamat Baru
                </a>
            </div>
            
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse ($addresses as $address)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-bold text-lg">{{ $address->address_label }}</h3>
                        <p class="font-semibold">{{ $address->recipient_name }}</p>
                        <p>{{ $address->phone_number }}</p>
                        <p>{{ $address->full_address }}</p>
                        {{-- Tampilkan info wilayah (membutuhkan relasi di model Address) --}}
                        <p class="text-sm text-gray-500 mt-2">
                            {{-- Kecamatan, Kota, Provinsi, Kode Pos --}}
                        </p>
                        {{-- Tambahkan tombol Edit & Hapus di sini --}}
                    </div>
                @empty
                    <div class="md:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                        Anda belum memiliki alamat tersimpan.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>