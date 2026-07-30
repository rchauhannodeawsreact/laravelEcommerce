<?php

namespace App\Services;

use App\Models\Settlement;
use App\Models\Commission;
use App\Models\Vendor;
use Exception;
use Illuminate\Support\Str;

class SettlementService
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Create settlement for vendor
     */
    public function createSettlement(int $vendorId, ?string $periodFrom = null, ?string $periodTo = null): Settlement
    {
        try {
            $vendor = Vendor::findOrFail($vendorId);
            $minAmount = config('app.min_settlement_amount', 1000);

            // Get pending and approved commissions
            $commissions = Commission::where('vendor_id', $vendorId)
                ->whereIn('status', ['pending', 'approved'])
                ->when($periodFrom, function ($q) use ($periodFrom) {
                    return $q->where('created_at', '>=', $periodFrom);
                })
                ->when($periodTo, function ($q) use ($periodTo) {
                    return $q->where('created_at', '<=', $periodTo);
                })
                ->get();

            $totalCommission = $commissions->sum('vendor_amount');

            if ($totalCommission < $minAmount) {
                throw new Exception("Minimum settlement amount is {$minAmount}. Current: {$totalCommission}");
            }

            $settlementNumber = 'SET-' . $vendor->id . '-' . date('Ymd') . '-' . Str::random(4);
            $totalDeductions = 0; // Can include processing fees, disputes, etc.
            $netAmount = $totalCommission - $totalDeductions;

            $settlement = Settlement::create([
                'vendor_id' => $vendorId,
                'settlement_number' => $settlementNumber,
                'total_commission' => $totalCommission,
                'total_deductions' => $totalDeductions,
                'net_amount' => $netAmount,
                'status' => 'pending',
                'settlement_period_from' => $periodFrom ?? now()->startOfMonth(),
                'settlement_period_to' => $periodTo ?? now()->endOfMonth(),
            ]);

            return $settlement;
        } catch (Exception $e) {
            throw new Exception('Settlement creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Approve settlement
     */
    public function approveSettlement(Settlement $settlement): bool
    {
        try {
            $settlement->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            return true;
        } catch (Exception $e) {
            throw new Exception('Settlement approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Process settlement via Paytm
     */
    public function processSettlement(Settlement $settlement): bool
    {
        try {
            $vendor = $settlement->vendor;

            if (!$vendor->bank_account_number || !$vendor->bank_ifsc) {
                throw new Exception('Vendor bank details are incomplete');
            }

            $settlement->update(['status' => 'processing', 'processed_at' => now()]);

            // TODO: Integrate with Paytm Settlement API
            // For now, we'll mark as completed
            $settlement->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Update commission statuses
            Commission::where('vendor_id', $vendor->id)
                ->whereIn('status', ['pending', 'approved'])
                ->update(['status' => 'paid', 'paid_at' => now()]);

            return true;
        } catch (Exception $e) {
            $settlement->update([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $e->getMessage(),
            ]);
            throw new Exception('Settlement processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Get pending settlements
     */
    public function getPendingSettlements()
    {
        return Settlement::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Get settlement history for vendor
     */
    public function getVendorSettlementHistory(int $vendorId, int $limit = 50)
    {
        return Settlement::where('vendor_id', $vendorId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
