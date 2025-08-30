<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Top Up') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Left Column: Topup Details -->
                        <div>
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-6">Informasi Top Up</h3>
                                
                                <div class="space-y-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Reference ID:</span>
                                        <span class="font-medium">{{ $topup->payment_reference }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Nominal:</span>
                                        <span class="font-bold text-lg text-green-600">{{ $topup->formatted_amount }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Metode:</span>
                                        <span class="font-medium">{{ $topup->method_name }}</span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Status:</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $topup->status_color }}">
                                            @if($topup->status === 'paid')
                                                <i class="fas fa-check mr-1"></i>
                                            @elseif($topup->status === 'pending')
                                                <i class="fas fa-clock mr-1"></i>
                                            @else
                                                <i class="fas fa-times mr-1"></i>
                                            @endif
                                            {{ $topup->status_name }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tanggal:</span>
                                        <span class="font-medium">{{ $topup->created_at->format('d F Y, H:i') }}</span>
                                    </div>

                                    @if($topup->payment_proof_url)
                                        <div class="border-t pt-4">
                                            <span class="text-gray-600 block mb-2">Bukti Transfer:</span>
                                            <img src="{{ $topup->payment_proof_url }}" alt="Bukti Transfer" class="max-w-full h-auto rounded-lg border">
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-6 flex space-x-2">
                                    <a href="{{ route('topup.index') }}" class="bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600 transition duration-200">
                                        <i class="fas fa-arrow-left mr-2"></i>
                                        Kembali
                                    </a>
                                    
                                    @if($topup->status === 'paid')
                                        <a href="{{ route('topup.receipt', $topup->topup_id) }}" class="bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-200">
                                            <i class="fas fa-receipt mr-2"></i>
                                            Lihat Bukti
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: QRIS or Status -->
                        <div>
                            @if($topup->status === 'pending' && isset($qrisCode))
                                <div class="bg-gray-50 rounded-lg p-6 text-center">
                                    <h3 class="text-lg font-medium text-gray-900 mb-6">Scan untuk Pembayaran</h3>
                                    
                                    <div class="mb-4">
                                        <div class="bg-green-100 border border-green-300 rounded-lg p-3">
                                            <div class="flex justify-between items-center">
                                                <span class="text-green-800 font-medium">Total Pembayaran:</span>
                                                <span class="text-green-800 font-bold text-lg">{{ $topup->formatted_amount }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white p-4 rounded-lg border-2 border-dashed border-gray-300 mb-6">
                                        <div id="qrcode" class="flex justify-center"></div>
                                    </div>

                                    @if(!$topup->payment_url)
                                        <!-- Upload Bukti Transfer -->
                                        <div id="uploadSection">
                                            <form id="uploadForm" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="topup_id" value="{{ $topup->topup_id }}">
                                                
                                                <div class="mb-4">
                                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                                        <i class="fas fa-upload mr-1"></i>
                                                        Upload Bukti Transfer <span class="text-red-500">*</span>
                                                    </label>
                                                    <input 
                                                        type="file" 
                                                        name="payment_proof" 
                                                        accept="image/*" 
                                                        required
                                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                                    >
                                                    <div class="mt-1 text-xs text-gray-500">
                                                        Format: JPG, PNG, JPEG. Maksimal 2MB
                                                    </div>
                                                </div>
                                                
                                                <button 
                                                    type="submit" 
                                                    id="uploadBtn"
                                                    class="w-full bg-green-600 text-white py-3 px-4 rounded-md hover:bg-green-700 focus:ring-4 focus:ring-green-200 transition duration-200 font-semibold"
                                                >
                                                    <i class="fas fa-check mr-2"></i>
                                                    Konfirmasi Pembayaran
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            @elseif($topup->status === 'paid')
                                <div class="bg-gray-50 rounded-lg p-6 text-center">
                                    <div class="mb-4">
                                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                                            <i class="fas fa-check text-green-600 text-xl"></i>
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Pembayaran Berhasil!</h3>
                                    <p class="text-gray-600 mb-4">Top up sebesar {{ $topup->formatted_amount }} telah berhasil diproses.</p>
                                    
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4">
                                        <p class="text-sm text-green-800">Saldo Anda telah bertambah dan siap digunakan.</p>
                                    </div>
                                </div>
                            @elseif($topup->status === 'failed')
                                <div class="bg-gray-50 rounded-lg p-6 text-center">
                                    <div class="mb-4">
                                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                                            <i class="fas fa-times text-red-600 text-xl"></i>
                                        </div>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Pembayaran Gagal</h3>
                                    <p class="text-gray-600 mb-4">Top up sebesar {{ $topup->formatted_amount }} gagal diproses.</p>
                                    
                                    <a href="{{ route('topup.qris') }}" class="inline-block bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-200">
                                        <i class="fas fa-redo mr-2"></i>
                                        Coba Lagi
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($topup->status === 'pending' && isset($qrisCode))
        <!-- Include QR Code Library -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>

        <script>
            // Generate QR Code on page load
            document.addEventListener('DOMContentLoaded', function() {
                const qrisCode = @json($qrisCode);
                generateQRCode(qrisCode);
            });

            function generateQRCode(qrisData) {
                const qr = qrcode(0, 'M');
                qr.addData(qrisData);
                qr.make();
                
                const qrElement = document.getElementById('qrcode');
                qrElement.innerHTML = '';
                
                const img = document.createElement('img');
                img.src = qr.createDataURL(10);
                img.alt = 'QRIS Code';
                img.style.maxWidth = '250px';
                img.className = 'mx-auto';
                
                qrElement.appendChild(img);
            }

            // Upload form submission
            @if(!$topup->payment_url)
                document.getElementById('uploadForm').addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const uploadBtn = document.getElementById('uploadBtn');
                    uploadBtn.disabled = true;
                    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengupload...';

                    try {
                        const formData = new FormData(this);
                        const response = await fetch(`/topup/{{ $topup->topup_id }}/upload-proof`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        const result = await response.json();

                        if (result.paid) {
                            // Reload page to show paid state
                            location.reload();
                        } else {
                            alert(result.message || 'Gagal mengupload bukti transfer');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan sistem');
                    } finally {
                        uploadBtn.disabled = false;
                        uploadBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Konfirmasi Pembayaran';
                    }
                });
            @endif
        </script>
    @endif
</x-app-layout>