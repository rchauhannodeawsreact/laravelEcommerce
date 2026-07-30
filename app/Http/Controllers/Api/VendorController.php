<?php

namespace App\Http\Controllers\Api;

use App\Models\Vendor;
use App\Models\Product;
use App\Services\VendorService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    protected $vendorService;
    protected $productService;

    public function __construct(VendorService $vendorService, ProductService $productService)
    {
        $this->vendorService = $vendorService;
        $this->productService = $productService;
    }

    /**
     * Register as vendor
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'shop_description' => 'nullable|string',
            'pan_number' => 'required|string|unique:vendors',
            'gst_number' => 'nullable|string|unique:vendors',
            'bank_account_number' => 'required|string',
            'bank_ifsc' => 'required|string',
            'bank_account_holder' => 'required|string',
            'business_address' => 'required|string',
            'business_city' => 'required|string',
            'business_state' => 'required|string',
            'business_zip' => 'required|string',
        ]);

        try {
            $vendor = $this->vendorService->registerVendor(auth()->id(), $validated);
            return $this->success($vendor, 'Vendor registration submitted for approval', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Get vendor profile
     */
    public function profile()
    {
        $vendor = Vendor::where('user_id', auth()->id())->with('user')->firstOrFail();
        $stats = $this->vendorService->getVendorStats($vendor);

        return $this->success([
            'vendor' => $vendor,
            'stats' => $stats,
        ], 'Vendor profile retrieved');
    }

    /**
     * Update vendor profile
     */
    public function updateProfile(Request $request)
    {
        $vendor = Vendor::where('user_id', auth()->id())->firstOrFail();

        $validated = $request->validate([
            'shop_name' => 'string|max:255',
            'shop_description' => 'nullable|string',
            'bank_account_number' => 'string',
            'bank_ifsc' => 'string',
            'bank_account_holder' => 'string',
        ]);

        try {
            $vendor->update($validated);
            return $this->success($vendor, 'Vendor profile updated');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Get vendor products
     */
    public function products(Request $request)
    {
        $vendor = Vendor::where('user_id', auth()->id())->firstOrFail();
        $products = $this->productService->getVendorProducts(
            $vendor->id,
            $request->input('status', 'approved')
        );

        return $this->success($products, 'Vendor products retrieved');
    }

    /**
     * Get vendor dashboard
     */
    public function dashboard()
    {
        $vendor = Vendor::where('user_id', auth()->id())->firstOrFail();

        $data = [
            'total_products' => $vendor->products()->count(),
            'active_products' => $vendor->products()->where('status', 'approved')->count(),
            'total_orders' => $vendor->orderItems()->distinct('order_id')->count(),
            'pending_commission' => $vendor->commissions()->whereIn('status', ['pending', 'approved'])->sum('commission_amount'),
            'total_earnings' => $vendor->commissions()->where('status', 'paid')->sum('vendor_amount'),
            'available_withdrawal' => $vendor->commissions()->where('status', 'paid')->sum('vendor_amount') - $vendor->settlements()->where('status', 'completed')->sum('net_amount'),
            'rating' => $vendor->rating,
            'total_reviews' => $vendor->total_reviews,
        ];

        return $this->success($data, 'Dashboard data retrieved');
    }
}
