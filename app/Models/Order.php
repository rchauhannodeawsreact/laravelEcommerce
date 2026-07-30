<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'shipping_address_id',
        'billing_address_id',
        'subtotal',
        'shipping_cost',
        'tax',
        'discount_amount',
        'coins_redeemed_value',
        'total_amount',
        'payment_method',
        'transaction_id',
        'payment_status',
        'notes',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'shipping_cost' => 'float',
        'tax' => 'float',
        'discount_amount' => 'float',
        'coins_redeemed_value' => 'float',
        'total_amount' => 'float',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'transaction_id', 'transaction_id');
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
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }

    // Methods
    public function markAsConfirmed()
    {
        $this->update(['status' => 'confirmed', 'confirmed_at' => now()]);
    }

    public function markAsShipped()
    {
        $this->update(['status' => 'shipped', 'shipped_at' => now()]);
    }

    public function markAsDelivered()
    {
        $this->update(['status' => 'delivered', 'delivered_at' => now()]);
    }
}
