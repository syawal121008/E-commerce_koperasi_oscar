<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Topup extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'topup_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'amount',
        'method',
        'payment_gateway',
        'payment_reference',
        'payment_url',
        'payment_proof', // Added payment proof field
        'status',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Generate payment reference saat topup dibuat
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($topup) {
            if (empty($topup->payment_reference)) {
                $topup->payment_reference = 'TOPUP-' . strtoupper(Str::random(10));
            }
        });
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'topup_id';
    }

    /**
     * Resolve a route binding to the model
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'related_id', 'topup_id')->where('type', 'topup');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Payment proof methods
    public function getPaymentProofUrl()
    {
        if ($this->payment_proof && Storage::disk('public')->exists($this->payment_proof)) {
            return Storage::url($this->payment_proof);
        }
        return null;
    }

    public function hasPaymentProof()
    {
        return $this->payment_proof && Storage::disk('public')->exists($this->payment_proof);
    }

    public function deletePaymentProof()
    {
        if ($this->payment_proof && Storage::disk('public')->exists($this->payment_proof)) {
            Storage::disk('public')->delete($this->payment_proof);
            $this->payment_proof = null;
            $this->save();
        }
    }

    // Auto-approve method for QRIS with payment proof
   public function autoApprove($approvedBy = null)
{
    $this->status = 'success';
    if ($approvedBy) {
        $this->approved_by = $approvedBy;
    }
    $this->save();
    
    // Add balance to user
    $this->user->addBalance($this->amount);
    
    // Update existing pending transaction or create new one
    $transaction = Transaction::where('related_id', $this->topup_id)
        ->where('type', 'topup')
        ->where('status', 'pending')
        ->first();

    if ($transaction) {
        $transaction->status = 'completed'; // ← DIUBAH: dari 'success' ke 'completed'
        $transaction->description = 'Top up balance - ' . $this->payment_reference . ' (Disetujui otomatis dengan bukti pembayaran dan pengecekan melalui sistem)';
        $transaction->save();
    } else {
        // Create transaction record if doesn't exist
        Transaction::create([
            'user_id' => $this->user_id,
            'type' => 'topup',
            'amount' => $this->amount,
            'status' => 'completed', // ← DIUBAH: dari 'success' ke 'completed'
            'related_id' => $this->topup_id,
            'description' => 'Top up balance - ' . $this->payment_reference . ' (Disetujui otomatis dengan bukti pembayaran dan pengecekan melalui sistem)'
        ]);
    }
}

    // Manual approve method (for admin approval)
    public function approve($approvedBy = null)
    {
        $this->status = 'success';
        if ($approvedBy) {
            $this->approved_by = $approvedBy;
        }
        $this->save();
        
        // Add balance to user
        $this->user->addBalance($this->amount);
        
        // Update existing pending transaction or create new one
        $transaction = Transaction::where('related_id', $this->topup_id)
            ->where('type', 'topup')
            ->where('status', 'pending')
            ->first();

        if ($transaction) {
            $transaction->status = 'success';
            $transaction->description = 'Top up balance - ' . $this->payment_reference . ' (Approved)';
            $transaction->save();
        } else {
            // Create transaction record if doesn't exist
            Transaction::create([
                'user_id' => $this->user_id,
                'type' => 'topup',
                'amount' => $this->amount,
                'status' => 'success',
                'related_id' => $this->topup_id,
                'description' => 'Top up balance - ' . $this->payment_reference
            ]);
        }
    }

    public function reject($reason = null)
    {
        $this->status = 'failed';
        $this->save();
        
        // Update existing pending transaction
        $transaction = Transaction::where('related_id', $this->topup_id)
            ->where('type', 'topup')
            ->where('status', 'pending')
            ->first();

        if ($transaction) {
            $transaction->status = 'failed';
            $transaction->description .= ' - Rejected' . ($reason ? ': ' . $reason : '');
            $transaction->save();
        } else {
            // Create failed transaction record if doesn't exist
            Transaction::create([
                'user_id' => $this->user_id,
                'type' => 'topup',
                'amount' => $this->amount,
                'status' => 'failed',
                'related_id' => $this->topup_id,
                'description' => 'Top up balance failed - ' . $this->payment_reference . ($reason ? ' - ' . $reason : '')
            ]);
        }
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getMethodNameAttribute()
    {
        return match ($this->method) {
            'qris' => 'QRIS',
            'scan_qr' => 'QR Scanner',
            default => ucfirst($this->method),
        };
    }

    public function getStatusNameAttribute()
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'success' => 'Success',
            'failed' => 'Failed',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => 'text-yellow-600 bg-yellow-100',
            'success' => 'text-green-600 bg-green-100',
            'failed' => 'text-red-600 bg-red-100',
            default => 'text-gray-600 bg-gray-100',
        };
    }

    /**
     * Check if user has any role
     */
    public static function canBeViewedBy($user, $topup)
    {
        // Admin can view all
        if ($user->role === 'admin') {
            return true;
        }
        
        // Guru can view all customer topups
        if ($user->role === 'guru') {
            return true;
        }
        
        // Users can only view their own topups
        return $topup->user_id === $user->user_id;
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
                    'success' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
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
                
                // Status filter - PERBAIKAN: map 'paid' ke 'success'
                if ($request->filled('status')) {
                    $status = $request->status === 'paid' ? 'success' : $request->status;
                    $query->where('status', $status);
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

        // Apply same filters for statistics - PERBAIKAN: map 'paid' ke 'success'
        if ($request->filled('student_id')) {
            $query->where('user_id', $request->student_id);
        }
        
        if ($request->filled('status')) {
            $status = $request->status === 'paid' ? 'success' : $request->status;
            $query->where('status', $status);
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
            'success_amount' => $allTopups->where('status', 'success')->sum('amount'), // PERBAIKAN: ganti ke 'success_amount'
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
}