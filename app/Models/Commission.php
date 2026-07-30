<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'order_item_id',
        'order_amount',
        'commission_percentage',
        'commission_amount',
        'vendor_amount',
        'platform_fee',
        'status',
        'notes',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'order_amount' => 'float',
        'commission_percentage' => 'float',
        'commission_amount' => 'float',
        'vendor_amount' => 'float',
        'platform_fee' => 'float',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
