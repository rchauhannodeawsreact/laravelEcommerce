<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'vendor_id',
        'product_name',
        'sku',
        'variant_options',
        'quantity',
        'unit_price',
        'discount_per_unit',
        'total_price',
        'status',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'variant_options' => 'array',
        'unit_price' => 'float',
        'discount_per_unit' => 'float',
        'total_price' => 'float',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
