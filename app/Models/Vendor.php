<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'shop_name',
        'shop_slug',
        'shop_description',
        'shop_logo',
        'shop_banner',
        'status',
        'commission_percentage',
        'pan_number',
        'gst_number',
        'bank_account_number',
        'bank_ifsc',
        'bank_account_holder',
        'business_address',
        'business_city',
        'business_state',
        'business_zip',
        'rating',
        'total_reviews',
        'total_products',
        'total_sales',
        'approved_at',
        'rejected_at',
        'suspended_at',
    ];

    protected $casts = [
        'rating' => 'float',
        'commission_percentage' => 'float',
        'total_sales' => 'float',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'approved')->where('suspended_at', null);
    }

    // Methods
    public function getTotalEarningsAttribute()
    {
        return $this->commissions()->where('status', 'paid')->sum('vendor_amount');
    }

    public function getPendingCommissionAttribute()
    {
        return $this->commissions()->whereIn('status', ['pending', 'approved'])->sum('commission_amount');
    }
}
