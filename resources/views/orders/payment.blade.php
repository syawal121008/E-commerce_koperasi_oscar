{{-- resources/views/orders/payment.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pembayaran QRIS') }}
            </h2>
            <a href="{{ route('orders.show', $order->order_id) }}" class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Detail Pesanan
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- QRIS Payment Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold text-gray-800 mb-2">
                                <i class="fas fa-qrcode text-purple-500 mr-2"></i>
                                Scan QR Code untuk Pembayaran
                            </h3>
                            <p class="text-gray-600">Gunakan aplikasi e-wallet untuk scan QR code di bawah ini</p>
                        </div>

                        <!-- QR Code Container -->
                        <div class="text-center mb-6">
                            <div id="qr-code-container" class="inline-block bg-white p-6 rounded-lg shadow-lg border-2 border-purple-200">
                                <div id="qr-code">
                                    <!-- QR Code akan digenerate di sini -->
                                </div>
                            </div>
                        </div>

                        <!-- Payment Amount -->
                        <div class="text-center mb-6">
                            <p class="text-sm text-gray-600 mb-1">Total Pembayaran:</p>
                            <p class="text-3xl font-bold text-purple-600">
                                Rp {{ number_format($order->total_price, 0, ',', '.') }}
                            </p>
                        </div>

                        <!-- Timer -->
                        <div class="text-center mb-6">
                            <div id="qr-timer" class="text-lg font-semibold text-red-600 mb-2"></div>
                            <button type="button" id="refresh-qr" class="hidden bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-refresh mr-2"></i>Generate QR Baru
                            </button>
                        </div>

                        <!-- Payment Status -->
                        <div id="payment-status" class="text-center mb-6">
                            <div class="flex items-center justify-center space-x-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-purple-600"></div>
                                <span class="text-gray-600">Menunggu pembayaran...</span>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <h4 class="font-semibold text-gray-800 mb-3">Cara Pembayaran:</h4>
                            <div class="space-y-2 text-sm text-gray-600">
                                <div class="flex items-start space-x-2">
                                    <span class="bg-purple-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-semibold">1</span>
                                    <span>Buka aplikasi e-wallet (GoPay, OVO, DANA, ShopeePay, dll)</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="bg-purple-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-semibold">2</span>
                                    <span>Pilih menu "Scan QR" atau "QRIS"</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="bg-purple-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-semibold">3</span>
                                    <span>Arahkan kamera ke QR code di atas</span>
                                </div>
                                <div class="flex items-start space-x-2">
                                    <span class="bg-purple-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-semibold">4</span>
                                    <span>Konfirmasi pembayaran di aplikasi Anda</span>
                                </div>
                            </div>
                        </div>

                        <!-- Simulate Payment Button (for testing) -->
                        @if(config('app.env') === 'local')
                        <div class="text-center">
                            <button id="simulate-payment" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                                <i class="fas fa-play mr-2"></i>Simulasi Pembayaran Berhasil
                            </button>
                            <p class="text-xs text-gray-500 mt-1">Tombol ini hanya untuk testing</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Order Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-6">Detail Pesanan</h3>
                        
                        <!-- Order Info -->
                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Order ID:</span>
                                <span class="font-semibold">#{{ $order->order_id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Tanggal:</span>
                                <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div class="border-t pt-4 mb-6">
                            <h4 class="font-semibold text-gray-800 mb-4">Produk</h4>
                            <div class="flex items-start space-x-4">
                                <img src="{{ $order->product->image ? asset('storage/' . $order->product->image) : 'https://via.placeholder.com/80x80?text=No+Image' }}" 
                                     alt="{{ $order->product->name }}" 
                                     class="w-16 h-16 object-cover rounded-lg">
                                <div class="flex-grow">
                                    <h5 class="font-semibold text-gray-800">{{ $order->product->name }}</h5>
                                    <p class="text-gray-600 mt-1">
                                        Rp {{ number_format($order->product->price, 0, ',', '.') }} x {{ $order->quantity }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-gray-800">
                                        Rp {{ number_format($order->total_price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="border-t pt-4">
                            <h4 class="font-semibold text-gray-800 mb-4">Alamat Pengiriman</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p class="font-semibold text-gray-800">{{ $order->recipient_name }}</p>
                                <p>{{ $order->recipient_phone }}</p>
                                <p>{{ $order->shipping_address }}</p>
                            </div>
                        </div>

                        @if($order->notes)
                        <div class="border-t pt-4 mt-4">
                            <h4 class="font-semibold text-gray-800 mb-2">Catatan</h4>
                            <p class="text-sm text-gray-600">{{ $order->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js"></script>

    <script>
    let qrTimer;
    let qrTimeLeft = 900; // 15 minutes in seconds
    let paymentCheckInterval;

    document.addEventListener('DOMContentLoaded', function() {
        generateQRCode();
        startPaymentStatusCheck();

        // Refresh QR button
        document.getElementById('refresh-qr').addEventListener('click', function() {
            generateQRCode();
            this.classList.add('hidden');
        });

        // Simulate payment button (for testing)
        @if(config('app.env') === 'local')
        document.getElementById('simulate-payment').addEventListener('click', function() {
            simulatePayment();
        });
        @endif
    });

    function generateQRCode() {
        const qrCodeElement = document.getElementById('qr-code');
        const orderId = {{ $order->order_id }};
        const amount = {{ $order->total_price }};
        
        // Clear previous QR code
        qrCodeElement.innerHTML = '';
        
        // Generate random transaction ID
        const transactionId = 'QRIS' + Date.now() + Math.random().toString(36).substr(2, 9);
        
        // Create QRIS data
        const qrisData = {
            merchant_name: '{{ config('app.name', 'Toko Online') }}',
            merchant_id: 'ID12345678901234567',
            amount: amount,
            currency: 'IDR',
            order_id: orderId,
            transaction_ref: transactionId,
            timestamp: new Date().toISOString(),
            expired_at: new Date(Date.now() + 15 * 60 * 1000).toISOString()
        };
        
        // Generate QR Code
        QRCode.toCanvas(qrCodeElement, JSON.stringify(qrisData), {
            width: 250,
            height: 250,
            margin: 2,
            color: {
                dark: '#7C3AED',
                light: '#FFFFFF'
            }
        }, function(error) {
            if (error) {
                console.error('Error generating QR code:', error);
                qrCodeElement.innerHTML = '<div class="text-red-500 text-sm p-4">Error generating QR code</div>';
            } else {
                startQRTimer();
                
                // Reset QR code appearance
                const container = document.getElementById('qr-code-container');
                container.style.opacity = '1';
                container.style.filter = 'none';
            }
        });
    }

    function startQRTimer() {
        clearQRTimer();
        qrTimeLeft = 900; // Reset to 15 minutes
        
        qrTimer = setInterval(function() {
            const minutes = Math.floor(qrTimeLeft / 60);
            const seconds = qrTimeLeft % 60;
            
            document.getElementById('qr-timer').innerHTML = 
                `<i class="fas fa-clock mr-1"></i>QR Code berlaku: ${minutes}:${seconds.toString().padStart(2, '0')}`;
            
            qrTimeLeft--;
            
            if (qrTimeLeft < 0) {
                clearQRTimer();
                document.getElementById('qr-timer').innerHTML = 
                    '<i class="fas fa-exclamation-triangle mr-1"></i>QR Code telah expired';
                document.getElementById('refresh-qr').classList.remove('hidden');
                
                // Disable QR code visually
                const container = document.getElementById('qr-code-container');
                container.style.opacity = '0.5';
                container.style.filter = 'grayscale(100%)';
            }
        }, 1000);
    }

    function clearQRTimer() {
        if (qrTimer) {
            clearInterval(qrTimer);
            qrTimer = null;
        }
    }

    function startPaymentStatusCheck() {
        paymentCheckInterval = setInterval(function() {
            checkPaymentStatus();
        }, 5000); // Check every 5 seconds
    }

    function checkPaymentStatus() {
        fetch(`/api/v1/payments/{{ $payment->payment_id }}/status`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Authorization': 'Bearer {{ auth()->user()->createToken("payment-check")->plainTextToken ?? "" }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.payment_status === 'paid') {
                // Payment successful
                clearInterval(paymentCheckInterval);
                clearQRTimer();
                
                updatePaymentStatus('success', 'Pembayaran berhasil!');
                
                // Redirect after 3 seconds
                setTimeout(function() {
                    window.location.href = '/orders/{{ $order->order_id }}';
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error checking payment status:', error);
        });
    }

    function updatePaymentStatus(type, message) {
        const statusElement = document.getElementById('payment-status');
        
        if (type === 'success') {
            statusElement.innerHTML = `
                <div class="flex items-center justify-center space-x-2 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                    <span class="font-semibold">${message}</span>
                </div>
                <p class="text-sm text-gray-600 mt-2">Mengalihkan ke halaman detail pesanan...</p>
            `;
        } else if (type === 'error') {
            statusElement.innerHTML = `
                <div class="flex items-center justify-center space-x-2 text-red-600">
                    <i class="fas fa-times-circle text-xl"></i>
                    <span class="font-semibold">${message}</span>
                </div>
            `;
        }
    }

    @if(config('app.env') === 'local')
    function simulatePayment() {
        // Disable button
        const button = document.getElementById('simulate-payment');
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        
        fetch(`/qris/simulate-payment/{{ $payment->payment_id }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePaymentStatus('success', 'Pembayaran simulasi berhasil!');
                
                // Stop checking and timer
                clearInterval(paymentCheckInterval);
                clearQRTimer();
                
                // Redirect after 2 seconds
                setTimeout(function() {
                    window.location.href = '/orders/{{ $order->order_id }}';
                }, 2000);
            } else {
                updatePaymentStatus('error', data.message || 'Simulasi gagal');
                
                // Re-enable button
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-play mr-2"></i>Simulasi Pembayaran Berhasil';
            }
        })
        .catch(error => {
            console.error('Error simulating payment:', error);
            updatePaymentStatus('error', 'Error dalam simulasi pembayaran');
            
            // Re-enable button
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-play mr-2"></i>Simulasi Pembayaran Berhasil';
        });
    }
    @endif

    // Cleanup intervals when leaving page
    window.addEventListener('beforeunload', function() {
        if (paymentCheckInterval) {
            clearInterval(paymentCheckInterval);
        }
        clearQRTimer();
    });
    </script>
</x-app-layout>