<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsExport;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    
    /**
     * Display transaction history
     */
    public function index(Request $request)
    {
        $query = Transaction::where('user_id', $request->user()->user_id);

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('start_date')) {
            try {
                $query->whereDate('created_at', '>=', $request->start_date);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }
        
        if ($request->filled('end_date')) {
            try {
                $query->whereDate('created_at', '<=', $request->end_date);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);
        // Calculate statistics
        $totalTopup = $query->clone()->where('type', 'topup')->sum('amount');
        $totalPayment = $query->clone()->where('type', 'payment')->sum('amount');
        $totalIncome = $totalTopup - $totalPayment;

        if ($request->expectsJson()) {
            return response()->json([
                'paid' => true,
                'data' => $transactions
            ]);
        }

return view('transactions.index', compact('transactions', 'totalTopup', 'totalPayment', 'totalIncome'));    }

    /**
     * Show transaction detail
     */
    public function show(Request $request, $transactionId)
{
    try {
        // Check if transactionId is numeric (primary key) or string (transaction_id)
        if (is_numeric($transactionId)) {
            // If numeric, assume it's the primary key (id)
            $transaction = Transaction::with('user')->findOrFail($transactionId);
        } else {
            // If not numeric, assume it's the transaction_id field
            $transaction = Transaction::with('user')
                ->where('transaction_id', $transactionId)
                ->firstOrFail();
        }

        // Check authorization
        if ($transaction->user_id !== $request->user()->user_id &&
            !in_array($request->user()->role, ['admin', 'guru'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Unauthorized to view this transaction'
                ], 403);
            }
            abort(403, 'Unauthorized to view this transaction');
        }

        // Get related model if exists, now with error handling
        $relatedModel = null;
        if (method_exists($transaction, 'getRelatedModel')) {
            try {
                $relatedModel = $transaction->getRelatedModel();
            } catch (\Exception $e) {
                // Log the error for debugging purposes and continue execution.
                // This prevents the 500 server error page.
                \Log::error("Error getting related model for transaction {$transaction->transaction_id}: " . $e->getMessage());
                // $relatedModel will remain null, and the page will still render.
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'paid' => true,
                'data' => [
                    'transaction' => $transaction,
                    'related' => $relatedModel
                ]
            ]);
        }

        return view('transactions.show', compact('transaction', 'relatedModel'));

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        if ($request->expectsJson()) {
            return response()->json([
                'paid' => false,
                'message' => 'Transaction not found'
            ], 404);
        }
        abort(404, 'Transaction not found');
    } catch (\Exception $e) {
        if ($request->expectsJson()) {
            return response()->json([
                'paid' => false,
                'message' => 'An error occurred',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
        abort(500, 'Internal server error');
    }
}
    /**
     * Show transaction history by QR scan
     */
    public function showByQR(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'paid' => false,
                'message' => 'Invalid QR data',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $qrData = json_decode($request->qr_data, true);
            
            if (!$qrData || !isset($qrData['user_id'])) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Invalid QR data format'
                ], 422);
            }

            $user = User::find($qrData['user_id']);

            if (!$user) {
                return response()->json([
                    'paid' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $transactions = Transaction::where('user_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            return response()->json([
                'paid' => true,
                'message' => 'Transaction history retrieved paidfully',
                'data' => [
                    'user_info' => [
                        'full_name' => $user->full_name,
                        'student_id' => $user->student_id,
                        'balance' => $user->balance
                    ],
                    'transactions' => $transactions
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'paid' => false,
                'message' => 'Failed to retrieve transaction history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all transactions (Admin only)
     */
    public function all(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'guru'])) {
            abort(403);
        }

        // Handle CSV export request
        if ($request->has('export')) {
            return $this->exportAllTransactions($request);
        }

        $query = Transaction::with('user');

        // Filter by user name or student ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                  ->orWhere('student_id', 'like', '%' . $search . '%');
            });
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('start_date')) {
            try {
                $query->whereDate('created_at', '>=', $request->start_date);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }
        
        if ($request->filled('end_date')) {
            try {
                $query->whereDate('created_at', '<=', $request->end_date);
            } catch (\Exception $e) {
                // Invalid date format, ignore filter
            }
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Calculate statistics
        $totalTopup = $query->clone()->where('type', 'topup')->sum('amount');
        $totalPayment = $query->clone()->where('type', 'payment')->sum('amount');
        $totalIncome = $totalTopup - $totalPayment;

        if ($request->expectsJson()) {
            return response()->json([
                'paid' => true,
                'data' => $transactions
            ]);
        }

        return view('transactions.all', compact('transactions', 'totalTopup', 'totalPayment', 'totalIncome'));
    }

    public function exportStudentTransactions(Request $request, string $userId)
    {
        $user = User::find($userId);

        if (!$user) {
            return redirect()->back()->with('error', 'Siswa tidak ditemukan.');
        }

        $filters = $request->only(['type', 'status', 'start_date', 'end_date']);
        $fileName = 'rekap-transaksi-' . Str::slug($user->full_name, '-') . '.xlsx';
        return Excel::download(new TransactionsExport($userId, $filters), $fileName);
    }

    /**
     * Ekspor rekap transaksi untuk semua siswa (Admin only).
     */
    public function exportAllTransactions(Request $request)
    {
        // Proteksi agar hanya admin yang bisa mengakses
        if (!in_array($request->user()->role, ['admin', 'guru'])) {
            abort(403, 'ANDA TIDAK MEMILIKI AKSES');
        }

        // Siapkan nama file
        $fileName = 'rekap-semua-transaksi-siswa-' . date('d-m-Y') . '.xlsx';

        // Download file Excel, tanpa mengirim userId ke constructor
        return Excel::download(new TransactionsExport(), $fileName);
    }

    public function supervisorView(Request $request)
    {
        // Only allow admin and guru to access this
        if (!in_array(auth()->user()->role, ['admin', 'guru'])) {
            abort(403, 'Unauthorized access.');
        }

        // Handle DataTables AJAX request
        if ($request->ajax()) {
            return $this->getTransactionsDataTable($request);
        }

        // Get all students for filter dropdown
        $students = User::where('role', 'customer')
            ->select('user_id', 'full_name', 'student_id')
            ->orderBy('full_name')
            ->get();

        // Calculate basic statistics
        $stats = $this->getTransactionStatistics($request);

        return view('supervisor.transactions', compact('students', 'stats'));
    }

    private function getTransactionsDataTable(Request $request)
    {
        $query = Transaction::with(['user'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'customer');
            })
            ->select('transactions.*');

        return DataTables::eloquent($query)
            ->addColumn('student_info', function ($transaction) {
                return [
                    'name' => $transaction->user->full_name ?? 'N/A',
                    'student_id' => $transaction->user->student_id ?? ''
                ];
            })
            ->addColumn('type_badge', function ($transaction) {
                if ($transaction->type == Transaction::TYPE_TOPUP) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-arrow-up mr-1"></i>Top Up
                            </span>';
                } elseif ($transaction->type == Transaction::TYPE_PAYMENT) {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <i class="fas fa-shopping-cart mr-1"></i>Pembelian
                            </span>';
                } else {
                    return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-question mr-1"></i>' . ucfirst($transaction->type) . '
                            </span>';
                }
            })
            ->addColumn('formatted_amount', function ($transaction) {
                if ($transaction->type == Transaction::TYPE_TOPUP) {
                    return '<span class="text-green-600">+Rp ' . number_format($transaction->amount, 0, ',', '.') . '</span>';
                } else {
                    return '<span class="text-red-600">-Rp ' . number_format($transaction->amount, 0, ',', '.') . '</span>';
                }
            })
            ->addColumn('status_badge', function ($transaction) {
                $badges = [
                    'completed' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Selesai</span>',
                    'paid' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Dibayar</span>',
                    'pending' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>',
                    'failed' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Gagal</span>',
                    'cancelled' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Dibatalkan</span>'
                ];
                return $badges[$transaction->status] ?? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">' . ucfirst($transaction->status) . '</span>';
            })
            ->addColumn('description_short', function ($transaction) {
                return $transaction->description ? 
                    (strlen($transaction->description) > 50 ? 
                        substr($transaction->description, 0, 50) . '...' : 
                        $transaction->description) : 
                    '-';
            })
            ->addColumn('action', function ($transaction) {
                return '<a href="' . route('transactions.show', $transaction->transaction_id) . '" 
                           class="text-blue-600 hover:text-blue-900">
                            <i class="fas fa-eye"></i> Detail
                        </a>';
            })
            ->addColumn('date_formatted', function ($transaction) {
                return $transaction->created_at->format('d/m/Y H:i');
            })
            ->filter(function ($query) use ($request) {
                // Student filter
                if ($request->filled('student_id')) {
                    $query->where('user_id', $request->student_id);
                }
                
                // Type filter - Map frontend type to model constants
                if ($request->filled('type')) {
                    $mappedType = $this->mapFilterTypeToModelType($request->type);
                    $query->where('type', $mappedType);
                }
                
                // Date range filter
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }
                
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
            })
            ->rawColumns(['type_badge', 'formatted_amount', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Map filter type to model type constants
     */
    private function mapFilterTypeToModelType($filterType)
    {
        return match($filterType) {
            'topup' => Transaction::TYPE_TOPUP,
            'purchase' => Transaction::TYPE_PAYMENT, // Map 'purchase' to 'payment'
            'payment' => Transaction::TYPE_PAYMENT,
            'income' => Transaction::TYPE_INCOME,
            'expense' => Transaction::TYPE_EXPENSE,
            'refund' => Transaction::TYPE_REFUND,
            default => $filterType
        };
    }

    /**
     * Get transaction statistics
     */
    private function getTransactionStatistics(Request $request)
    {
        // Query untuk statistik yang menghormati SEMUA filter (termasuk tanggal)
        $filteredQuery = Transaction::whereHas('user', function ($q) {
            $q->where('role', 'customer');
        });

        // Terapkan filter yang sama untuk statistik umum
        if ($request->filled('student_id')) {
            $filteredQuery->where('user_id', $request->student_id);
        }
        
        if ($request->filled('type')) {
            $mappedType = $this->mapFilterTypeToModelType($request->type);
            $filteredQuery->where('type', $mappedType);
        }
        
        if ($request->filled('date_from')) {
            $filteredQuery->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $filteredQuery->whereDate('created_at', '<=', $request->date_to);
        }

        // --- MODIFIKASI: Logika Siswa Aktif diubah di sini ---

        // Query KHUSUS untuk 'Siswa Aktif', yang TIDAK menghormati filter tanggal,
        // tetapi menghitung siswa dengan transaksi dalam 1 bulan terakhir.
        $activeStudentQuery = Transaction::where('created_at', '>=', now()->subMonth())
            ->whereHas('user', function ($q) {
                $q->where('role', 'customer');
            });

        // Namun, filter siswa dan tipe tetap diterapkan untuk konsistensi
        if ($request->filled('student_id')) {
            $activeStudentQuery->where('user_id', $request->student_id);
        }
        if ($request->filled('type')) {
            $mappedType = $this->mapFilterTypeToModelType($request->type);
            $activeStudentQuery->where('type', $mappedType);
        }

        // Hitung statistik menggunakan query yang sudah disiapkan
        return [
            'total_transactions' => $filteredQuery->clone()->count(),
            'total_topup' => $filteredQuery->clone()->where('type', Transaction::TYPE_TOPUP)->sum('amount'),
            'total_purchase' => $filteredQuery->clone()->where('type', Transaction::TYPE_PAYMENT)->sum('amount'),
            'active_students' => $activeStudentQuery->distinct('user_id')->count('user_id')
        ];
    }

    /**
     * Get statistics via AJAX
     */
    public function getStatistics(Request $request)
    {
        if (!in_array(auth()->user()->role, ['admin', 'guru'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($this->getTransactionStatistics($request));
    }
}