<?php

namespace App\Http\Controllers;

use App\Models\Topup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\QrCodeService;
use App\Exports\TopupsExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class TopupController extends Controller
{
    /**
     * Display topup page
     */
    public function index(Request $request)
    {
        $topups = Topup::where('user_id', $request->user()->user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('topup.index', compact('topups'));
    }

    /**
     * Show topup form
     */
    public function qris()
    {
        return view('topup.qris');
    }

    /**
     * Create topup request - UPDATED for auto-approval with payment proof
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1000|max:500000', // Diubah
            'method' => 'required|in:qris,scan_qr',
            'payment_gateway' => 'nullable|string|max:50',
            'payment_proof' => 'required|image|mimes:jpeg,jpg,png|max:5120' // 5MB max
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Handle payment proof upload
            $paymentProofPath = null;
            if ($request->hasFile('payment_proof')) {
                $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
            }

            $topup = Topup::create([
                'user_id' => $request->user()->user_id,
                'amount' => $request->amount,
                'method' => $request->method,
                'payment_gateway' => $request->payment_gateway,
                'payment_proof' => $paymentProofPath,
                'status' => 'pending' // Will be auto-approved if payment proof exists
            ]);

            // Create pending transaction first
            Transaction::create([
                'user_id' => $request->user()->user_id,
                'type' => 'topup',
                'amount' => $request->amount,
                'status' => 'pending',
                'related_id' => $topup->topup_id,
                'description' => 'Isi Saldo - ' . $topup->payment_reference
            ]);

            // Auto-approve if payment proof is uploaded
            if ($paymentProofPath) {
                $topup->autoApprove($request->user()->user_id);
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => $paymentProofPath ?
                        'Top up request approved automatically! Balance has been added.' :
                        'Top up request created paidfully',
                    'data' => $topup->fresh()
                ], 201);
            }

            $paidMessage = $paymentProofPath ?
                'Top up berhasil! Saldo Anda telah ditambahkan.' :
                'Permintaan top up berhasil dibuat dan menunggu persetujuan admin.';

            return redirect()->route('topup.index')
                ->with('paid', $paidMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if transaction failed
            if (isset($paymentProofPath) && $paymentProofPath && Storage::disk('public')->exists($paymentProofPath)) {
                Storage::disk('public')->delete($paymentProofPath);
            }
            
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Failed to create top up request',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            return back()->withErrors(['error' => 'Failed to create top up request: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show QR Scanner page for topup
     */
    public function scan()
    {
        return view('topup.scan');
    }

    /**
     * Process topup via QR scanner
     */
    public function storeByQr(Request $request, QrCodeService $qrCodeService)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|string|exists:users,user_id',
            'amount' => 'required|numeric|min:1000|max:500000', // Diubah
        ]);

        if ($validator->fails()) {
            return response()->json([
                'paid' => false,
                'message' => 'Invalid data provided.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $adminOrGuru = $request->user();

        try {
            $customer = User::findOrFail($request->user_id);

            DB::beginTransaction();

            // Create the Topup record
            $topup = Topup::create([
                'user_id' => $customer->user_id,
                'amount' => $request->amount,
                'method' => 'scan_qr',
                'status' => 'success', // Direct paid
                'approved_by' => $adminOrGuru->user_id,
            ]);

            // Add balance to customer's account
            $customer->addBalance($request->amount);

            // Create completed transaction record
            Transaction::create([
                'user_id' => $customer->user_id,
                'type' => 'topup',
                'amount' => $request->amount,
                'status' => 'paid',
                'related_id' => $topup->topup_id,
                'description' => 'Isi Saldo via QR by ' . $adminOrGuru->role_name . ' (' . $adminOrGuru->full_name . ')'
            ]);

            DB::commit();

            // Load fresh data with relationships
            $topup->load(['user', 'approver']);

            return response()->json([
                'paid' => true,
                'message' => 'Top up for ' . $customer->full_name . ' amount Rp ' . number_format($request->amount, 0, ',', '.') . ' paidful!',
                'data' => [
                    'topup_id' => $topup->topup_id,
                    'customer_name' => $customer->full_name,
                    'amount' => 'Rp ' . number_format($request->amount, 0, ',', '.'),
                    'payment_reference' => $topup->payment_reference,
                    'receipt_url' => route('topup.receipt', $topup->topup_id),
                    'receipt_pdf_url' => route('topup.receipt.download', $topup->topup_id),
                    'status' => $topup->status,
                    'created_at' => $topup->created_at->format('d F Y, H:i:s'),
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'paid' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending topups (Admin only)
     */
    public function pending(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }
            abort(403);
        }

        $topups = Topup::with('user')
            ->pending()
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        if ($request->expectsJson()) {
            return response()->json([
                'paid' => true,
                'data' => $topups
            ]);
        }

        return view('topup.pending', compact('topups'));
    }

    /**
     * Approve topup (Admin only) - Manual approval
     */
    public function approve(Request $request, $topupId)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'paid' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $topup = Topup::where('topup_id', $topupId)->with('user')->firstOrFail();
            
            if ($topup->status !== 'pending') {
                return response()->json([
                    'paid' => false,
                    'message' => 'Top up is not in pending status'
                ], 400);
            }

            DB::beginTransaction();
            $topup->approve($request->user()->user_id);
            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Top up approved paidfully',
                    'data' => $topup->fresh()
                ]);
            }

            return back()->with('paid', 'Top up approved paidfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'paid' => false,
                'message' => 'Failed to approve top up',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject topup (Admin only)
     */
    public function reject(Request $request, $topupId)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'paid' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'paid' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $topup = Topup::where('topup_id', $topupId)->with('user')->firstOrFail();
            
            if ($topup->status !== 'pending') {
                return response()->json([
                    'paid' => false,
                    'message' => 'Top up is not in pending status'
                ], 400);
            }

            DB::beginTransaction();
            
            // Update topup status
            $topup->status = 'failed';
            $topup->save();

            // Update transaction to failed
            $transaction = Transaction::where('related_id', $topup->topup_id)
                ->where('type', 'topup')
                ->where('status', 'pending')
                ->first();
                
            if ($transaction) {
                $transaction->status = 'failed';
                $transaction->description .= ' - Rejected: ' . ($request->reason ?? 'No reason provided');
                $transaction->save();
            }
            
            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'message' => 'Top up rejected paidfully',
                    'data' => $topup->fresh()
                ]);
            }

            return back()->with('paid', 'Top up rejected paidfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'paid' => false,
                'message' => 'Failed to reject top up',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show topup details
     */
    public function show(Request $request, $topupId)
    {
        try {
            $topup = Topup::where('topup_id', $topupId)->with(['user', 'transaction'])->firstOrFail();
            
            // Check authorization
            if ($topup->user_id !== $request->user()->user_id &&
                $request->user()->role !== 'admin') {
                return response()->json([
                    'paid' => false,
                    'message' => 'Unauthorized to view this top up'
                ], 403);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'data' => $topup
                ]);
            }

            return view('topup.show', compact('topup'));

        } catch (\Exception $e) {
            return response()->json([
                'paid' => false,
                'message' => 'Top up not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show topup receipt
     */
    public function receipt(Request $request, $topupId)
    {
        try {
            $topup = Topup::where('topup_id', $topupId)->with(['user', 'transaction', 'approver'])->firstOrFail();
            
            // Check authorization
            if ($topup->user_id !== $request->user()->user_id &&
                $request->user()->role !== 'admin' &&
                $request->user()->role !== 'guru') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'paid' => false,
                        'message' => 'Unauthorized to view this receipt'
                    ], 403);
                }
                abort(403, 'Unauthorized to view this receipt');
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => true,
                    'data' => $topup
                ]);
            }

            return view('topup.receipt', compact('topup'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Receipt not found',
                    'error' => $e->getMessage()
                ], 404);
            }
            abort(404, 'Receipt not found');
        }
    }

    /**
     * Download topup receipt as PDF
     */
    public function downloadReceipt(Request $request, $topupId)
    {
        try {
            $topup = Topup::where('topup_id', $topupId)->with(['user', 'transaction', 'approver'])->firstOrFail();
            
            // Check authorization
            if ($topup->user_id !== $request->user()->user_id &&
                $request->user()->role !== 'admin' &&
                $request->user()->role !== 'guru') {
                abort(403, 'Unauthorized to download this receipt');
            }

            // Generate PDF using Laravel's built-in PDF generator or a package like DOMPDF
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('topup.receipt-pdf', compact('topup'));
            
            $filename = 'Bukti_TopUp_' . $topup->payment_reference . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to download receipt: ' . $e->getMessage()]);
        }
    }

    /**
     * Supervisor view for guru and admin
     */
    public function supervisorView(Request $request)
    {
        // Only allow admin and guru to access this
        if (!in_array($request->user()->role, ['admin', 'guru'])) {
            abort(403, 'Unauthorized access.');
        }

        // Handle DataTables AJAX request
        if ($request->ajax()) {
            return $this->getTopupsDataTable($request);
        }

        // Get all students for filter dropdown
        $students = User::where('role', 'customer')
            ->select('user_id', 'full_name', 'student_id')
            ->orderBy('full_name')
            ->get();

        // Calculate basic statistics
        $stats = $this->getTopupStatistics($request);

        return view('supervisor.topups', compact('students', 'stats'));
    }

    private function getTopupsDataTable(Request $request)
    {
        $query = Topup::with(['user'])
            ->whereHas('user', function ($q) {
                $q->where('role', 'customer');
            })
            ->select('topups.*');

        return DataTables::eloquent($query)
            ->addColumn('student_info', function ($topup) {
                return [
                    'name' => $topup->user->full_name ?? 'N/A',
                    'student_id' => $topup->user->student_id ?? ''
                ];
            })
            ->addColumn('formatted_amount', function ($topup) {
                return 'Rp ' . number_format($topup->amount, 0, ',', '.');
            })
            ->addColumn('status_badge', function ($topup) {
                $badges = [
                    'paid' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Berhasil
                                  </span>',
                    'pending' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                  </span>',
                    'failed' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                   <i class="fas fa-times mr-1"></i>Gagal
                                 </span>'
                ];
                return $badges[$topup->status] ?? '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">' . ucfirst($topup->status) . '</span>';
            })
            ->addColumn('reference_info', function ($topup) {
                return [
                    'payment_reference' => $topup->payment_reference,
                    'topup_id' => $topup->topup_id
                ];
            })
            ->addColumn('action', function ($topup) use ($request) {
                $actions = '<a href="' . route('topup.receipt', $topup->topup_id) . '"
                               class="text-blue-600 hover:text-blue-900 mr-2">
                                <i class="fas fa-eye"></i> Detail
                            </a>';
                            
                if ($request->user()->role === 'admin' && $topup->status === 'pending') {
                    $actions .= '<button onclick="approveTopup(\'' . $topup->topup_id . '\')"
                                        class="text-green-600 hover:text-green-900 mr-2">
                                    <i class="fas fa-check"></i> Setujui
                                </button>
                                <button onclick="rejectTopup(\'' . $topup->topup_id . '\')"
                                        class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-times"></i> Tolak
                                </button>';
                }
                return $actions;
            })
            ->addColumn('date_formatted', function ($topup) {
                return [
                    'date' => $topup->created_at->format('d/m/Y'),
                    'time' => $topup->created_at->format('H:i')
                ];
            })
            ->addColumn('method_badge', function ($topup) {
                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">' .
                       ($topup->method_name ?? $topup->method) . '</span>';
            })
            ->addColumn('payment_proof', function ($topup) {
                if ($topup->hasPaymentProof()) {
                    return '<a href="' . $topup->getPaymentProofUrl() . '" target="_blank" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-image mr-1"></i>Lihat Bukti
                            </a>';
                }
                return '<span class="text-gray-400">-</span>';
            })
            ->filter(function ($query) use ($request) {
                // Student filter
                if ($request->filled('student_id')) {
                    $query->where('user_id', $request->student_id);
                }
                
                // Status filter
                if ($request->filled('status')) {
                    $query->where('status', $request->status);
                }
                
                // Date range filter
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }
                
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
            })
            ->rawColumns(['status_badge', 'action', 'method_badge', 'payment_proof'])
            ->make(true);
    }

    private function getTopupStatistics(Request $request)
    {
        $query = Topup::whereHas('user', function ($q) {
            $q->where('role', 'customer');
        });

        // Apply same filters for statistics
        if ($request->filled('student_id')) {
            $query->where('user_id', $request->student_id);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $allTopups = $query->get();
        
        return [
            'total_requests' => $allTopups->count(),
            'pending_count' => $allTopups->where('status', 'pending')->count(),
            'paid_amount' => $allTopups->where('status', 'paid')->sum('amount'),
            'failed_count' => $allTopups->where('status', 'failed')->count(),
        ];
    }

    public function getStatistics(Request $request)
    {
        if (!in_array($request->user()->role, ['admin', 'guru'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($this->getTopupStatistics($request));
    }

    /**
     * Export topups to Excel
     */
    public function export(Request $request)
    {
        // Only allow admin and guru to export
        if (!in_array($request->user()->role, ['admin', 'guru'])) {
            abort(403, 'Unauthorized access.');
        }

        try {
            // Get filters from request
            $filters = $request->only([
                'student_id',
                'status',
                'date_from',
                'date_to'
            ]);

            // Create filename with timestamp and filters
            $filename = 'Rekap_TopUp_' . date('Y-m-d_H-i-s');
            
            // Add filter info to filename
            if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
                $dateInfo = '';
                if (!empty($filters['date_from'])) {
                    $dateInfo .= '_dari_' . date('Y-m-d', strtotime($filters['date_from']));
                }
                if (!empty($filters['date_to'])) {
                    $dateInfo .= '_sampai_' . date('Y-m-d', strtotime($filters['date_to']));
                }
                $filename .= $dateInfo;
            }

            if (!empty($filters['status'])) {
                $filename .= '_status_' . $filters['status'];
            }

            $filename .= '.xlsx';

            return Excel::download(new TopupsExport($filters), $filename);

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'paid' => false,
                    'message' => 'Failed to export data: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to export data: ' . $e->getMessage()]);
        }
    }
}