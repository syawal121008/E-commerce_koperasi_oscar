<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';
    protected $primaryKey = 'review_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment',
        'media_path',
        'helpful_count',
        'verified_purchase'
    ];

    protected $casts = [
        'verified_purchase' => 'boolean',
        'helpful_count' => 'integer',
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'media_url',
        'media_type',
        'formatted_date',
        'rating_stars',
        'file_size_formatted'
    ];

    // ============ RELATIONSHIPS ============

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function product()
    {
        // Try both Product and Products model
        try {
            return $this->belongsTo(Product::class, 'product_id', 'product_id');
        } catch (\Exception $e) {
            return $this->belongsTo(Products::class, 'product_id', 'product_id');
        }
    }

    // ============ SCOPES ============

    public function scopeVerified($query)
    {
        return $query->where('verified_purchase', true);
    }

    public function scopeWithMedia($query)
    {
        return $query->whereNotNull('media_path')
                    ->where('media_path', '!=', '');
    }

    public function scopeWithComment($query)
    {
        return $query->whereNotNull('comment')
                    ->where('comment', '!=', '');
    }

    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function scopeHelpful($query)
    {
        return $query->orderBy('helpful_count', 'desc');
    }

    // ============ ACCESSORS ============

    /**
     * Get media URL with fallback
     */
    public function getMediaUrlAttribute()
    {
        if (!$this->media_path) {
            return null;
        }
        
        try {
            if (Storage::disk('public')->exists($this->media_path)) {
                return Storage::url($this->media_path);
            }
        } catch (\Exception $e) {
            Log::warning('Error accessing media file', [
                'media_path' => $this->media_path,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }

    /**
     * Get media type (image/video)
     */
    public function getMediaTypeAttribute()
    {
        if (!$this->media_path) {
            return null;
        }

        $extension = strtolower(pathinfo($this->media_path, PATHINFO_EXTENSION));
        
        // Image extensions
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'])) {
            return 'image';
        }
        
        // Video extensions
        if (in_array($extension, ['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', '3gp', 'flv', 'wmv'])) {
            return 'video';
        }
        
        return 'unknown';
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->format('d M Y');
    }

    /**
     * Get rating as stars HTML
     */
    public function getRatingStarsAttribute()
    {
        return $this->getStarsHtml();
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeFormattedAttribute()
    {
        if (!$this->media_path) {
            return null;
        }

        try {
            if (!Storage::disk('public')->exists($this->media_path)) {
                return 'File not found';
            }

            $bytes = Storage::disk('public')->size($this->media_path);
            $units = ['B', 'KB', 'MB', 'GB'];
            
            $i = 0;
            while ($bytes > 1024 && $i < count($units) - 1) {
                $bytes /= 1024;
                $i++;
            }
            
            return round($bytes, 2) . ' ' . $units[$i];
        } catch (\Exception $e) {
            Log::warning('Error getting file size', [
                'media_path' => $this->media_path,
                'error' => $e->getMessage()
            ]);
            return 'Unknown size';
        }
    }

    // ============ METHODS ============

    /**
     * Check if media is video
     */
    public function isVideo()
    {
        return $this->media_type === 'video';
    }

    /**
     * Check if media is image
     */
    public function isImage()
    {
        return $this->media_type === 'image';
    }

    /**
     * Check if has media
     */
    public function hasMedia()
    {
        return !empty($this->media_path) && !is_null($this->media_url);
    }

    /**
     * Get stars HTML representation
     */
    public function getStarsHtml($class = 'text-yellow-400', $size = 'text-sm')
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $html .= "<i class='fas fa-star {$class} {$size}'></i>";
            } else {
                $html .= "<i class='far fa-star text-gray-300 {$size}'></i>";
            }
        }
        return $html;
    }

    /**
     * Get file extension
     */
    public function getFileExtension()
    {
        return $this->media_path ? strtolower(pathinfo($this->media_path, PATHINFO_EXTENSION)) : null;
    }

    /**
     * Get MIME type from file
     */
    public function getMimeType()
    {
        if (!$this->media_path || !Storage::disk('public')->exists($this->media_path)) {
            return null;
        }

        try {
            $fullPath = Storage::disk('public')->path($this->media_path);
            return mime_content_type($fullPath);
        } catch (\Exception $e) {
            Log::warning('Error getting MIME type', [
                'media_path' => $this->media_path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get review summary (truncated comment)
     */
    public function getSummary($length = 100)
    {
        if (!$this->comment) return '';
        
        return strlen($this->comment) > $length 
            ? substr($this->comment, 0, $length) . '...'
            : $this->comment;
    }

    /**
     * Check if review is recent (within 30 days)
     */
    public function isRecent()
    {
        return $this->created_at->diffInDays(now()) <= 30;
    }

    /**
     * Get review age in human readable format
     */
    public function getAge()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Check if current user has voted this review as helpful
     */
    public function isHelpfulByUser($userId)
    {
        if (!$userId) return false;
        
        try {
            return \DB::table('review_helpful')
                ->where('review_id', $this->review_id)
                ->where('user_id', $userId)
                ->exists();
        } catch (\Exception $e) {
            Log::error('Error checking helpful vote', [
                'review_id' => $this->review_id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get media thumbnail for video
     */
    public function getVideoThumbnail()
    {
        if (!$this->isVideo() || !$this->hasMedia()) {
            return null;
        }

        // For now, return placeholder. In future, you can generate actual thumbnails
        return 'https://via.placeholder.com/150x150/000000/FFFFFF?text=VIDEO';
    }

    /**
     * Get media display HTML
     */
    public function getMediaDisplayHtml($size = 'w-24 h-24')
    {
        if (!$this->hasMedia()) {
            return '';
        }

        if ($this->isImage()) {
            return "<img src='{$this->media_url}' alt='Review image' class='{$size} object-cover rounded-lg cursor-pointer' onclick=\"openMediaModal('{$this->media_url}', 'image')\">";
        } elseif ($this->isVideo()) {
            return "
                <div class='relative {$size} bg-black rounded-lg overflow-hidden cursor-pointer group' onclick=\"openMediaModal('{$this->media_url}', 'video')\">
                    <video class='w-full h-full object-cover'>
                        <source src='{$this->media_url}' type='" . $this->getMimeType() . "'>
                    </video>
                    <div class='absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center group-hover:bg-opacity-50 transition-all'>
                        <i class='fas fa-play text-white text-lg'></i>
                    </div>
                    <div class='absolute bottom-1 right-1 bg-black bg-opacity-70 text-white text-xs px-1 rounded'>
                        <i class='fas fa-video'></i>
                    </div>
                </div>
            ";
        }

        return '';
    }

    /**
     * Boot method - handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // When deleting review, also delete its media
        static::deleting(function ($review) {
            try {
                if ($review->media_path && Storage::disk('public')->exists($review->media_path)) {
                    Storage::disk('public')->delete($review->media_path);
                    Log::info('Media file deleted with review', [
                        'review_id' => $review->review_id,
                        'media_path' => $review->media_path
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error deleting media file', [
                    'review_id' => $review->review_id,
                    'media_path' => $review->media_path,
                    'error' => $e->getMessage()
                ]);
            }
        });
    }
}