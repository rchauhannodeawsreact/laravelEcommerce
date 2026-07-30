<?php

namespace App\Services;

use App\Models\Product;
use Exception;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Create product
     */
    public function createProduct(int $vendorId, array $data): Product
    {
        try {
            $data['vendor_id'] = $vendorId;
            $data['slug'] = Str::slug($data['name']);
            $data['status'] = 'pending_approval';

            $product = Product::create($data);

            // Upload images if provided
            if (isset($data['images'])) {
                $this->uploadImages($product, $data['images']);
            }

            // Create variants if provided
            if (isset($data['variants'])) {
                $this->createVariants($product, $data['variants']);
            }

            // Initialize inventory
            $this->initializeInventory($product, $data['stock'] ?? 0);

            return $product;
        } catch (Exception $e) {
            throw new Exception('Product creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Upload product images
     */
    public function uploadImages(Product $product, array $images): void
    {
        foreach ($images as $index => $image) {
            $product->images()->create([
                'image_path' => $image, // Should store actual path from storage
                'sort_order' => $index,
                'is_featured' => $index === 0,
            ]);
        }
    }

    /**
     * Create product variants
     */
    public function createVariants(Product $product, array $variants): void
    {
        foreach ($variants as $variant) {
            $product->variants()->create([
                'name' => $variant['name'],
                'options' => $variant['options'],
            ]);
        }
    }

    /**
     * Initialize product inventory
     */
    public function initializeInventory(Product $product, int $quantity): void
    {
        $product->inventory()->create([
            'sku' => $product->sku,
            'quantity' => $quantity,
            'reserved_quantity' => 0,
            'minimum_stock' => 10,
        ]);
    }

    /**
     * Update product
     */
    public function updateProduct(Product $product, array $data): Product
    {
        try {
            if (isset($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $product->update($data);

            return $product;
        } catch (Exception $e) {
            throw new Exception('Product update failed: ' . $e->getMessage());
        }
    }

    /**
     * Approve product
     */
    public function approveProduct(Product $product): bool
    {
        try {
            $product->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            return true;
        } catch (Exception $e) {
            throw new Exception('Product approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject product
     */
    public function rejectProduct(Product $product, string $reason): bool
    {
        try {
            $product->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            // TODO: Send rejection email to vendor

            return true;
        } catch (Exception $e) {
            throw new Exception('Product rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Get products by vendor
     */
    public function getVendorProducts(int $vendorId, string $status = 'approved')
    {
        return Product::where('vendor_id', $vendorId)
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->with(['images', 'variants', 'inventory'])
            ->paginate(20);
    }

    /**
     * Search products
     */
    public function searchProducts(string $query, ?int $categoryId = null, ?int $vendorId = null)
    {
        return Product::where('status', 'approved')
            ->whereRaw('MATCH (name, description, specification) AGAINST (? IN BOOLEAN MODE)', [$query])
            ->when($categoryId, function ($q) use ($categoryId) {
                return $q->where('category_id', $categoryId);
            })
            ->when($vendorId, function ($q) use ($vendorId) {
                return $q->where('vendor_id', $vendorId);
            })
            ->with(['images', 'vendor'])
            ->paginate(20);
    }
}
