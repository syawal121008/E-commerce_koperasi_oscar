<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class QRController extends Controller
{
    /**
     * Scan QR code and return user info
     */
    public function scan(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR data'
            ], 422);
        }

        try {
            $qrData = json_decode($request->qr_data, true);
            
            if (!$qrData || !isset($qrData['user_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code format'
                ], 400);
            }

            $user = User::find($qrData['user_id']);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'QR code scanned successfully',
                'data' => [
                    'user_id' => $user->user_id,
                    'full_name' => $user->full_name,
                    'student_id' => $user->student_id,
                    'balance' => $user->balance,
                    'role' => $user->role
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to scan QR code',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show QR scanner page
     */
    public function scanner()
    {
        return view('qr.scanner');
    }

    /**
     * Show payment QR scanner
     */
    public function paymentScanner($orderId = null)
    {
        $order = null;
        if ($orderId) {
            $order = Order::with(['items.product', 'customer', 'admin'])
                ->findOrFail($orderId);
        }
        
        return view('qr.payment-scanner', compact('order'));
    }

    /**
     * Get profile and history for topup scanner
     */
    public function getProfileAndHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Invalid QR data'
            ], 422);
        }

        try {
            // Decode QR data
            $qrData = json_decode($request->qr_data, true);
            
            // Validate JSON format
            if (json_last_error() !== JSON_ERROR_NONE || !isset($qrData['user_id'])) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Invalid QR code format'
                ], 400);
            }

            $user = User::find($qrData['user_id']);
            
            if (!$user) {
                return response()->json([
                    'success' => false, 
                    'message' => 'User not found'
                ], 404);
            }

            // Get last 5 transactions
            $transactions = Transaction::where('user_id', $user->user_id)
                ->latest()
                ->take(5)
                ->get();

            // Get last 5 orders
            $orders = Order::where('customer_id', $user->user_id)
                ->with(['items.product', 'admin'])
                ->latest()
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'User data retrieved successfully',
                'data' => [
                    'profile' => [
                        'user_id' => $user->user_id,
                        'full_name' => $user->full_name,
                        'student_id' => $user->student_id,
                        'balance' => $user->balance,
                        'role' => $user->role,
                        'formatted_balance' => 'Rp ' . number_format($user->balance, 0, ',', '.')
                    ],
                    'transactions' => $transactions,
                    'orders' => $orders
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('QR Scan Profile Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'An error occurred while processing QR code'
            ], 500);
        }
    }

    /**
     * Show transaction history via QR
     */
    public function showTransactions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR data'
            ], 422);
        }

        try {
            $qrData = json_decode($request->qr_data, true);
            $user = User::find($qrData['user_id']);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $transactions = Transaction::where('user_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            $orders = Order::where('customer_id', $user->user_id)
                ->with(['items.product', 'admin'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Transaction history retrieved successfully',
                'data' => [
                    'user_info' => [
                        'full_name' => $user->full_name,
                        'student_id' => $user->student_id,
                        'balance' => $user->balance
                    ],
                    'transactions' => $transactions,
                    'recent_orders' => $orders
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transaction history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}