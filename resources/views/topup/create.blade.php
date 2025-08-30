<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Isi Saldo Via Qris') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid md:grid-cols-2 gap-8">
                        <!-- Topup Form -->
                        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 p-8 rounded-2xl border border-indigo-200 shadow-lg">
                            <div class="text-center mb-8">
                                <h3 class="text-2xl font-bold text-gray-800 mb-2">Isi Saldo</h3>
                                <p class="text-gray-600">Isi saldo dengan mudah menggunakan QRIS</p>
                            </div>
                            
                            <!-- Payment Method Selection -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">Metode Pembayaran</label>
                                <div class="payment-option selected p-4 text-center cursor-pointer bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-lg shadow-md">
                                    <div class="payment-icon mx-auto mb-2">
                                        <i class="fas fa-qrcode text-2xl"></i>
                                    </div>
                                    <p class="font-medium">QRIS</p>
                                    <p class="text-xs opacity-90">Scan & Pay dengan aplikasi favorit Anda</p>
                                </div>
                            </div>

                            <!-- Amount Input -->
                            <form id="topupForm" method="POST" action="{{ route('topup.store') }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="method" value="qris">
                                
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nominal Isi Saldo</label>
                                    <div class="relative">
                                        <input 
                                            type="text" 
                                            id="nominalInput"
                                            name="amount"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-lg"
                                            placeholder="Masukkan nominal (min. Rp 1.000)"
                                            required
                                            value="{{ old('amount') }}"
                                        >
                                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-500 font-medium">Rp</span>
                                    </div>
                                    @error('amount')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    
                                    <!-- Quick Amount Buttons -->
                                    <div class="mt-3 grid grid-cols-3 gap-2">
                                        <button type="button" onclick="setAmount(10000)" class="quick-amount-btn px-3 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-700 rounded-lg text-sm font-medium transition">
                                            10K
                                        </button>
                                        <button type="button" onclick="setAmount(25000)" class="quick-amount-btn px-3 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-700 rounded-lg text-sm font-medium transition">
                                            25K
                                        </button>
                                        <button type="button" onclick="setAmount(50000)" class="quick-amount-btn px-3 py-2 bg-gray-100 hover:bg-indigo-100 text-gray-700 rounded-lg text-sm font-medium transition">
                                            50K
                                        </button>
                                    </div>
                                    
                                    <div class="mt-3 flex justify-between items-center text-sm">
                                        <span class="text-gray-600">Total Pembayaran:</span>
                                        <span id="totalAmount" class="font-bold text-green-600">Rp 0</span>
                                    </div>
                                </div>

                                <!-- Steps Display -->
                                <div id="stepsContainer" class="mb-6 hidden">
                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                        <h4 class="font-semibold text-gray-800 mb-3">Langkah Selanjutnya:</h4>
                                        <div id="step1" class="flex items-center mb-2 text-sm">
                                            <span class="w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs mr-3">1</span>
                                            <span>Generate QR Code</span>
                                        </div>
                                        <div id="step2" class="flex items-center mb-2 text-sm text-gray-400">
                                            <span class="w-6 h-6 bg-gray-300 text-white rounded-full flex items-center justify-center text-xs mr-3">2</span>
                                            <span>Scan & bayar dengan aplikasi Anda</span>
                                        </div>
                                        <div id="step3" class="flex items-center text-sm text-gray-400">
                                            <span class="w-6 h-6 bg-gray-300 text-white rounded-full flex items-center justify-center text-xs mr-3">3</span>
                                            <span>Upload bukti transfer</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Generate QR Button -->
                                <button 
                                    type="button"
                                    id="generateQRBtn" 
                                    class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white py-3 px-6 rounded-lg font-semibold text-lg transition duration-200 shadow-lg hover:shadow-xl"
                                >
                                    <i class="fas fa-qrcode mr-2"></i>
                                    Generate QR Code
                                </button>

                                <!-- Payment Proof Upload Section -->
                                <div id="uploadSection" class="mt-6 hidden">
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                        <div class="flex items-start">
                                            <i class="fas fa-check-circle text-green-500 text-lg mr-3 mt-1"></i>
                                            <div>
                                                <h4 class="font-semibold text-green-800 mb-1">Sudah melakukan pembayaran?</h4>
                                                <p class="text-green-700 text-sm">Upload bukti transfer untuk mendapatkan saldo secara otomatis</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-upload mr-1"></i>
                                            Upload Bukti Transfer
                                        </label>
                                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-400 transition-colors">
                                            <div class="space-y-1 text-center">
                                                <div id="uploadPreview" class="hidden">
                                                    <img id="previewImage" class="mx-auto h-32 w-auto rounded-lg shadow-md" alt="Preview">
                                                    <p id="fileName" class="text-sm text-gray-600 mt-2"></p>
                                                </div>
                                                <div id="uploadPlaceholder">
                                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                    </svg>
                                                    <div class="flex text-sm text-gray-600 justify-center">
                                                        <label for="payment_proof" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                            <span>Upload gambar</span>
                                                            <input id="payment_proof" name="payment_proof" type="file" class="sr-only" accept="image/*">
                                                        </label>
                                                        <p class="pl-1">atau drag and drop</p>
                                                    </div>
                                                    <p class="text-xs text-gray-500">PNG, JPG, JPEG sampai 5MB</p>
                                                </div>
                                            </div>
                                        </div>
                                        @error('payment_proof')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <button 
                                        type="submit"
                                        id="submitBtn"
                                        class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white py-3 px-6 rounded-lg font-semibold text-lg transition duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                                        disabled
                                    >
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Kirim & Dapatkan Saldo Otomatis
                                    </button>

                                    <div class="mt-3 text-center">
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-bolt text-yellow-500 mr-1"></i>
                                            Saldo akan ditambahkan secara otomatis setelah upload bukti transfer
                                        </p>
                                    </div>
                                </div>
                            </form>

                            <div class="mt-6 text-center">
                                <div class="flex items-center justify-center space-x-4 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <i class="fas fa-shield-alt text-green-500 mr-1"></i>
                                        <span>Aman</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-bolt text-yellow-500 mr-1"></i>
                                        <span>Otomatis</span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-blue-500 mr-1"></i>
                                        <span>Terpercaya</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Information & Instructions -->
                        <div class="space-y-6">
                            <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                                <h3 class="text-xl font-semibold text-gray-800 mb-4">
                                    <i class="fas fa-info-circle text-indigo-600 mr-2"></i>
                                    Cara Top Up (Baru)
                                </h3>
                                <ol class="list-decimal list-inside space-y-3 text-gray-600">
                                    <li class="flex items-start">
                                        <span class="mr-2">1.</span>
                                        <span>Masukkan nominal yang ingin anda top up</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">2.</span>
                                        <span>Klik "Generate QR Code" untuk mendapatkan QR pembayaran</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">3.</span>
                                        <span>Scan QR Code dengan aplikasi pembayaran Anda</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">4.</span>
                                        <span class="font-semibold text-green-600">Upload bukti transfer dan saldo langsung ditambahkan!</span>
                                    </li>
                                </ol>
                                
                                <div class="mt-4 p-3 bg-green-50 rounded-lg border-l-4 border-green-400">
                                    <p class="text-sm text-green-700">
                                        <i class="fas fa-sparkles mr-1"></i>
                                        <strong>Fitur Baru:</strong> Upload bukti transfer dan saldo otomatis bertambah setelah sistem melakukan pengecekan!
                                    </p>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-2xl border border-green-200">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                    <i class="fas fa-wallet text-green-600 mr-2"></i>
                                    Aplikasi Yang Didukung
                                </h3>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="flex items-center p-3 bg-white rounded-lg shadow-sm">
                                        <i class="fab fa-google-pay text-blue-500 text-xl mr-2"></i>
                                        <span class="text-sm font-medium">GoPay</span>
                                    </div>
                                    <div class="flex items-center p-3 bg-white rounded-lg shadow-sm">
                                        <i class="fas fa-mobile-alt text-orange-500 text-xl mr-2"></i>
                                        <span class="text-sm font-medium">DANA</span>
                                    </div>
                                    <div class="flex items-center p-3 bg-white rounded-lg shadow-sm">
                                        <i class="fas fa-university text-purple-500 text-xl mr-2"></i>
                                        <span class="text-sm font-medium">Bank Digital</span>
                                    </div>
                                    <div class="flex items-center p-3 bg-white rounded-lg shadow-sm">
                                        <i class="fas fa-credit-card text-red-500 text-xl mr-2"></i>
                                        <span class="text-sm font-medium">E-Wallet</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-info-circle text-blue-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <strong>Tips:</strong> Pastikan bukti transfer yang diupload jelas dan menunjukkan 
                                            nominal yang sesuai. Saldo akan otomatis bertambah setelah upload berhasil.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Styles -->
    <style>
        .payment-option {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .payment-option:hover, .payment-option.selected {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2);
        }
        
        .quick-amount-btn:hover {
            transform: translateY(-1px);
        }
        
        #generateQRBtn:hover, #submitBtn:hover:not(:disabled) {
            transform: translateY(-1px);
        }
        
        .payment-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin: 0 auto;
        }

        .step-active {
            color: #059669 !important;
        }

        .step-active .w-6 {
            background-color: #059669 !important;
        }

        .swal-custom-popup {
            border-radius: 15px;
        }
        
        .swal-confirm-btn {
            font-weight: 600;
            border-radius: 8px;
            padding: 10px 20px;
        }
    </style>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode-generator/1.4.4/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Static QRIS code (same as your original working version)
        const STATIC_QRIS = "00020101021126670016COM.NOBUBANK.WWW01189360050300000879140214844519767362640303UMI51440014ID.CO.QRIS.WWW0215ID20243345184510303UMI5204541153033605802ID5920YANTO SHOP OK18846346005DEPOK61051641162070703A0163046879";

        // Form state
        let qrGenerated = false;
        let paymentProofUploaded = false;

        // Utility functions
        function formatRupiah(amount) {
            return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function updateAmounts() {
            const input = document.getElementById('nominalInput').value.replace(/\./g, '');
            const amount = parseInt(input) || 0;
            
            if (amount > 0) {
                document.getElementById('totalAmount').textContent = formatRupiah(amount);
                document.getElementById('stepsContainer').classList.remove('hidden');
            } else {
                document.getElementById('totalAmount').textContent = 'Rp 0';
                document.getElementById('stepsContainer').classList.add('hidden');
            }
            
            updateFormState();
        }

        function setAmount(amount) {
            document.getElementById('nominalInput').value = amount.toString();
            updateAmounts();
        }

        function updateFormState() {
            const submitBtn = document.getElementById('submitBtn');
            const input = document.getElementById('nominalInput').value.replace(/\./g, '');
            const amount = parseInt(input) || 0;
            
            if (amount >= 1000 && qrGenerated && paymentProofUploaded) {
                submitBtn.disabled = false;
            } else {
                submitBtn.disabled = true;
            }
        }

        function updateSteps() {
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            const step3 = document.getElementById('step3');

            if (qrGenerated) {
                step1.classList.add('step-active');
                step2.classList.remove('text-gray-400');
                step2.classList.add('step-active');
            }

            if (paymentProofUploaded) {
                step3.classList.remove('text-gray-400');
                step3.classList.add('step-active');
            }
        }

        function charCodeAt(str, index) {
            return str.charCodeAt(index);
        }

        function ConvertCRC16(str) {
            let crc = 0xFFFF;
            const strlen = str.length;
            
            for (let c = 0; c < strlen; c++) {
                crc ^= charCodeAt(str, c) << 8;
                for (let i = 0; i < 8; i++) {
                    if (crc & 0x8000) {
                        crc = (crc << 1) ^ 0x1021;
                    } else {
                        crc = crc << 1;
                    }
                }
            }
            
            let hex = (crc & 0xFFFF).toString(16).toUpperCase();
            return hex.length === 3 ? '0' + hex : hex.padStart(4, '0');
        }

        function generateQRCode(text, amount) {
            const qr = qrcode(0, 'M');
            qr.addData(text);
            qr.make();
            
            Swal.fire({
                title: '<div class="text-xl font-bold text-gray-800">QR Code Pembayaran</div>',
                html: `
                    <div class="text-center">
                        <p class="text-gray-600 mb-4">Scan QR Code di bawah dengan aplikasi pembayaran Anda</p>
                        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg p-4 mb-4">
                            <div class="flex justify-between items-center text-sm mb-2">
                                <span class="text-gray-600">Total Pembayaran:</span>
                                <span class="font-bold text-green-600 text-lg">${formatRupiah(amount)}</span>
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border-2 border-gray-200 inline-block">
                            <img src="${qr.createDataURL(8)}" alt="QR Code Pembayaran" class="mx-auto" style="max-width: 280px;">
                        </div>
                        <div class="mt-4 p-3 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                            <p class="text-sm text-blue-700">
                                <i class="fas fa-info-circle mr-1"></i>
                                Setelah melakukan pembayaran, kembali ke halaman ini dan upload bukti transfer.
                            </p>
                        </div>
                    </div>
                `,
                width: '500px',
                showConfirmButton: true,
                confirmButtonText: '<i class="fas fa-check mr-2"></i>Sudah Bayar, Lanjut Upload',
                confirmButtonColor: '#059669',
                background: '#f8fafc',
                customClass: {
                    popup: 'swal-custom-popup',
                    confirmButton: 'swal-confirm-btn'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    qrGenerated = true;
                    document.getElementById('uploadSection').classList.remove('hidden');
                    updateSteps();
                    updateFormState();
                    
                    // Scroll to upload section
                    document.getElementById('uploadSection').scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'center'
                    });
                }
            });
        }

        // Generate QR Button Handler - FIXED VERSION (using the working algorithm from your first JS)
        document.getElementById('generateQRBtn').addEventListener('click', function() {
            const input = document.getElementById('nominalInput').value.replace(/\./g, '');
            const amount = parseInt(input) || 0;
            
            if (amount < 1000) {
                Swal.fire({
                    title: 'Nominal Minimum',
                    text: 'Minimum top up adalah Rp 1.000',
                    icon: 'warning',
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }
            
            if (amount > 5000000) {
                Swal.fire({
                    title: 'Nominal Maximum',
                    text: 'Maximum top up adalah Rp 5.000.000',
                    icon: 'warning',
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }
            
            // Generate QRIS with amount - USING THE CORRECT ALGORITHM FROM YOUR FIRST JS
            let qris = STATIC_QRIS.slice(0, -4); // Remove last 4 chars (CRC)
            let step1 = qris.replace("010211", "010212"); // Change to dynamic
            let step2 = step1.split("5802ID"); // Split at country code
            let uang = "54" + amount.toString().length.toString().padStart(2, '0') + amount.toString();
            uang += "5802ID";
            const fix = step2[0].trim() + uang + step2[1].trim();
            const finalQR = fix + ConvertCRC16(fix);
            
            generateQRCode(finalQR, amount);
        });

        // Enable submit button when file is selected
        document.getElementById('payment_proof').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const submitBtn = document.getElementById('submitBtn');
            
            if (file) {
                paymentProofUploaded = true;
                
                // Show preview
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('previewImage').src = e.target.result;
                        document.getElementById('fileName').textContent = file.name;
                        document.getElementById('uploadPreview').classList.remove('hidden');
                        document.getElementById('uploadPlaceholder').classList.add('hidden');
                    };
                    reader.readAsDataURL(file);
                }
                
                updateSteps();
                updateFormState();
            } else {
                paymentProofUploaded = false;
                document.getElementById('uploadPreview').classList.add('hidden');
                document.getElementById('uploadPlaceholder').classList.remove('hidden');
                updateFormState();
            }
        });

        // Form submission
        document.getElementById('topupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const input = document.getElementById('nominalInput').value.replace(/\./g, '');
            const amount = parseInt(input) || 0;
            
            if (amount < 1000) {
                Swal.fire({
                    title: 'Nominal Minimum',
                    text: 'Minimum top up adalah Rp 1.000',
                    icon: 'warning',
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }
            
            if (!paymentProofUploaded) {
                Swal.fire({
                    title: 'Bukti Transfer Diperlukan',
                    text: 'Silakan upload bukti transfer',
                    icon: 'warning',
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }
            
            // Show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            
            // Create FormData
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('amount', amount);
            formData.append('method', 'qris');
            formData.append('payment_proof', document.getElementById('payment_proof').files[0]);
            
            fetch('{{ route("topup.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message || 'Top up berhasil! Saldo Anda telah ditambahkan.',
                        icon: 'success',
                        confirmButtonColor: '#059669',
                        confirmButtonText: 'Lihat Riwayat'
                    }).then(() => {
                        window.location.href = '{{ route("topup.index") }}';
                    });
                } else {
                    let errorMsg = 'Terjadi kesalahan saat memproses top up.';
                    if (data.errors) {
                        errorMsg = Object.values(data.errors).flat().join(', ');
                    } else if (data.message) {
                        errorMsg = data.message;
                    }
                    
                    Swal.fire({
                        title: 'Error',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonColor: '#dc2626'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Terjadi kesalahan jaringan. Silakan coba lagi.',
                    icon: 'error',
                    confirmButtonColor: '#dc2626'
                });
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane mr-2"></i>Kirim & Dapatkan Saldo Otomatis';
            });
        });

        // Amount input formatting
        document.getElementById('nominalInput').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\./g, '');
            if (value) {
                value = parseInt(value).toString();
                e.target.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            updateAmounts();
        });

        // Initialize
        updateAmounts();
    </script>
</x-app-layout>