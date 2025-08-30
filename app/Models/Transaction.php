<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'transaction_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'status',
        'related_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Transaction type constants - DISESUAIKAN DENGAN MIGRATION
    const TYPE_TOPUP = 'topup';
    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND = 'refund';
    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';

    // Transaction status constants - DISESUAIKAN DENGAN MIGRATION + TAMBAHAN COMPLETED
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed'; // TAMBAHAN BARU


    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'related_id', 'order_id');
    }

   
   // Status check methods - TAMBAHKAN
    public function isCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }
public function isPaid()
{
    return $this->status === self::STATUS_PAID;
}
    // Scopes
    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }

    public function scopeTopups($query)
    {
        return $query->where('type', self::TYPE_TOPUP);
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    public function scopepaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
// Di app/Models/Transaction.php  
public function topup()
{
    return $this->belongsTo(Topup::class, 'related_id', 'topup_id')
                ->where('type', 'topup');
}
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Status check methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

   

    

    public function isFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }
public function markAsPaid()
{
    $this->status = self::STATUS_PAID;
    $this->save();
    return $this;
}
// Update method ispaidful - TAMBAHKAN COMPLETED
   public function ispaidful()
{
    return in_array($this->status, [self::STATUS_PAID, self::STATUS_COMPLETED]);
}
    // Type check methods
    public function isPayment()
    {
        return $this->type === self::TYPE_PAYMENT;
    }

    public function isIncome()
    {
        return $this->type === self::TYPE_INCOME;
    }

    public function isRefund()
    {
        return $this->type === self::TYPE_REFUND;
    }

    public function isTopup()
    {
        return $this->type === self::TYPE_TOPUP;
    }

    public function isExpense()
    {
        return $this->type === self::TYPE_EXPENSE;
    }

    public function isDebit()
    {
        return in_array($this->type, [self::TYPE_PAYMENT, self::TYPE_EXPENSE]);
    }

    public function isCredit()
    {
        return in_array($this->type, [self::TYPE_INCOME, self::TYPE_REFUND, self::TYPE_TOPUP]);
    }

    // Formatted attributes
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getSignedAmountAttribute()
    {
        $prefix = $this->isDebit() ? '-' : '+';
        return $prefix . $this->formatted_amount;
    }

    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            self::TYPE_PAYMENT => 'Pembayaran',
            self::TYPE_INCOME => 'Pendapatan',
            self::TYPE_REFUND => 'Pengembalian Dana',
            self::TYPE_TOPUP => 'Isi Ulang Saldo',
            self::TYPE_EXPENSE => 'Pengeluaran',
            default => 'Tidak Diketahui'
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_PAID => 'Dibayar',          // GANTI dari 'Berhasil'
            self::STATUS_COMPLETED => 'Selesai', // TAMBAHAN
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Tidak Diketahui'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_paid => 'green',
            self::STATUS_COMPLETED => 'blue', // TAMBAHAN
            self::STATUS_FAILED => 'red',
            self::STATUS_CANCELLED => 'red',
            default => 'gray'
        };
    }
       // Method baru - TAMBAHKAN
    public function markAsCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->save();
        return $this;
    }

    public function getTypeColorAttribute()
    {
        return match($this->type) {
            self::TYPE_PAYMENT => 'red',
            self::TYPE_INCOME => 'green',
            self::TYPE_REFUND => 'blue',
            self::TYPE_TOPUP => 'purple',
            self::TYPE_EXPENSE => 'orange',
            default => 'gray'
        };
    }


    public function markAsFailed()
    {
        $this->status = self::STATUS_FAILED;
        $this->save();
        return $this;
    }

    // Static methods for creating transactions
    public static function createPayment($userId, $amount, $orderId, $description = null)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_PAYMENT,
            'amount' => $amount,
            'status' => self::STATUS_paid,
            'related_id' => $orderId,
            'description' => $description ?: "Pembayaran pesanan #{$orderId}"
        ]);
    }

    public static function createIncome($userId, $amount, $orderId, $description = null)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_INCOME,
            'amount' => $amount,
            'status' => self::STATUS_paid,
            'related_id' => $orderId,
            'description' => $description ?: "Pendapatan dari pesanan #{$orderId}"
        ]);
    }

    public static function createRefund($userId, $amount, $orderId, $description = null)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_REFUND,
            'amount' => $amount,
            'status' => self::STATUS_paid,
            'related_id' => $orderId,
            'description' => $description ?: "Pengembalian dana pesanan #{$orderId}"
        ]);
    }

    public static function createTopup($userId, $amount, $topupId, $description = null)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_TOPUP,
            'amount' => $amount,
            'status' => self::STATUS_paid,
            'related_id' => $topupId,
            'description' => $description ?: "Isi ulang saldo"
        ]);
    }

    public static function createExpense($userId, $amount, $relatedId = null, $description = null)
    {
        return self::create([
            'user_id' => $userId,
            'type' => self::TYPE_EXPENSE,
            'amount' => $amount,
            'status' => self::STATUS_paid,
            'related_id' => $relatedId,
            'description' => $description ?: "Pengeluaran"
        ]);
    }

    // Get related model based on type and related_id
    public function getRelatedModel()
    {
        if (!$this->related_id) {
            return null;
        }

        return match($this->type) {
            self::TYPE_PAYMENT, self::TYPE_INCOME, self::TYPE_REFUND => $this->order,
            self::TYPE_TOPUP => $this->topup,
            default => null
        };
    }

    // Override route key name to use transaction_id
    public function getRouteKeyName()
    {
        return 'transaction_id';
    }
    
}