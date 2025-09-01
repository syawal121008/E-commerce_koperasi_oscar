<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\QrCodeService;
use App\Notifications\ResetPasswordNotification; // tambahkan di atas


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_GURU = 'guru';
    const ROLE_CUSTOMER = 'customer';

    protected $fillable = [
        'full_name',
        'student_id',
        'email',
        'password',
        'role',
        'balance',
        'qr_code',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get profile photo URL with proper fallback
     */
     public function getProfilePhotoUrlAttribute()
    {
        // Jika ada profile_photo dan file exists di storage
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            return Storage::url($this->profile_photo);
        }
        
        // Jika tidak ada, gunakan default avatar
        return asset('images/default-avatar.png');
    }

    /**
     * Alternative method - keep for backward compatibility
     */
    public function profilePhotoUrl()
    {
        return $this->profile_photo_url;
    }

    /**
     * Get profile photo path (untuk debugging)
     */
    public function getProfilePhotoPath()
    {
        return $this->profile_photo;
    }

    /**
     * Check if profile photo exists
     */
    public function hasProfilePhoto()
    {
        return $this->profile_photo && Storage::disk('public')->exists($this->profile_photo);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Delete profile photo file
     */
    public function deleteProfilePhoto(): bool
    {
        if ($this->profile_photo && Storage::disk('public')->exists($this->profile_photo)) {
            $deleted = Storage::disk('public')->delete($this->profile_photo);
            if ($deleted) {
                $this->profile_photo = null;
                $this->save();
                Log::info('Profile photo deleted for user: ' . $this->user_id);
            }
            return $deleted;
        }
        return true;
    }
    
    public function getQrCodeUrlAttribute()
    {
        if (empty($this->qr_code)) {
            return null;
        }
        
        if (str_contains($this->qr_code, 'qrcodes/')) {
            if (!Storage::disk('public')->exists($this->qr_code)) {
                Log::error("QR Code file not found: ".$this->qr_code);
                return null;
            }
            return Storage::disk('public')->url($this->qr_code);
        }
        
        if ($this->isValidBase64($this->qr_code)) {
            return 'data:image/png;base64,' . $this->qr_code;
        }
        
        return null;
    }

    public function getRoleNameAttribute()
    {
        $map = [
            'customer' => 'Siswa',
            'guru'     => 'Kepala Koperasi',
            'admin'    => 'Admin Koperasi',
        ];

        return $map[$this->role] ?? $this->role;
    }

    /**
     * Generate and save QR code to database
     */
    public function generateAndSaveQrCode(): bool
    {
        try {
            Log::info('Generating QR code for user: ' . $this->user_id);
            
            $qrPath = app(QrCodeService::class)->generateForUser($this);
            
            $this->qr_code = $qrPath;
            $result = $this->save();
            
            if ($result) {
                Log::info('QR Code saved paidfully for user ' . $this->user_id . ' at path: ' . $qrPath);
            } else {
                throw new \Exception('Failed to save QR code to database');
            }

            return $result;
            
        } catch (\Exception $e) {
            Log::error('Error generating and saving QR code for user ' . $this->user_id . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Regenerate QR code (only when explicitly requested)
     */
    public function regenerateQrCode(): string
    {
        try {
            Log::info('Regenerating QR code for user: ' . $this->user_id);
            
            if (!empty($this->qr_code) && str_contains($this->qr_code, 'qrcodes/')) {
                Storage::disk('public')->delete($this->qr_code);
            }
            
            $newQrPath = app(QrCodeService::class)->generateForUser($this);
            
            $this->qr_code = $newQrPath;
            $result = $this->save();
            
            if (!$result) {
                throw new \Exception('Failed to save regenerated QR code to database');
            }

            Log::info('QR Code regenerated paidfully for user ' . $this->user_id);
            return $this->qr_code;
            
        } catch (\Exception $e) {
            Log::error('Error regenerating QR code for user ' . $this->user_id . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if user has valid QR code
     */
    public function hasValidQrCode(): bool
    {
        if (empty($this->qr_code)) {
            return false;
        }
        
        if (str_contains($this->qr_code, 'qrcodes/')) {
            return Storage::disk('public')->exists($this->qr_code);
        }
        
        return $this->isValidBase64($this->qr_code);
    }

    /**
     * Simple base64 validation
     */
    private function isValidBase64(string $base64String): bool
    {
        try {
            $decoded = base64_decode($base64String, true);
            return $decoded !== false && !empty($decoded);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get QR code data
     */
    public function getQrCodeData(): ?array
    {
        // ================================================================
        // PERBAIKAN: Data disederhanakan untuk menghindari "code length overflow".
        // Hanya user_id yang dimasukkan ke QR. Data lain akan diambil dari DB
        // oleh controller setelah scan berhasil.
        // ================================================================
        return [
            'user_id' => $this->user_id,
        ];
    }

    // Balance methods
  // Di User model
public function addBalance($amount)
{
    $this->increment('balance', $amount);
    return true;
}

public function deductBalance($amount)
{
    if ($this->balance < $amount) {
        return false;
    }
    $this->decrement('balance', $amount);
    return true;
}

public function hasSufficientBalance($amount)
{
    return $this->balance >= $amount;
}

    // Role methods
    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isGuru()
    {
        return $this->role === self::ROLE_GURU;
    }

    public function isCustomer()
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isUser()
    {
        return $this->isCustomer();
    }

    public function hasRole($roles)
    {
        if (is_string($roles)) {
            return $this->role === $roles;
        }
        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }
        return false;
    }

    public function hasAnyRole($roles)
    {
        return $this->hasRole($roles);
    }

    public function canSell()
    {
        return $this->isAdmin() || $this->isGuru();
    }

    public function canManageAdmin()
    {
        return $this->isAdmin();
    }

    public function getRoleName()
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_GURU => 'Guru',
            self::ROLE_CUSTOMER => 'Customer',
            default => 'Unknown',
        };
    }

    public function setRole($role)
    {
        $allowedRoles = [self::ROLE_ADMIN, self::ROLE_GURU, self::ROLE_CUSTOMER];
        
        if (in_array($role, $allowedRoles)) {
            $this->role = $role;
            $this->save();
            return true;
        }
        return false;
    }

    // Relationships
    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function guruOrders()
    {
        return $this->hasMany(Order::class, 'admin_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'admin_id');
    }

    public function topups()
    {
        return $this->hasMany(Topup::class, 'user_id', 'user_id');
    }

    public function transactions()
{
    return $this->hasMany(Transaction::class, 'user_id', 'user_id');
}


    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    public function scopeGurus($query)
    {
        return $query->where('role', self::ROLE_GURU);
    }

    public function scopeCustomers($query)
    {
        return $query->where('role', self::ROLE_CUSTOMER);
    }

    public function scopeUsers($query)
    {
        return $query->where('role', self::ROLE_CUSTOMER);
    }

    public function scopeCanSell($query)
    {
        return $query->whereIn('role', [self::ROLE_ADMIN, self::ROLE_GURU]);
    }
}