<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Inventory;
use App\Models\Commission;
use Exception;
use Illuminate\Support\Str;

class OrderService
{
    protected $coinService;
    protected $commissionService;

    public function __construct(CoinService $coinService, CommissionService $commissionService)
    {
        $this->coinService = $coinService;
        $this->commissionService = $commissionService;
    }

    /**
     * Create order from cart items
     */
    public function createOrder(int $userId, array $items, int $shippingAddressId, ?int $billingAddressId = null, ?string $notes = null, ?float $coinsRedeemed = null): Order
    {
        try {
            $subtotal = 0;
            $tax = 0;
            $orderNumber = 'ORD-' . date('Ymd') . '-' . Str::random(6);
            $shippingCost = 0; // Can be calculated based on address and items

            // Calculate totals and validate stock
            foreach ($items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $quantity = $item['quantity'];

                // Check inventory
                $inventory = Inventory::where('product_id', $product->id)->firstOrFail();
                if ($inventory->available_quantity < $quantity) {
                    throw new Exception("Insufficient stock for {$product->name}");
                }

                $itemPrice = $product->discount_price ?? $product->price;
                $itemTotal = $itemPrice * $quantity;
                $subtotal += $itemTotal;

                // Calculate tax (assuming 18% GST)
                $tax += $itemTotal * 0.18;
            }

            $discountAmount = 0;
            $coinsRedeemValue = 0;

            if ($coinsRedeemed) {
                $coinsRedeemValue = $this->coinService->convertCoinsToDiscount($coinsRedeemed);
                $discountAmount += $coinsRedeemValue;
                $this->coinService->redeemCoins($userId, $coinsRedeemed, 'Coins redeemed for order discount');
            }

            $totalAmount = $subtotal + $tax + $shippingCost - $discountAmount;

            // Create order
            $order = Order::create([
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'shipping_address_id' => $shippingAddressId,
                'billing_address_id' => $billingAddressId ?? $shippingAddressId,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax' => $tax,
                'discount_amount' => $discountAmount,
                'coins_redeemed_value' => $coinsRedeemValue,
                'total_amount' => max(0, $totalAmount),
                'payment_status' => 'pending',
                'notes' => $notes,
            ]);

            // Create order items and reserve inventory
            foreach ($items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $quantity = $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'vendor_id' => $product->vendor_id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'variant_options' => $item['variant_options'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $product->discount_price ?? $product->price,
                    'total_price' => ($product->discount_price ?? $product->price) * $quantity,
                    'status' => 'pending',
                ]);

                // Reserve inventory
                $inventory = Inventory::where('product_id', $product->id)->first();
                if ($inventory) {
                    $inventory->reserved_quantity += $quantity;
                    $inventory->save();
                }
            }

            return $order;
        } catch (Exception $e) {
            throw new Exception('Order creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Confirm order after payment
     */
    public function confirmOrder(Order $order): bool
    {
        try {
            $order->markAsConfirmed();

            // Deduct from inventory
            foreach ($order->items as $item) {
                $inventory = Inventory::where('product_id', $item->product_id)->first();
                if ($inventory) {
                    $inventory->quantity -= $item->quantity;
                    $inventory->reserved_quantity -= $item->quantity;
                    $inventory->save();
                }
            }

            // Award coins
            $this->coinService->awardPurchaseCoins($order->user_id, $order->total_amount, $order->id);

            // Generate commissions
            foreach ($order->items as $item) {
                $this->commissionService->createCommission($item);
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Order confirmation failed: ' . $e->getMessage());
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Order $order, string $reason = ''): bool
    {
        try {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            // Release reserved inventory
            foreach ($order->items as $item) {
                $inventory = Inventory::where('product_id', $item->product_id)->first();
                if ($inventory) {
                    $inventory->reserved_quantity -= $item->quantity;
                    $inventory->save();
                }
            }

            return true;
        } catch (Exception $e) {
            throw new Exception('Order cancellation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get order details
     */
    public function getOrder(int $orderId): ?Order
    {
        return Order::with(['items', 'shippingAddress', 'user'])->find($orderId);
    }
}
