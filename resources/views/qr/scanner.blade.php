<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pemindai Profil & Riwayat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="text-center mb-4">
                        <div id="qr-reader" class="mx-auto border dark:border-gray-600 rounded-lg" style="width: 100%; max-width: 400px;"></div>
                        <div id="scanner-status" class="mt-2 text-sm text-gray-500">Arahkan kamera ke QR Code</div>
                    </div>
                    
                    <div id="result-container" class="hidden mt-6 space-y-6">
                        </div>

                    <div class="mt-6 flex justify-center">
                        <button id="start-scanner-btn" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-md shadow-md transition-all duration-300">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Mulai Pindai
                        </button>
                    </div>
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

                const qrCodepaidCallback = (decodedText, decodedResult) => {
                    // Hentikan pemindai setelah berhasil
                    html5QrCode.stop().then(ignore => {
                        scannerStatus.innerHTML = `<span class="text-green-500 font-semibold">Berhasil! Memproses data...</span>`;
                        startButton.disabled = false;
                        startButton.innerHTML = 'Mulai Pindai Lagi';
                        handleScanResult(decodedText);
                    }).catch(err => console.error("Gagal menghentikan pemindai.", err));
                };

                const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                startButton.addEventListener('click', () => {
                    resultContainer.classList.add('hidden');
                    resultContainer.innerHTML = '';
                    scannerStatus.textContent = "Memulai kamera...";
                    startButton.disabled = true;
                    startButton.textContent = "Memindai...";

                    html5QrCode.start({ facingMode: "environment" }, config, qrCodepaidCallback)
                        .catch(err => {
                            scannerStatus.innerHTML = `<span class="text-red-500">Error: ${err}</span>`;
                            startButton.disabled = false;
                            startButton.textContent = 'Coba Lagi';
                        });
                });

                function handleScanResult(decodedText) {
                    // <<< PERUBAIKAN: Panggil endpoint baru
                    fetch('{{ route("api.qr.profile") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ qr_data: decodedText })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw new Error(err.message || 'Network response was not ok') });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.paid) {
                            displayProfileAndHistory(data.data);
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        displayError(error.message);
                    });
                }

                function displayError(message) {
                    resultContainer.innerHTML = `
                        <div class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 text-red-700 dark:text-red-200 p-4">
                            <h5 class="font-bold">Gagal Memuat Data</h5>
                            <p class="mt-2">${message}</p>
                        </div>
                    `;
                    resultContainer.classList.remove('hidden');
                }

                // <<< BARU: Fungsi untuk menampilkan semua data
                function displayProfileAndHistory(data) {
                    const { profile, transactions, orders } = data;

                    // 1. Template untuk Profil
                    const profileHtml = `
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow">
                            <h4 class="text-xl font-bold mb-3 border-b pb-2 dark:border-gray-600">Profil Pengguna</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div><strong>Nama:</strong> ${profile.full_name}</div>
                                <div><strong>ID Siswa:</strong> ${profile.student_id || '-'}</div>
                                <div><strong>Peran:</strong> ${profile.role}</div>
                                <div class="font-bold text-lg"><strong>Saldo:</strong> ${profile.formatted_balance}</div>
                            </div>
                        </div>
                    `;

                    // 2. Template untuk Transaksi
                    let transactionsHtml = `
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow">
                            <h4 class="text-xl font-bold mb-3 border-b pb-2 dark:border-gray-600">5 Transaksi Terakhir</h4>
                    `;
                    if (transactions.length > 0) {
                        transactionsHtml += '<ul class="space-y-2">';
                        transactions.forEach(tx => {
                            const amountClass = ['payment', 'expense'].includes(tx.type) ? 'text-red-500' : 'text-green-500';
                            const sign = ['payment', 'expense'].includes(tx.type) ? '-' : '+';
                            transactionsHtml += `
                                <li class="flex justify-between items-center text-sm">
                                    <span>${tx.description} <br> <small class="text-gray-500">${new Date(tx.created_at).toLocaleString('id-ID')}</small></span>
                                    <span class="font-mono ${amountClass}">${sign}Rp ${new Intl.NumberFormat('id-ID').format(tx.amount)}</span>
                                </li>
                            `;
                        });
                        transactionsHtml += '</ul>';
                    } else {
                        transactionsHtml += '<p class="text-sm text-gray-500">Tidak ada riwayat transaksi.</p>';
                    }
                    transactionsHtml += '</div>';
                    
                    // 3. Template untuk Pesanan
                    let ordersHtml = `
                        <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow">
                             <h4 class="text-xl font-bold mb-3 border-b pb-2 dark:border-gray-600">5 Pembelian Terakhir</h4>
                    `;
                    if (orders.length > 0) {
                        ordersHtml += '<div class="space-y-3">';
                        orders.forEach(order => {
                             ordersHtml += `
                                <div class="text-sm">
                                    <div class="flex justify-between font-semibold">
                                        <span>Pesanan #${order.order_id.substring(0, 8)}</span>
                                        <span>Rp ${new Intl.NumberFormat('id-ID').format(order.total_price)}</span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <span>Penjual: ${order.admin.full_name}</span> |
                                        <span>Status: ${order.status}</span>
                                    </div>
                                </div>
                             `;
                        });
                        ordersHtml += '</div>';
                    } else {
                        ordersHtml += '<p class="text-sm text-gray-500">Tidak ada riwayat pembelian.</p>';
                    }
                     ordersHtml += '</div>';


                    resultContainer.innerHTML = profileHtml + transactionsHtml + ordersHtml;
                    resultContainer.classList.remove('hidden');
                }
            });
        </script>
    @endpush
</x-app-layout>