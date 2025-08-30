<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasFactory;

    protected $primaryKey = 'address_id';
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'recipient_name',
        'recipient_phone',
        'phone_number',
        'full_address',
        'district_id',
        'regency_id',
        'province_id',
        'postal_code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function regency()
    {
        return $this->belongsTo(Regency::class, 'regency_id', 'id');
    }

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Orders::class, 'shipping_address_id', 'address_id');
    }

    // Accessors
    public function getFullAddressWithLocationAttribute()
    {
        $address = $this->full_address;
        
        if ($this->district) {
            $address .= ', ' . $this->district->name;
        }
        
        if ($this->regency) {
            $address .= ', ' . $this->regency->name;
        }
        
        if ($this->province) {
            $address .= ', ' . $this->province->name;
        }
        
        if ($this->postal_code) {
            $address .= ' - ' . $this->postal_code;
        }
        
        return $address;
    }

    // Scopes
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Methods
    public function setAsDefault()
    {
        // Remove default from other addresses
        static::where('user_id', $this->user_id)
            ->where('address_id', '!=', $this->address_id)
            ->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
        
        return $this;
    }
}