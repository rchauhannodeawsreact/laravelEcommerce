<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'variant_options',
        'quantity',
        'reserved_quantity',
        'minimum_stock',
        'warehouse_location',
        'last_stocked_at',
    ];

    protected $casts = [
        'variant_options' => 'array',
        'last_stocked_at' => 'datetime',
    ];

    // Relationships
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Methods
    public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }

    public function isLowStock()
    {
        return $this->quantity <= $this->minimum_stock;
    }
}
