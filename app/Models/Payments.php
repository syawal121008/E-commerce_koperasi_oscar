<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payments extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_id';
    protected $keyType = 'int';

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationship
    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'order_id');
    }

    // Accessors
    public function getPaymentMethodNameAttribute()
    {
        $methods = [
            'cod' => 'Cash on Delivery',
            'transfer' => 'Transfer Bank',
            'qris' => 'QRIS'
        ];

        return $methods[$this->payment_method] ?? $this->payment_method;
    }

    public function getPaymentStatusNameAttribute()
    {
        $statuses = [
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Telah Dibayar',
            'failed' => 'Gagal'
        ];

        return $statuses[$this->payment_status] ?? $this->payment_status;
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'paid' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800'
        ];

        return $badges[$this->payment_status] ?? 'bg-gray-100 text-gray-800';
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeFailed($query)
    {
        return $query->where('payment_status', 'failed');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    // Methods
    public function isPending()
    {
        return $this->payment_status === 'pending';
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function isFailed()
    {
        return $this->payment_status === 'failed';
    }

    public function isQris()
    {
        return $this->payment_method === 'qris';
    }

    public function isCod()
    {
        return $this->payment_method === 'cod';
    }

    public function isTransfer()
    {
        return $this->payment_method === 'transfer';
    }

    public function markAsPaid()
    {
        $this->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        // Update order status
        if ($this->order) {
            $this->order->update(['status' => 'paid']);
        }

        return $this;
    }

    public function markAsFailed()
    {
        $this->update([
            'payment_status' => 'failed',
        ]);

        return $this;
    }
}