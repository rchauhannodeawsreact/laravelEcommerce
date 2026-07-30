<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Address;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Create order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variant_options' => 'nullable|array',
            'shipping_address_id' => 'required|integer|exists:addresses,id',
            'billing_address_id' => 'nullable|integer|exists:addresses,id',
            'coins_to_redeem' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $order = $this->orderService->createOrder(
                auth()->id(),
                $validated['items'],
                $validated['shipping_address_id'],
                $validated['billing_address_id'] ?? null,
                $validated['notes'] ?? null,
                $validated['coins_to_redeem'] ?? null
            );

            return $this->success([
                'order' => $order,
                'payment_required' => true,
            ], 'Order created successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Get user orders
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['items', 'shippingAddress'])
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->success($orders, 'Orders retrieved');
    }

    /**
     * Get order details
     */
    public function show($id)
    {
        $order = Order::with([
            'items',
            'shippingAddress',
            'billingAddress',
            'payment',
            'refunds',
        ])->findOrFail($id);

        $this->authorize('view', $order);

        return $this->success($order, 'Order details retrieved');
    }

    /**
     * Cancel order
     */
    public function cancel($id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('update', $order);

        if ($order->status !== 'pending') {
            return $this->error('Order cannot be cancelled at this stage', 422);
        }

        try {
            $this->orderService->cancelOrder($order);
            return $this->success(null, 'Order cancelled successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Get order status timeline
     */
    public function timeline($id)
    {
        $order = Order::findOrFail($id);
        $this->authorize('view', $order);

        $timeline = [
            ['status' => 'created', 'timestamp' => $order->created_at, 'label' => 'Order Created'],
        ];

        if ($order->confirmed_at) {
            $timeline[] = ['status' => 'confirmed', 'timestamp' => $order->confirmed_at, 'label' => 'Payment Confirmed'];
        }

        if ($order->shipped_at) {
            $timeline[] = ['status' => 'shipped', 'timestamp' => $order->shipped_at, 'label' => 'Order Shipped'];
        }

        if ($order->delivered_at) {
            $timeline[] = ['status' => 'delivered', 'timestamp' => $order->delivered_at, 'label' => 'Order Delivered'];
        }

        return $this->success($timeline, 'Order timeline retrieved');
    }
}
