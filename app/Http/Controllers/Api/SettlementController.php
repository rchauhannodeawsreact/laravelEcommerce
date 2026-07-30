<?php

namespace App\Http\Controllers\Api;

use App\Models\Settlement;
use App\Models\Vendor;
use App\Services\SettlementService;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    protected $settlementService;

    public function __construct(SettlementService $settlementService)
    {
        $this->settlementService = $settlementService;
    }

    /**
     * Get vendor settlements
     */
    public function index(Request $request)
    {
        $vendor = Vendor::where('user_id', auth()->id())->firstOrFail();
        $settlements = $this->settlementService->getVendorSettlementHistory(
            $vendor->id,
            $request->input('limit', 50)
        );

        return $this->success($settlements, 'Settlements retrieved');
    }

    /**
     * Create settlement request
     */
    public function store(Request $request)
    {
        $vendor = Vendor::where('user_id', auth()->id())->firstOrFail();

        try {
            $settlement = $this->settlementService->createSettlement($vendor->id);
            return $this->success($settlement, 'Settlement created successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Get settlement details
     */
    public function show($id)
    {
        $settlement = Settlement::findOrFail($id);
        $vendor = Vendor::where('user_id', auth()->id())->firstOrFail();

        if ($settlement->vendor_id !== $vendor->id) {
            return $this->error('Unauthorized', 403);
        }

        return $this->success($settlement, 'Settlement details retrieved');
    }
}
