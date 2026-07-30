<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'max_discount_amount',
        'minimum_purchase_amount',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'status',
        'start_date',
        'end_date',
        'applicable_categories',
        'applicable_vendors',
    ];

    protected $casts = [
        'discount_value' => 'float',
        'max_discount_amount' => 'float',
        'minimum_purchase_amount' => 'float',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'applicable_categories' => 'array',
        'applicable_vendors' => 'array',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('end_date', '>', now())
            ->where('start_date', '<=', now());
    }

    public function scopeValid($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhere('usage_count', '<', 'usage_limit');
            });
    }
}
