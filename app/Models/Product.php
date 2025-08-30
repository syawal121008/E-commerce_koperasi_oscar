<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_id';
    protected $table = 'products';

    protected $fillable = [
        'admin_id',
        'name',
        'description',
        'price',
        'modal_price',
        'stock',
        'category_id',
        'image',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'modal_price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_price',
        'formatted_modal_price',
        'stock_status',
        'image_full_url',
        'avg_rating',
        'reviews_count',
    ];

    // ============ RELATIONSHIPS ============
    
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id', 'product_id');
    }

    public function carts()
    {
        return $this->hasMany(Cart::class, 'product_id', 'product_id');
    }

    // ============ SCOPES ============
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeByadmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%');
        });
    }

    public function scopeLowStock($query, $threshold = 10)
    {
        return $query->where('stock', '<', $threshold)->where('stock', '>', 0);
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock', 0);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    // ============ ACCESSOR & MUTATOR ============
    
    /**
     * Get formatted price (Rp 10.000)
     */
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

        public function getFormattedModalPriceAttribute()
    {
        return 'Rp ' . number_format($this->modal_price, 0, ',', '.');
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) {
            return 'Out of Stock';
        } elseif ($this->stock <= 5) {
            return 'Low Stock';
        } else {
            return 'In Stock';
        }
    }

    /**
     * Get full image URL
     */
    public function getImageFullUrlAttribute()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }
        return asset('images/no-image.png'); // Default image
    }

    /**
     * Accessor: average rating
     */
    public function getAvgRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    /**
     * Accessor: reviews count
     */
    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Mutator: Auto set inactive when stock is 0
     */
    public function setStockAttribute($value)
    {
        $this->attributes['stock'] = $value;
        
        // Auto set inactive jika stok 0
        if ($value == 0) {
            $this->attributes['is_active'] = false;
        }
    }

    // ============ METHODS ============
    
    /**
     * Update stock when product is sold
     */
    public function updateStock($quantity)
    {
        if ($this->stock >= $quantity) {
            $this->decrement('stock', $quantity);
            
            // Auto set inactive jika stok habis setelah dikurangi
            if ($this->fresh()->stock == 0) {
                $this->update(['is_active' => false]);
            }
            
            return true;
        }
        return false;
    }

    /**
     * Restore stock (for cancelled orders)
     */
    public function restoreStock($quantity)
    {
        $this->increment('stock', $quantity);
        
        // Jika stok kembali ada dan produk inactive karena stok habis, bisa diaktifkan manual
        // Tapi tidak otomatis aktif, harus manual untuk kontrol admin
        
        return true;
    }

    /**
     * Check if product has sufficient stock
     */
    public function hasStock($quantity = 1)
    {
        return $this->stock >= $quantity;
    }

    /**
     * Get average rating (method)
     */
    public function getAverageRating()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get total reviews count (method)
     */
    public function getReviewsCount()
    {
        return $this->reviews()->count();
    }

    /**
     * Get rating with stars format
     */
    public function getRatingStars()
    {
        $rating = $this->getAverageRating();
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
        $emptyStars = 5 - $fullStars - $halfStar;

        return [
            'full' => $fullStars,
            'half' => $halfStar,
            'empty' => $emptyStars,
            'average' => round($rating, 1)
        ];
    }

    /**
     * Get rating distribution
     */
    public function getRatingDistribution()
    {
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $this->reviews()->where('rating', $i)->count();
        }
        return $distribution;
    }

    /**
     * Toggle product active status
     */
    public function toggleActive()
    {
        if (!$this->is_active && $this->stock == 0) {
            return false;
        }
        
        $this->is_active = !$this->is_active;
        $this->save();
        return $this->is_active;
    }

    /**
     * Check if product can be ordered
     */
    public function canBeOrdered()
    {
        return $this->is_active && $this->stock > 0;
    }

    /**
     * Get similar products (same category, different product)
     */
    public function getSimilarProducts($limit = 4)
    {
        return self::where('category_id', $this->category_id)
                   ->where('product_id', '!=', $this->product_id)
                   ->active()
                   ->inStock()
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'product_id';
    }

    /**
     * Boot method - handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // When deleting product, also delete its image
        static::deleting(function ($product) {
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
        });

        // When updating product
        static::updating(function ($product) {
            // Auto set inactive jika stok 0
            if ($product->isDirty('stock') && $product->stock == 0) {
                $product->is_active = false;
            }
        });

        // When creating product
        static::creating(function ($product) {
            // Auto set inactive jika stok 0 saat create
            if ($product->stock == 0) {
                $product->is_active = false;
            }
        });
    }
}