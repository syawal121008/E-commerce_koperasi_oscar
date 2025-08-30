<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'order_items';
    protected $primaryKey = 'item_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'order_id',
        'product_id', 
        'quantity',
        'unit_price',
        'modal_price',   
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'modal_price' => 'decimal:2',   
        'subtotal' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_unit_price',
        'formatted_modal_price',
        'formatted_subtotal',
        'formatted_total_price',
        'profit',               
        'formatted_profit',       
    ];

    public function getOrderItemIdAttribute()
    {
        return $this->item_id;
    }

    public function getCartItemIdAttribute()
    {
        return 'cart_' . $this->item_id;
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($orderItem) {
            // PERBAIKAN: Pastikan modal_price tersimpan dengan benar
            if (is_null($orderItem->modal_price) || $orderItem->modal_price <= 0) {
                if ($orderItem->product) {
                    // Ambil modal price dari produk saat ini (snapshot)
                    $orderItem->modal_price = $orderItem->product->modal_price ?? 0;
                } else {
                    // Jika produk tidak ditemukan, coba ambil dari database
                    $product = Product::find($orderItem->product_id);
                    $orderItem->modal_price = $product ? ($product->modal_price ?? 0) : 0;
                }
            }

            // Hitung subtotal
            $orderItem->subtotal = $orderItem->unit_price * $orderItem->quantity;
        });

        static::creating(function ($orderItem) {
            // Pastikan modal_price diset saat membuat record baru
            if (is_null($orderItem->modal_price) || $orderItem->modal_price <= 0) {
                $product = $orderItem->product ?: Product::find($orderItem->product_id);
                if ($product) {
                    $orderItem->modal_price = $product->modal_price ?? 0;
                }
            }
        });
    }

    public function getFormattedUnitPriceAttribute()
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    public function getFormattedModalPriceAttribute()
    {
        return 'Rp ' . number_format($this->modal_price ?? 0, 0, ',', '.');
    }

    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getTotalPriceAttribute()
    {
        return $this->unit_price * $this->quantity;
    }

    public function getFormattedTotalPriceAttribute()
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    public function getProfitAttribute()
    {
        $modalPrice = $this->modal_price ?? 0;
        return ($this->unit_price - $modalPrice) * $this->quantity;
    }

    public function getFormattedProfitAttribute()
    {
        return 'Rp ' . number_format($this->profit, 0, ',', '.');
    }

    public function calculateSubtotal()
    {
        $this->subtotal = $this->unit_price * $this->quantity;
        return $this->subtotal;
    }

    public function updateSubtotal()
    {
        $this->subtotal = $this->unit_price * $this->quantity;
        $this->save();
        return $this;
    }

    public function validateStock()
    {
        if ($this->product && $this->product->stock < $this->quantity) {
            return false;
        }
        return true;
    }

    public function hasValidProduct()
    {
        return $this->product && $this->product->is_active;
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function getRouteKeyName()
    {
        return 'item_id';
    }

    public function toCartFormat()
    {
        return [
            'cart_item_id' => $this->cart_item_id,
            'product_id' => $this->product_id,
            'admin_id' => $this->product->admin_id ?? null,
            'name' => $this->product->name ?? 'Unknown Product',
            'image' => $this->product->image ?? null,
            'unit_price' => (float) $this->unit_price,
            'modal_price' => (float) ($this->modal_price ?? 0),
            'quantity' => $this->quantity,
            'subtotal' => (float) $this->subtotal,
            'profit' => (float) $this->profit,  
            'stock' => $this->product->stock ?? 0,
            'admin_name' => $this->product->admin->full_name ?? 'Unknown Shop',
            'selected' => false
        ];
    }

    public static function createFromCartItem($orderId, $cartItem)
    {
        // Ambil produk untuk mendapatkan modal_price terkini
        $product = Product::find($cartItem['product_id']);
        $modalPrice = $cartItem['modal_price'] ?? ($product ? $product->modal_price : 0);

        return self::create([
            'order_id' => $orderId,
            'product_id' => $cartItem['product_id'],
            'quantity' => $cartItem['quantity'],
            'unit_price' => $cartItem['unit_price'],
            'modal_price' => $modalPrice, // Pastikan modal_price disimpan
            'subtotal' => $cartItem['subtotal']
        ]);
    }

    public static function createFromCartItems($orderId, array $cartItems)
    {
        $orderItems = [];
        
        foreach ($cartItems as $cartItem) {
            $orderItems[] = self::createFromCartItem($orderId, $cartItem);
        }
        
        return collect($orderItems);
    }

    /**
     * Get profit per unit
     */
    public function getProfitPerUnitAttribute()
    {
        $modalPrice = $this->modal_price ?? 0;
        return $this->unit_price - $modalPrice;
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMarginAttribute()
    {
        if ($this->unit_price <= 0) {
            return 0;
        }
        
        $modalPrice = $this->modal_price ?? 0;
        $profit = $this->unit_price - $modalPrice;
        return ($profit / $this->unit_price) * 100;
    }
}