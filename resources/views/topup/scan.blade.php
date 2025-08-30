<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Top Up via Pemindai QR') }}
            </h2>
        </div>
    </x-slot>

    <div id="receipt-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-2xl rounded-2xl bg-white dark:bg-gray-800">
            <div class="text-center mb-6">
                <div class="w-20 h-20 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Top Up Berhasil!</h3>
                <p class="text-gray-600 dark:text-gray-400">Saldo telah berhasil ditambahkan</p>
            </div>

            <div id="modal-transaction-info" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 mb-6">
                </div>

            <div id="auto-redirect-info" class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700 mb-4">
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    Otomatis menampilkan bukti dalam <span id="countdown">5</span> detik
                </p>
                <button onclick="cancelAutoRedirect()" class="text-xs text-blue-600 dark:text-blue-400 underline hover:no-underline ml-2">
                    Batalkan
                </button>
            </div>

            <div class="space-y-4">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 text-center">
                    Mau lihat bukti transaksi?
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <button onclick="viewReceiptNow()"
                            class="flex items-center justify-center px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Lihat Bukti
                    </button>

                    <button onclick="downloadPDFNow()"
                            class="flex items-center justify-center px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download PDF
                    </button>

                    <button onclick="continueScanning()"
                            class="flex items-center justify-center px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Scan Lagi
                    </button>
                </div>
            </div>

            <div class="text-center mt-4">
                <button onclick="closeModal()" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    Tutup tanpa melihat bukti
                </button>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Pengaturan Scanner</h3>
                        <button id="toggle-settings" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div id="settings-content" class="hidden space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Auto Redirect ke Bukti
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Otomatis buka bukti setelah topup berhasil
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="auto-redirect" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                <select id="redirect-delay" class="text-sm border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-800">
                                    <option value="3">3 detik</option>
                                    <option value="5" selected>5 detik</option>
                                    <option value="10">10 detik</option>
                                    <option value="0">Langsung</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Notifikasi Suara
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Mainkan suara saat topup berhasil
                                </p>
                            </div>
                            <input type="checkbox" id="sound-notification" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="text-center mb-4">
                        <div id="qr-reader" class="mx-auto border dark:border-gray-600 rounded-lg" style="width: 100%; max-width: 400px;"></div>
                        <div id="scanner-status" class="mt-2 text-sm text-gray-500">Arahkan kamera ke Kode QR</div>
                    </div>

                    <div class="mt-6 flex justify-center">
                        <button id="start-scanner-btn" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-md shadow-md transition-all duration-300">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Mulai Pindai
                        </button>
                    </div>

                    <div id="result-container" class="hidden mt-6 space-y-6">
                        </div>

                    <div id="alert-container" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const html5QrCode = new Html5Qrcode("qr-reader");
                const resultContainer = document.getElementById("result-container");
                const startButton = document.getElementById("start-scanner-btn");
                const scannerStatus = document.getElementById("scanner-status");
                const alertContainer = document.getElementById("alert-container");
                const toggleSettingsBtn = document.getElementById("toggle-settings");
                const settingsContent = document.getElementById("settings-content");

                // CSRF Token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                // Settings toggle
                toggleSettingsBtn.addEventListener('click', () => {
                    settingsContent.classList.toggle('hidden');
                });

                const qrCodepaidCallback = (decodedText, decodedResult) => {
                    // Stop scanner after paid
                    html5QrCode.stop().then(ignore => {
                        scannerStatus.innerHTML = `<span class="text-green-500 font-semibold">Sukses! Memproses data...</span>`;
                        startButton.disabled = false;
                        startButton.innerHTML = `
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Pindai Lagi
                        `;
                        handleScanResult(decodedText);
                    }).catch(err => console.error("Gagal menghentikan pemindai.", err));
                };

                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                startButton.addEventListener('click', () => {
                    resultContainer.classList.add('hidden');
                    resultContainer.innerHTML = '';
                    clearAlerts();
                    scannerStatus.textContent = "Membuka kamera...";
                    startButton.disabled = true;
                    startButton.textContent = "Memindai...";

                    html5QrCode.start({ facingMode: "environment" }, config, qrCodepaidCallback)
                        .catch(err => {
                            scannerStatus.innerHTML = `<span class="text-red-500">Gagal: ${err}</span>`;
                            startButton.disabled = false;
                            startButton.innerHTML = `
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Coba Lagi
                            `;
                        });
                });

                function handleScanResult(decodedText) {
                    // Call QR profile endpoint
                    fetch('{{ route("api.qr.profile") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ qr_data: decodedText })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => {
                                throw new Error(err.message || 'Respons jaringan tidak baik')
                            });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.paid) {
                            displayCustomerAndTopupForm(data.data);
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        displayError(error.message);
                    });
                }

                function displayError(message) {
                    showAlert(message, 'error');
                    resultContainer.classList.add('hidden');
                }

                function displayCustomerAndTopupForm(data) {
                    const { profile } = data;
                    
                    // --- CHANGED: Role translation logic ---
                    const roleMap = {
                        customer: 'Siswa',
                        admin: 'Admin Koperasi',
                        guru: 'Kepala Koperasi'
                    };
                    const roleName = roleMap[profile.role.toLowerCase()] || (profile.role.charAt(0).toUpperCase() + profile.role.slice(1));

                    const customerAndFormHtml = `
                       <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
    <h4 class="text-2xl font-bold mb-5 text-gray-800 dark:text-gray-100 border-b pb-3">
        Informasi Siswa
    </h4>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-gray-700 dark:text-gray-200">
        <div>
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Nama</span>
            <span class="text-lg font-semibold">${profile.full_name}</span>
        </div>
        <div>
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">NIS/NIP</span>
            <span class="text-lg font-semibold">${profile.student_id || '-'}</span>
        </div>
        <div>
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Peran</span>
            <span class="text-lg font-semibold">
                ${roleName}
            </span>
        </div>
        <div>
            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Saldo</span>
            <span class="text-xl font-bold bg-gradient-to-r from-green-500 to-emerald-600 text-white px-3 py-1 rounded-lg shadow">
                ${profile.formatted_balance}
            </span>
        </div>
    </div>
</div>

<div class="relative bg-white dark:bg-gray-900 p-8 rounded-2xl shadow-xl mt-8 border border-gray-200 dark:border-gray-700 hover:shadow-2xl transition-all duration-300">
    <h4 class="text-2xl font-bold mb-6 text-gray-900 dark:text-gray-100 relative">
        Isi Saldo
        <span class="absolute -bottom-2 left-0 w-20 h-1 bg-blue-600 rounded-full"></span>
    </h4>

    <form id="form-topup" class="space-y-6">
        <input type="hidden" id="customer-user-id" name="user_id" value="${profile.user_id}">
        
        <div>
            <label for="amount" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Jumlah (Rp)
            </label>
            <input 
                type="number" 
                id="amount" 
                name="amount" 
                min="1000" 
                max="500000" 
                step="1000"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100 text-lg transition-all"
                placeholder="Masukkan Jumlah (min Rp 1.000)"
                required
            >
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Minimum: Rp 1.000 | Maksimum: Rp 500.000</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4">
            <button 
                type="submit" 
                class="flex-1 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-transform duration-300 hover:scale-[1.02] active:scale-95"
            >
                Proses Top Up
            </button>
            
            <button 
                type="button" 
                id="reset-form"
                class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-xl shadow-md transition-transform duration-300 hover:scale-[1.02] active:scale-95"
            >
                Atur Ulang
            </button>
        </div>
    </form>
</div>
                    `;

                    resultContainer.innerHTML = customerAndFormHtml;
                    resultContainer.classList.remove('hidden');

                    // Add event listeners for the new form
                    setupTopupForm();
                    clearAlerts();
                }

                function setupTopupForm() {
                    const topupForm = document.getElementById('form-topup');
                    const resetFormBtn = document.getElementById('reset-form');
                    const amountInput = document.getElementById('amount');

                    // Format number input
                    amountInput.addEventListener('input', function(e) {
                        const value = e.target.value.replace(/\D/g, '');
                        if (value) {
                            e.target.value = parseInt(value);
                        }
                    });

                    // Reset form handler
                    resetFormBtn.addEventListener('click', resetForm);

                    // Form submit handler
                    topupForm.addEventListener('submit', async function(event) {
                        event.preventDefault();
                        
                        const formData = new FormData(event.target);
                        const data = Object.fromEntries(formData.entries());

                        // Disable submit button
                        const submitBtn = event.target.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = `
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        `;

                        try {
                            const response = await fetch('{{ route("topup.storeByQr") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify(data)
                            });

                            const result = await response.json();

                            if (result.paid) {
                                // Play paid sound if enabled
                                if (document.getElementById('sound-notification').checked) {
                                    playpaidSound();
                                }

                                // Show receipt modal with data
                                showReceiptModal(result.data);
                                
                                // Reset form after showing modal
                                setTimeout(() => {
                                    resetForm();
                                }, 1000);
                            } else {
                                showAlert(result.message || 'Gagal memproses top up', 'error');
                            }
                        } catch (error) {
                            console.error('Error processing topup:', error);
                            showAlert('Gagal memproses top up', 'error');
                        } finally {
                            // Re-enable submit button
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    });
                }

                function resetForm() {
                    // Hide result container
                    resultContainer.classList.add('hidden');
                    resultContainer.innerHTML = '';
                    
                    // Clear alerts
                    clearAlerts();
                    
                    // Reset scanner status
                    scannerStatus.textContent = "Arahkan kamera ke Kode QR";
                }

                function showAlert(message, type) {
                    const alertClass = type === 'paid'
                        ? 'bg-green-100 dark:bg-green-900/20 border-green-500 text-green-700 dark:text-green-300'
                        : 'bg-red-100 dark:bg-red-900/20 border-red-500 text-red-700 dark:text-red-300';
                    
                    const iconSvg = type === 'paid'
                        ? `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>`
                        : `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>`;
                    
                    const alertHtml = `
                        <div class="border-l-4 ${alertClass} p-4 rounded-r-lg shadow-md">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    ${iconSvg}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium">${message}</p>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    alertContainer.innerHTML = alertHtml;
                    
                    // Auto-hide error messages after 5 seconds
                    if (type === 'error') {
                        setTimeout(() => {
                            clearAlerts();
                        }, 5000);
                    }
                }

                function clearAlerts() {
                    alertContainer.innerHTML = '';
                }

                // Play paid sound
                function playpaidSound() {
                    try {
                        // Create audio context for paid sound
                        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                        const oscillator = audioContext.createOscillator();
                        const gainNode = audioContext.createGain();

                        oscillator.connect(gainNode);
                        gainNode.connect(audioContext.destination);

                        oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                        oscillator.frequency.setValueAtTime(1000, audioContext.currentTime + 0.1);
                        oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.2);

                        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

                        oscillator.start(audioContext.currentTime);
                        oscillator.stop(audioContext.currentTime + 0.5);
                    } catch (e) {
                        console.log('Could not play paid sound:', e);
                    }
                }
            });

            // Modal functions
            let currentTopupData = null;
            let autoRedirectTimer = null;
            let countdownTimer = null;

            function showReceiptModal(topupData) {
                currentTopupData = topupData;
                
                // Populate transaction info
                const transactionInfo = document.getElementById('modal-transaction-info');
                transactionInfo.innerHTML = `
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Siswa</span>
                            <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">${topupData.customer_name}</span>
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah</span>
                            <span class="text-xl font-bold text-green-600 dark:text-green-400">${topupData.amount}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Referensi</span>
                            <span class="text-lg font-mono font-semibold text-blue-600 dark:text-blue-400">${topupData.payment_reference}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Waktu</span>
                            <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">${topupData.created_at}</span>
                        </div>
                    </div>
                `;
                
                // Show modal
                document.getElementById('receipt-modal').classList.remove('hidden');
                
                // Start auto redirect countdown if enabled
                if (document.getElementById('auto-redirect').checked) {
                    startAutoRedirect();
                }
            }

            function startAutoRedirect() {
                const delaySeconds = parseInt(document.getElementById('redirect-delay').value);
                let countdown = delaySeconds;
                const countdownElement = document.getElementById('countdown');
                
                // Update initial countdown
                countdownElement.textContent = countdown;
                
                if (delaySeconds === 0) {
                    viewReceiptNow();
                    return;
                }
                
                countdownTimer = setInterval(() => {
                    countdown--;
                    countdownElement.textContent = countdown;
                    
                    // Add warning class when countdown is low
                    if (countdown <= 2) {
                        countdownElement.classList.add('warning');
                    }
                    
                    if (countdown <= 0) {
                        clearInterval(countdownTimer);
                        viewReceiptNow();
                    }
                }, 1000);

                // Set auto redirect
                autoRedirectTimer = setTimeout(() => {
                    viewReceiptNow();
                }, delaySeconds * 1000);
            }

            function cancelAutoRedirect() {
                if (autoRedirectTimer) {
                    clearTimeout(autoRedirectTimer);
                    autoRedirectTimer = null;
                }
                if (countdownTimer) {
                    clearInterval(countdownTimer);
                    countdownTimer = null;
                }
                
                // Hide auto redirect info
                document.getElementById('auto-redirect-info').style.display = 'none';
            }

            function viewReceiptNow() {
                if (currentTopupData && currentTopupData.receipt_url) {
                    window.open(currentTopupData.receipt_url, '_blank');
                }
                closeModal();
            }

            function downloadPDFNow() {
                if (currentTopupData && currentTopupData.receipt_pdf_url) {
                    window.open(currentTopupData.receipt_pdf_url, '_blank');
                }
                // Don't close modal, user might want to do other actions
            }

            function continueScanning() {
                closeModal();
                // Reset form will be handled by the modal close
            }

            function closeModal() {
                // Clear timers
                if (autoRedirectTimer) {
                    clearTimeout(autoRedirectTimer);
                    autoRedirectTimer = null;
                }
                if (countdownTimer) {
                    clearInterval(countdownTimer);
                    countdownTimer = null;
                }
                
                // Hide modal
                document.getElementById('receipt-modal').classList.add('hidden');
                currentTopupData = null;
                
                // Reset auto redirect info display
                document.getElementById('auto-redirect-info').style.display = 'block';
                document.getElementById('countdown').classList.remove('warning');
                
                // Reset countdown to default
                const delaySeconds = parseInt(document.getElementById('redirect-delay').value);
                document.getElementById('countdown').textContent = delaySeconds;
            }
        </script>
    @endpush

    @push('styles')
    <style>
        /* Modal animation */
        #receipt-modal .relative {
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Countdown animation */
        #countdown {
            display: inline-block;
            min-width: 20px;
            text-align: center;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        #countdown.warning {
            color: #ef4444;
            transform: scale(1.1);
        }

        /* QR Scanner responsive */
        #qr-reader {
            background: #f9fafb;
            border-radius: 0.5rem;
        }

        /* Dark mode adjustments */
        @media (prefers-color-scheme: dark) {
            #qr-reader {
                background: #374151;
            }
        }
    </style>
    @endpush
</x-app-layout>