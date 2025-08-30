<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payments;
use App\Models\Orders;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentsController extends Controller
{
    public function index()
    {
        $payments = Payments::with('order')->orderBy('created_at', 'desc')->get();
        return view('payments.index', compact('payments'));
    }

    public function show($paymentId)
    {
        $payment = Payments::with('order.user')->findOrFail($paymentId);
        return view('payments.show', compact('payment'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,order_id',
            'payment_method' => 'required|in:cod,transfer,qris',
        ]);

        try {
            DB::beginTransaction();

            $payment = Payments::create([
                'order_id' => $request->order_id,
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
            ]);

            // Jika COD, langsung set status order menjadi confirmed
            if ($request->payment_method === 'cod') {
                $order = Orders::find($request->order_id);
                $order->status = 'confirmed';
                $order->save();
            }

            DB::commit();

            return response()->json([
                'paid' => true,
                'payment' => $payment,
                'message' => 'Payment record created paidfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment creation failed: ' . $e->getMessage());
            
            return response()->json([
                'paid' => false,
                'message' => 'Failed to create payment record'
            ], 500);
        }
    }

    public function updateStatus(Request $request, $paymentId)
    {
        $request->validate([
            'status' => 'required|in:pending,paid,failed',
        ]);

        try {
            DB::beginTransaction();

            $payment = Payments::findOrFail($paymentId);
            $payment->payment_status = $request->status;

            if ($request->status === 'paid') {
                $payment->paid_at = now();
                
                // Update order status jika pembayaran berhasil
                $order = Orders::find($payment->order_id);
                $order->status = 'paid';
                $order->save();
            }

            $payment->save();

            DB::commit();

            return response()->json([
                'paid' => true,
                'payment' => $payment,
                'message' => 'Payment status updated paidfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment status update failed: ' . $e->getMessage());
            
            return response()->json([
                'paid' => false,
                'message' => 'Failed to update payment status'
            ], 500);
        }
    }

    public function simulateQrisPayment(Request $request, $paymentId)
    {
        // Simulasi pembayaran QRIS untuk testing
        try {
            DB::beginTransaction();

            $payment = Payments::findOrFail($paymentId);
            
            if ($payment->payment_method !== 'qris') {
                return response()->json([
                    'paid' => false,
                    'message' => 'This payment is not QRIS method'
                ], 400);
            }

            if ($payment->payment_status === 'paid') {
                return response()->json([
                    'paid' => false,
                    'message' => 'Payment already completed'
                ], 400);
            }

            // Simulasi pembayaran berhasil
            $payment->payment_status = 'paid';
            $payment->paid_at = now();
            $payment->save();

            // Update order status
            $order = Orders::find($payment->order_id);
            $order->status = 'paid';
            $order->save();

            DB::commit();

            return response()->json([
                'paid' => true,
                'payment' => $payment,
                'message' => 'QRIS payment completed paidfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('QRIS payment simulation failed: ' . $e->getMessage());
            
            return response()->json([
                'paid' => false,
                'message' => 'Failed to process QRIS payment'
            ], 500);
        }
    }

    public function checkPaymentStatus($paymentId)
    {
        try {
            $payment = Payments::with('order')->findOrFail($paymentId);
            
            return response()->json([
                'paid' => true,
                'payment_status' => $payment->payment_status,
                'order_status' => $payment->order->status,
                'paid_at' => $payment->paid_at,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'paid' => false,
                'message' => 'Payment not found'
            ], 404);
        }
    }

    public function generateQrisCode(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'order_id' => 'required|exists:orders,order_id',
        ]);

        try {
            // Generate unique transaction reference
            $transactionRef = 'QRIS' . time() . rand(1000, 9999);
            
            // Data untuk QR Code (format sederhana)
            $qrisData = [
                'merchant_name' => config('app.name', 'Toko Online'),
                'merchant_id' => 'ID12345678901234567', // ID merchant
                'amount' => $request->amount,
                'currency' => 'IDR',
                'transaction_ref' => $transactionRef,
                'order_id' => $request->order_id,
                'timestamp' => now()->toISOString(),
                'expired_at' => now()->addMinutes(15)->toISOString(),
            ];

            return response()->json([
                'paid' => true,
                'qris_data' => $qrisData,
                'qr_string' => json_encode($qrisData),
                'transaction_ref' => $transactionRef,
                'expired_at' => $qrisData['expired_at'],
            ]);

        } catch (\Exception $e) {
            Log::error('QRIS code generation failed: ' . $e->getMessage());
            
            return response()->json([
                'paid' => false,
                'message' => 'Failed to generate QRIS code'
            ], 500);
        }
    }

    public function processWebhook(Request $request)
    {
        // Untuk integrasi dengan payment gateway seperti Midtrans, Xendit, dll
        try {
            $signature = $request->header('X-Callback-Token');
            $body = $request->getContent();
            
            // Verifikasi signature webhook (sesuai dengan provider)
            // if (!$this->verifyWebhookSignature($signature, $body)) {
            //     return response()->json(['message' => 'Invalid signature'], 400);
            // }

            $data = $request->all();
            
            // Cari payment berdasarkan transaction reference
            $payment = Payments::whereHas('order', function ($query) use ($data) {
                $query->where('order_id', $data['order_id'] ?? null);
            })->first();

            if (!$payment) {
                return response()->json(['message' => 'Payment not found'], 404);
            }

            // Update status berdasarkan response dari payment provider
            switch (strtolower($data['transaction_status'] ?? '')) {
                case 'settlement':
                case 'capture':
                case 'paid':
                    $payment->markAsPaid();
                    break;
                    
                case 'pending':
                    // Status tetap pending
                    break;
                    
                case 'deny':
                case 'cancel':
                case 'expire':
                case 'failure':
                    $payment->markAsFailed();
                    break;
            }

            return response()->json(['message' => 'Webhook processed paidfully']);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to process webhook'
            ], 500);
        }
    }

    public function generatePaymentUrl(Request $request)
    {
        // Untuk redirect ke payment gateway
        $request->validate([
            'payment_id' => 'required|exists:payments,payment_id',
        ]);

        try {
            $payment = Payments::with('order')->findOrFail($request->payment_id);
            
            if ($payment->payment_method === 'qris') {
                // Generate URL untuk halaman QRIS internal
                return response()->json([
                    'paid' => true,
                    'payment_url' => route('orders.payment', $payment->order_id),
                    'type' => 'internal'
                ]);
            } elseif ($payment->payment_method === 'transfer') {
                // Bisa redirect ke halaman instruksi transfer
                return response()->json([
                    'paid' => true,
                    'payment_url' => route('payments.transfer-instruction', $payment->payment_id),
                    'type' => 'internal'
                ]);
            } else {
                // COD tidak perlu URL pembayaran
                return response()->json([
                    'paid' => false,
                    'message' => 'Payment method does not require payment URL'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Generate payment URL failed: ' . $e->getMessage());
            
            return response()->json([
                'paid' => false,
                'message' => 'Failed to generate payment URL'
            ], 500);
        }
    }

    public function cancelPayment(Request $request, $paymentId)
    {
        try {
            DB::beginTransaction();

            $payment = Payments::findOrFail($paymentId);
            
            if ($payment->payment_status === 'paid') {
                return response()->json([
                    'paid' => false,
                    'message' => 'Cannot cancel paid payment'
                ], 400);
            }

            $payment->payment_status = 'failed';
            $payment->save();

            // Update order status
            $order = Orders::find($payment->order_id);
            if ($order) {
                $order->status = 'cancelled';
                $order->save();
                
                // Restore stock jika perlu
                if ($order->product) {
                    $order->product->increment('stock', $order->quantity);
                }
            }

            DB::commit();

            return response()->json([
                'paid' => true,
                'message' => 'Payment cancelled paidfully'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment cancellation failed: ' . $e->getMessage());
            
            return response()->json([
                'paid' => false,
                'message' => 'Failed to cancel payment'
            ], 500);
        }
    }

    public function retryPayment(Request $request, $paymentId)
    {
        try {
            $payment = Payments::findOrFail($paymentId);
            
            if ($payment->payment_status === 'paid') {
                return response()->json([
                    'paid' => false,
                    'message' => 'Payment already completed'
                ], 400);
            }

            // Reset payment status untuk retry
            $payment->payment_status = 'pending';
            $payment->save();

            // Generate new payment URL or QR code
            if ($payment->payment_method === 'qris') {
                return response()->json([
                    'paid' => true,
                    'payment_url' => route('orders.payment', $payment->order_id),
                    'message' => 'Payment retry initiated'
                ]);
            }

            return response()->json([
                'paid' => true,
                'message' => 'Payment retry initiated'
            ]);

        } catch (\Exception $e) {
            Log::error('Payment retry failed: ' . $e->getMessage());
            
            return response()->json([
                'paid' => false,
                'message' => 'Failed to retry payment'
            ], 500);
        }
    }

    // Helper method untuk verifikasi webhook signature
    private function verifyWebhookSignature($signature, $body)
    {
        // Implementasi verifikasi signature sesuai dengan payment provider
        // Contoh untuk Midtrans:
        // $serverKey = config('services.midtrans.server_key');
        // $hashed = hash('sha512', $body . $serverKey);
        // return hash_equals($signature, $hashed);
        
        return true; // Sementara return true untuk development
    }
}