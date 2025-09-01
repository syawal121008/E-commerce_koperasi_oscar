<x-app-layout>
    {{-- HEADER --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                QR Code Saya
            </h2>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 hover:underline">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    {{-- KONTEN UTAMA --}}
    <div class="py-12">
        <div class="mx-auto max-w-md sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-lg sm:rounded-lg">
                <div class="p-8 text-center">

                    <h3 class="text-lg font-medium text-gray-900">Pindai untuk Membayar</h3>
                    <p class="mb-6 mt-1 text-sm text-gray-500">Tunjukkan kode ini kepada penjual</p>
                    
                    <div id="qr-container" class="mb-6">
                        @if($user->qr_code_url)
                            <div class="mx-auto flex h-64 w-64 items-center justify-center rounded-lg border bg-gray-50 p-2 shadow-inner">
                                <img src="{{ $user->qr_code_url }}" 
                                     alt="QR Code Pembayaran" 
                                     class="h-full w-full object-contain"
                                     onerror="this.style.display='none'; document.getElementById('qr-error').style.display='flex';">
                            </div>
                        @else
                            <div class="mx-auto flex h-64 w-64 flex-col items-center justify-center rounded-lg border-2 border-dashed bg-gray-50">
                                <p class="text-gray-500">QR Code tidak tersedia</p>
                            </div>
                        @endif
                        
                        <div id="qr-error" style="display: none;" class="mx-auto flex h-64 w-64 flex-col items-center justify-center rounded-lg border-2 border-dashed border-red-300 bg-red-50">
                            <p class="text-sm text-red-600">Gagal memuat QR Code</p>
                        </div>
                    </div>
                    
                    <div class="mb-8 border-y text-left">
                        <div class="flex justify-between px-4 py-3">
                            <span class="text-sm text-gray-600">Nama</span>
                            <span class="font-medium text-gray-900">{{ $user->full_name }}</span>
                        </div>
                        <div class="flex justify-between border-t px-4 py-3">
                            <span class="text-sm text-gray-600">NIS/NIP</span>
                            <span class="font-medium text-gray-900">{{ $user->student_id }}</span>
                        </div>
                        <div class="flex justify-between border-t bg-green-50 px-4 py-3">
                            <span class="text-sm text-gray-600">Saldo Saat Ini</span>
                            <span class="font-bold text-green-700">Rp {{ number_format($user->balance, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row justify-center items-center gap-3 w-full">
                        @if($user->qr_code_url)
                            <button onclick="downloadQR()" class="flex items-center justify-center gap-2 rounded-md bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 w-full sm:w-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download
                            </button>
                        @else
                            <button class="flex items-center justify-center rounded-md bg-green-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-green-700 w-full sm:w-auto">
                                Generate QR Code
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function downloadQR() {
            const qrImage = document.querySelector('#qr-container img');
            if (!qrImage || !qrImage.src) {
                alert('QR code tidak tersedia untuk diunduh.');
                return;
            }
            const link = document.createElement('a');
            link.href = qrImage.src;
            link.download = 'qr-code-{{ $user->student_id }}.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // Anda bisa menambahkan fungsi untuk generate atau regenerate di sini
        // Contoh:
        // function regenerateQR() {
        //     if(confirm('Apakah Anda yakin ingin membuat ulang QR Code? Kode yang lama tidak akan berlaku.')) {
        //         // Kirim request ke server untuk regenerate
        //         window.location.href = '{{-- route('qrcode.regenerate') --}}'; 
        //     }
        // }
    </script>
</x-app-layout>