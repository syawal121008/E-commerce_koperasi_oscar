<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'order_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'customer_id',
        'admin_id',
        'total_price',
        'status',
        'payment_method',
        'balance_used',
        'cash_amount',
        'notes'
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'balance_used' => 'decimal:2',
        'cash_amount' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_COMPLETED = 'completed'; // BARU
    const STATUS_CANCELLED = 'cancelled';

    // Payment method constants
    const PAYMENT_BALANCE = 'balance';
    const PAYMENT_CASH = 'cash';
    const PAYMENT_MIXED = 'mixed';

    // Relationships
    public function customer()
{
    return $this->belongsTo(User::class, 'customer_id', 'user_id')
                ->where('role', 'customer');
}


    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'related_id', 'order_id')->where('type', 'payment');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }
    
    public function scopeCompleted($query) // BARU
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    // Status check methods
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isCompleted() // BARU
    {
        return $this->status === self::STATUS_COMPLETED;
    }
    
    public function isCancelled()
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBePaid()
    {
        return $this->isPending() && ($this->payment_method === self::PAYMENT_CASH || $this->payment_method === self::PAYMENT_MIXED);
    }

    // Attribute Accessors
    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Belum Dibayar',
            self::STATUS_PAID => 'Dibayar',
            self::STATUS_COMPLETED => 'Selesai', // BARU
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Tidak Diketahui'
        };
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_PAID => 'blue',
            self::STATUS_COMPLETED => 'green', // BARU
            self::STATUS_CANCELLED => 'red',
            default => 'gray'
        };
    }

    public function getPaymentMethodLabelAttribute()
    {
        return match($this->payment_method) {
            self::PAYMENT_BALANCE => 'Saldo E-Wallet',
            self::PAYMENT_CASH => 'Tunai (Cash)',
            self::PAYMENT_MIXED => 'Kombinasi (Saldo + Tunai)',
            default => 'Unknown'
        };
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function getRemainingCashAmountAttribute()
    {
        if ($this->isPending() && ($this->payment_method == self::PAYMENT_CASH || $this->payment_method == self::PAYMENT_MIXED)) {
            return $this->cash_amount;
        }
        return 0;
    }
}