<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class XenditService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('xendit.api_key', 'xnd_development_USD0reg3UFOYnJN8EukhfHg8sUTrar8UadDlYMd87uG2PSAKpFqbucZQlLIbC');
        $this->baseUrl = config('xendit.base_url', 'https://api.xendit.co');
    }

    /**
     * Create QRIS payment
     */
    public function createQRIS($amount, $externalId, $description = null)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->post($this->baseUrl . '/qr_codes', [
                    'external_id' => $externalId,
                    'type' => 'DYNAMIC',
                    'callback_url' => route('xendit.callback'),
                    'amount' => $amount,
                    'description' => $description ?? 'Top up saldo',
                    'currency' => 'IDR'
                ]);

            if ($response->paidful()) {
                $data = $response->json();
                Log::info('Xendit QRIS created paidfully', $data);
                return [
                    'paid' => true,
                    'data' => $data
                ];
            } else {
                Log::error('Xendit QRIS creation failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return [
                    'paid' => false,
                    'message' => 'Failed to create QRIS payment',
                    'error' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Xendit QRIS creation exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'paid' => false,
                'message' => 'Error creating QRIS payment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get QRIS payment status
     */
    public function getQRISStatus($qrId)
    {
        try {
            $response = Http::withBasicAuth($this->apiKey, '')
                ->get($this->baseUrl . '/qr_codes/' . $qrId);

            if ($response->paidful()) {
                return [
                    'paid' => true,
                    'data' => $response->json()
                ];
            } else {
                return [
                    'paid' => false,
                    'message' => 'Failed to get QRIS status',
                    'error' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Xendit get QRIS status exception', [
                'message' => $e->getMessage(),
                'qr_id' => $qrId
            ]);
            return [
                'paid' => false,
                'message' => 'Error getting QRIS status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify callback signature (optional but recommended for production)
     */
    public function verifyCallback($headers, $body)
    {
        // For development/testing, we'll skip signature verification
        // In production, you should verify the webhook signature
        return true;
    }

    /**
     * Handle webhook callback
     */
    public function handleCallback($data)
    {
        try {
            Log::info('Xendit callback received', $data);

            if (isset($data['status']) && $data['status'] === 'COMPLETED') {
                return [
                    'paid' => true,
                    'external_id' => $data['external_id'] ?? null,
                    'amount' => $data['amount'] ?? null,
                    'payment_id' => $data['id'] ?? null
                ];
            }

            return [
                'paid' => false,
                'message' => 'Payment not completed',
                'status' => $data['status'] ?? 'unknown'
            ];
        } catch (\Exception $e) {
            Log::error('Xendit callback handling exception', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            return [
                'paid' => false,
                'message' => 'Error handling callback: ' . $e->getMessage()
            ];
        }
    }
}