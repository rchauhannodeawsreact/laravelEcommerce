<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\Order;
use App\Services\PaymentService;
use App\Services\OrderService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $orderService;

    public function __construct(PaymentService $paymentService, OrderService $orderService)
    {
        $this->paymentService = $paymentService;
        $this->orderService = $orderService;
    }

    /**
     * Initiate payment
     */
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'payment_method' => 'required|in:paytm_card,paytm_wallet,paytm_netbanking,paytm_upi',
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);
            $this->authorize('payment', $order);

            $result = $this->paymentService->initiatePayment(
                $order,
                auth()->id(),
                $validated['payment_method']
            );

            return $this->success($result, 'Payment initiated', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Paytm callback handler
     */
    public function callback(Request $request)
    {
        try {
            $callbackData = $request->all();
            $result = $this->paymentService->verifyCallback($callbackData);

            if ($result['success']) {
                // Find order and confirm it
                $payment = Payment::where('paytm_transaction_id', $result['transaction_id'])->first();
                if ($payment && $payment->order) {
                    $this->orderService->confirmOrder($payment->order);
                }
            }

            return $this->success($result, 'Payment processed');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Get payment status
     */
    public function status($orderId)
    {
        $order = Order::findOrFail($orderId);
        $this->authorize('view', $order);

        $payment = $this->paymentService->getPaymentByOrder($orderId);

        if (!$payment) {
            return $this->error('No payment found for this order', 404);
        }

        return $this->success($payment, 'Payment status retrieved');
    }

    /**
     * Get payment history
     */
    public function history(Request $request)
    {
        $payments = Payment::where('user_id', auth()->id())
            ->with('order')
            ->when($request->filled('status'), function ($q) use ($request) {
                $q->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->success($payments, 'Payment history retrieved');
    }
}
