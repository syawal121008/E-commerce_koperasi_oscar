<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerProfile extends Model
{
    use HasFactory;

    protected $table = 'seller_profile';
    protected $primaryKey = 'seller_profile_id';

    protected $fillable = [
        'user_id',
        'store_name',
        'store_description',
        'business_type',
        'business_address',
        'bank_account',
        'bank_name',
        'npwp',
        'verified'
    ];

    protected $casts = [
        'verified' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function products()
    {
        return $this->hasMany(Products::class, 'seller_profile_id', 'seller_profile_id');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('verified', false);
    }

    // Helper methods
    public function isVerified()
    {
        return $this->verified;
    }
}