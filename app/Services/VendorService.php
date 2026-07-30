<?php

namespace App\Services;

use App\Models\Vendor;
use Exception;
use Illuminate\Support\Str;

class VendorService
{
    /**
     * Create vendor registration
     */
    public function registerVendor(int $userId, array $data): Vendor
    {
        try {
            $data['shop_slug'] = Str::slug($data['shop_name']);
            $data['status'] = 'pending';
            $data['user_id'] = $userId;

            $vendor = Vendor::create($data);

            return $vendor;
        } catch (Exception $e) {
            throw new Exception('Vendor registration failed: ' . $e->getMessage());
        }
    }

    /**
     * Approve vendor
     */
    public function approveVendor(Vendor $vendor): bool
    {
        try {
            $vendor->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            // TODO: Send approval email

            return true;
        } catch (Exception $e) {
            throw new Exception('Vendor approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject vendor
     */
    public function rejectVendor(Vendor $vendor, string $reason): bool
    {
        try {
            $vendor->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            // TODO: Send rejection email with reason

            return true;
        } catch (Exception $e) {
            throw new Exception('Vendor rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Suspend vendor
     */
    public function suspendVendor(Vendor $vendor, string $reason): bool
    {
        try {
            $vendor->update([
                'status' => 'suspended',
                'suspended_at' => now(),
            ]);

            // TODO: Send suspension email

            return true;
        } catch (Exception $e) {
            throw new Exception('Vendor suspension failed: ' . $e->getMessage());
        }
    }

    /**
     * Update vendor commission percentage
     */
    public function updateCommissionPercentage(Vendor $vendor, float $percentage): bool
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new Exception('Commission percentage must be between 0 and 100');
        }

        try {
            $vendor->update(['commission_percentage' => $percentage]);
            return true;
        } catch (Exception $e) {
            throw new Exception('Commission update failed: ' . $e->getMessage());
        }
    }

    /**
     * Get vendor statistics
     */
    public function getVendorStats(Vendor $vendor): array
    {
        return [
            'total_products' => $vendor->products()->count(),
            'active_products' => $vendor->products()->where('status', 'approved')->count(),
            'total_orders' => $vendor->orderItems()->distinct('order_id')->count(),
            'total_sales' => $vendor->orderItems()->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'delivered')
                ->sum('order_items.total_price'),
            'average_rating' => $vendor->rating,
            'total_reviews' => $vendor->total_reviews,
        ];
    }

    /**
     * Get pending vendors
     */
    public function getPendingVendors()
    {
        return Vendor::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
