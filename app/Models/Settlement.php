<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'settlement_number',
        'total_commission',
        'total_deductions',
        'net_amount',
        'status',
        'paytm_transaction_id',
        'settlement_details',
        'failure_reason',
        'settlement_period_from',
        'settlement_period_to',
        'approved_at',
        'processed_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'total_commission' => 'float',
        'total_deductions' => 'float',
        'net_amount' => 'float',
        'settlement_details' => 'array',
        'settlement_period_from' => 'date',
        'settlement_period_to' => 'date',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
