<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Admin dashboard
     */
    public function admin()
    {
        $this->authorize('isAdmin');

        $data = [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'total_users' => User::where('user_type', 'customer')->count(),
            'total_vendors' => User::where('user_type', 'vendor')->count(),
            'total_orders' => Order::count(),
            'active_vendors' => Vendor::where('status', 'approved')->count(),
            'pending_vendors' => Vendor::where('status', 'pending')->count(),
            'pending_settlements' => 0, // Calculate from settlements
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Payment::where('status', 'completed')
                ->whereDate('completed_at', today())
                ->sum('amount'),
            'recent_orders' => Order::with(['user', 'items'])->latest()->limit(10)->get(),
            'pending_vendors_list' => Vendor::where('status', 'pending')->limit(5)->get(),
        ];

        return $this->success($data, 'Admin dashboard data retrieved');
    }

    /**
     * Vendor dashboard
     */
    public function vendor()
    {
        $this->authorize('isVendor');

        $vendor = Vendor::where('user_id', auth()->id())->firstOrFail();

        $data = [
            'total_products' => $vendor->products()->count(),
            'active_products' => $vendor->products()->where('status', 'approved')->count(),
            'pending_approval' => $vendor->products()->where('status', 'pending_approval')->count(),
            'total_orders' => $vendor->orderItems()->distinct('order_id')->count(),
            'total_sales' => $vendor->orderItems()->sum('total_price'),
            'today_orders' => $vendor->orderItems()
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->whereDate('orders.created_at', today())
                ->distinct('order_id')
                ->count(),
            'pending_commission' => $vendor->commissions()->whereIn('status', ['pending', 'approved'])->sum('commission_amount'),
            'total_earnings' => $vendor->commissions()->where('status', 'paid')->sum('vendor_amount'),
            'rating' => $vendor->rating,
            'recent_orders' => $vendor->orderItems()
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->with(['order', 'product'])
                ->latest('orders.created_at')
                ->limit(10)
                ->get(),
        ];

        return $this->success($data, 'Vendor dashboard data retrieved');
    }

    /**
     * Customer dashboard
     */
    public function customer()
    {
        $user = auth()->user();

        $data = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'pending_orders' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'delivered_orders' => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
            'total_spent' => Order::where('user_id', $user->id)->where('status', 'delivered')->sum('total_amount'),
            'coin_balance' => $user->coins->available_balance ?? 0,
            'wallet_balance' => $user->wallet->balance ?? 0,
            'recent_orders' => Order::where('user_id', $user->id)
                ->with(['items'])
                ->latest()
                ->limit(5)
                ->get(),
        ];

        return $this->success($data, 'Customer dashboard data retrieved');
    }
}
