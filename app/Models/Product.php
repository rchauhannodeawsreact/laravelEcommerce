<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'description',
        'specification',
        'price',
        'cost_price',
        'discount_price',
        'discount_percentage',
        'sku',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'minimum_order_quantity',
        'is_b2b',
        'status',
        'rating',
        'total_reviews',
        'total_sold',
        'is_featured',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'price' => 'float',
        'cost_price' => 'float',
        'discount_price' => 'float',
        'rating' => 'float',
        'is_b2b' => 'boolean',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Relationships
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
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

    public function scopeActive($query)
    {
        return $query->where('status', 'approved')->where('is_active', true);
    }

    public function scopeB2B($query)
    {
        return $query->where('is_b2b', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Methods
    public function getDiscountedPriceAttribute()
    {
        return $this->discount_price ?? $this->price;
    }

    public function getTotalStockAttribute()
    {
        return $this->inventory()->sum('quantity');
    }

    public function getAvailableStockAttribute()
    {
        return $this->inventory()->sum('quantity') - $this->inventory()->sum('reserved_quantity');
    }
}
