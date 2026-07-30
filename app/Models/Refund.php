<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'order_id',
        'vendor_id',
        'refund_number',
        'amount',
        'reason',
        'customer_notes',
        'status',
        'tracking_number',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'refund_completed_at',
    ];

    protected $casts = [
        'amount' => 'float',
        'rejection_reason' => 'array',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'refund_completed_at' => 'datetime',
    ];

    // Relationships
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
