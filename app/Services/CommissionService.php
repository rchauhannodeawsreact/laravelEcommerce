<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\OrderItem;
use Exception;

class CommissionService
{
    /**
     * Create commission for order item
     */
    public function createCommission(OrderItem $orderItem): Commission
    {
        try {
            $vendor = $orderItem->vendor;
            $commissionPercentage = $vendor->commission_percentage ?? 10;
            $orderAmount = $orderItem->total_price;
            $commissionAmount = ($orderAmount * $commissionPercentage) / 100;
            $vendorAmount = $orderAmount - $commissionAmount;

            $commission = Commission::create([
                'vendor_id' => $orderItem->vendor_id,
                'order_item_id' => $orderItem->id,
                'order_amount' => $orderAmount,
                'commission_percentage' => $commissionPercentage,
                'commission_amount' => $commissionAmount,
                'vendor_amount' => $vendorAmount,
                'platform_fee' => 0,
                'status' => 'pending',
            ]);

            return $commission;
        } catch (Exception $e) {
            throw new Exception('Commission creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Approve commission
     */
    public function approveCommission(Commission $commission): bool
    {
        try {
            $commission->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            return true;
        } catch (Exception $e) {
            throw new Exception('Commission approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Get pending commissions for vendor
     */
    public function getPendingCommissions(int $vendorId)
    {
        return Commission::where('vendor_id', $vendorId)
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Calculate total commission for vendor
     */
    public function getTotalCommission(int $vendorId, ?string $status = null)
    {
        $query = Commission::where('vendor_id', $vendorId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->sum('commission_amount');
    }

    /**
     * Get vendor earnings
     */
    public function getVendorEarnings(int $vendorId)
    {
        return [
            'total_earnings' => Commission::where('vendor_id', $vendorId)->where('status', 'paid')->sum('vendor_amount'),
            'pending_commission' => Commission::where('vendor_id', $vendorId)->whereIn('status', ['pending', 'approved'])->sum('commission_amount'),
            'pending_vendor_amount' => Commission::where('vendor_id', $vendorId)->whereIn('status', ['pending', 'approved'])->sum('vendor_amount'),
        ];
    }
}
