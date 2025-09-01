<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Topup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show dashboard with QR code and statistics
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Ensure QR code exists untuk admin dan customer
        if ($user->role === 'admin' || $user->role === 'customer') {
            $this->ensureQrCodeExists($user);
        }
        
        // Get statistics based on user role
        $stats = $this->getUserStats($user);

        // Recent transactions - hanya untuk admin dan customer
        $recentTransactions = collect();
        if ($user->role === 'admin' || $user->role === 'customer') {
            $recentTransactions = Transaction::where('user_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        }

        // Kalau role admin → tampilkan pemasukan harian
        $dailyIncome = null;
        if ($user->role === 'admin') {
            $dailyIncome = Transaction::where('type', 'income')
                ->whereDate('created_at', now()->toDateString())
                ->where('status', 'paid')
                ->sum('amount');
        }

        // Kalau role guru → tampilkan pemasukan bulan ini (sama seperti admin tapi bulanan)
        $monthlySaldoIncome = null;
        if ($user->role === 'guru') {
            $monthlySaldoIncome = Transaction::where('type', 'income')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'paid')
                ->sum('amount');
        }

        // Data untuk chart jika role guru
        $chartData = [];
        if ($user->role === 'guru') {
            $chartData = $this->getChartDataForGuru();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'paid' => true,
                'data' => [
                    'user' => $user,
                    'qr_code_url' => ($user->role === 'admin' || $user->role === 'customer') ? $user->getQrCodeUrl() : null,
                    'balance' => $user->balance,
                    'stats' => $stats,
                    'recent_transactions' => $recentTransactions,
                    'daily_income' => $dailyIncome,
                    'monthly_saldo_income' => $monthlySaldoIncome,
                    'chart_data' => $chartData
                ]
            ]);
        }

        return view('dashboard', compact('user', 'stats', 'recentTransactions', 'dailyIncome', 'monthlySaldoIncome', 'chartData'));
    }

    /**
     * Show QR code page - hanya untuk admin dan customer
     */
    public function qrCode(Request $request)
    {
        $user = $request->user();
        
        // Cek apakah user memiliki akses ke QR code
        if ($user->role !== 'admin' && $user->role !== 'customer') {
            abort(403, 'Akses ditolak. Fitur QR Code hanya tersedia untuk Admin dan Customer.');
        }
        
        // Ensure QR code exists
        $this->ensureQrCodeExists($user);
        
        if ($request->expectsJson()) {
            return response()->json([
                'paid' => true,
                'data' => [
                    'user_id' => $user->user_id,
                    'full_name' => $user->full_name,
                    'student_id' => $user->student_id,
                    'qr_code_url' => $user->getQrCodeUrl(),
                    'balance' => $user->balance,
                    'role' => $user->role,
                    'has_valid_qr' => $user->hasValidQrCode()
                ]
            ]);
        }

        return view('qr-code', compact('user'));
    }

    /**
     * Force regenerate QR code (only when user explicitly requests)
     */
    public function regenerateQR(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Cek apakah user memiliki akses ke QR code
            if ($user->role !== 'admin' && $user->role !== 'customer') {
                return response()->json([
                    'paid' => false,
                    'message' => 'Akses ditolak. Fitur QR Code hanya tersedia untuk Admin dan Customer.'
                ], 403);
            }
            
            // Validate user has student_id
            if (empty($user->student_id)) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Student ID is required to generate QR code'
                ], 400);
            }

            Log::info('User requested QR regeneration: ' . $user->user_id);
            
            // Force regenerate QR code
            $newQrCode = $user->regenerateQrCode();
            
            return response()->json([
                'paid' => true,
                'message' => 'QR code regenerated paidfully',
                'data' => [
                    'qr_code_url' => $user->getQrCodeUrl(),
                    'user_id' => $user->user_id,
                    'student_id' => $user->student_id,
                    'timestamp' => now()->toISOString()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error regenerating QR code: ' . $e->getMessage());
            
            return response()->json([
                'paid' => false,
                'message' => 'Failed to regenerate QR code: ' . $e->getMessage()
            ], 500);
        }
    }

    // Tambah di DashboardController atau controller yang handle dashboard
public function refreshDashboardData()
{
    // Clear any cache if using cache
    Cache::forget('dashboard_stats_' . auth()->id());
    Cache::forget('recent_transactions_' . auth()->id());
    
    return response()->json(['success' => true]);
}

    /**
     * Ensure QR code exists for user (generate if missing)
     */
    private function ensureQrCodeExists($user): void
    {
        // Only generate if QR code doesn't exist
        if (!$user->hasValidQrCode() && !empty($user->student_id)) {
            try {
                Log::info('QR code missing for user ' . $user->user_id . ', generating...');
                $user->generateAndSaveQrCode();
                $user->refresh();
            } catch (\Exception $e) {
                Log::error('Failed to ensure QR code exists: ' . $e->getMessage());
                // Don't throw exception, just log the error
            }
        }
    }

    /**
     * Get user statistics based on role
     */
    private function getUserStats($user): array
    {
        return match ($user->role) {
            'admin' => [
                'total_users' => User::count(),
                'total_orders' => Order::count(),
                'total_products' => Product::count(),
                'pending_topups' => Topup::where('status', 'pending')->count(),
                'total_revenue' => Order::where('status', 'paid')->sum('total_price')
            ],
            'guru' => [
                'total_saldo_terkumpul' => User::where('role', 'customer')->sum('balance'),
                'total_transaksi_bulan_ini' => Transaction::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'total_siswa' => User::where('role', 'customer')->count(),
                'rata_rata_saldo' => User::where('role', 'customer')->avg('balance') ?? 0
            ],
            default => [
                'my_orders' => Order::where('customer_id', $user->user_id)->count(),
                'pending_orders' => Order::where('customer_id', $user->user_id)->where('status', 'pending')->count(),
                'paid_orders' => Order::where('customer_id', $user->user_id)->where('status', 'paid')->count(),
                'total_spent' => Order::where('customer_id', $user->user_id)->where('status', 'paid')->sum('total_price')
            ]
        };
    }

    /**
     * Get chart data for guru role
     */
    private function getChartDataForGuru(): array
    {
        // Data 12 bulan terakhir
        $months = [];
        $salesData = [];
        $incomeData = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
            
            // Data penjualan per bulan
            $monthlySales = Order::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->where('status', 'paid')
                ->sum('total_price');
            $salesData[] = (float) $monthlySales;
            
            // Data pemasukan saldo per bulan
            $monthlyIncome = Transaction::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->where('type', 'topup')
                ->where('status', 'paid')
                ->sum('amount');
            $incomeData[] = (float) $monthlyIncome;
        }

        return [
            'months' => $months,
            'sales' => $salesData,
            'income' => $incomeData
        ];
    }

    /**
     * Rekap saldo untuk guru (route baru yang perlu dibuat)
     */
    public function saldoRecap(Request $request)
    {
        $user = $request->user();
        
        // Cek akses guru
        if ($user->role !== 'guru') {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Guru.');
        }

        // Data rekap saldo
        $students = User::where('role', 'customer')
            ->select('user_id', 'full_name', 'student_id', 'balance', 'created_at')
            ->orderBy('balance', 'desc')
            ->paginate(20);

        $totalSaldo = User::where('role', 'customer')->sum('balance');
        $totalSiswa = User::where('role', 'customer')->count();
        $rataSaldo = $totalSiswa > 0 ? $totalSaldo / $totalSiswa : 0;

        if ($request->expectsJson()) {
            return response()->json([
                'paid' => true,
                'data' => [
                    'students' => $students,
                    'summary' => [
                        'total_saldo' => $totalSaldo,
                        'total_siswa' => $totalSiswa,
                        'rata_saldo' => $rataSaldo
                    ]
                ]
            ]);
        }

        return view('guru.saldo-recap', compact('students', 'totalSaldo', 'totalSiswa', 'rataSaldo'));
    }
}