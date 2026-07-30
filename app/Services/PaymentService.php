<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Order;
use Exception;
use Illuminate\Support\Str;

class PaymentService
{
    private $merchantId;
    private $merchantKey;
    private $merchantWebsite;
    private $channelId;
    private $industryTypeId;
    private $callbackUrl;

    public function __construct()
    {
        $this->merchantId = config('paytm.merchant_id');
        $this->merchantKey = config('paytm.merchant_key');
        $this->merchantWebsite = config('paytm.merchant_website');
        $this->channelId = config('paytm.channel_id');
        $this->industryTypeId = config('paytm.industry_type_id');
        $this->callbackUrl = config('paytm.callback_url');
    }

    /**
     * Initiate payment with Paytm
     */
    public function initiatePayment(Order $order, int $userId, string $paymentMethod = 'paytm_card'): array
    {
        try {
            $transactionId = 'TXN-' . $order->id . '-' . time();
            $orderId = $order->order_number;
            $amount = (string)$order->total_amount;

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'initiated',
                'initiated_at' => now(),
            ]);

            // Prepare request parameters
            $paytmParams = [
                'MID' => $this->merchantId,
                'ORDER_ID' => $orderId,
                'CUST_ID' => $userId,
                'TXN_AMOUNT' => $amount,
                'CHANNEL_ID' => $this->channelId,
                'WEBSITE' => $this->merchantWebsite,
                'INDUSTRY_TYPE_ID' => $this->industryTypeId,
                'EMAIL' => auth()->user()->email,
                'MOBILE_NO' => auth()->user()->phone ?? '',
                'CALLBACK_URL' => $this->callbackUrl,
                'TXN_TYPE' => 'TXN',
            ];

            // Generate checksum
            $paytmParams['CHECKSUMHASH'] = $this->generateChecksum($paytmParams);

            // Get payment URL
            $paymentUrl = $this->getPaymentUrl($paytmParams);

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'payment_url' => $paymentUrl,
                'order_id' => $order->id,
            ];
        } catch (Exception $e) {
            throw new Exception('Payment initiation failed: ' . $e->getMessage());
        }
    }

    /**
     * Verify payment callback from Paytm
     */
    public function verifyCallback(array $callbackData): array
    {
        try {
            $checksumHash = $callbackData['CHECKSUMHASH'] ?? null;
            unset($callbackData['CHECKSUMHASH']);

            $isValidChecksum = $this->verifyChecksum($callbackData, $checksumHash);

            if (!$isValidChecksum) {
                throw new Exception('Invalid checksum');
            }

            $transactionId = $callbackData['TXNID'] ?? null;
            $orderId = $callbackData['ORDERID'] ?? null;
            $status = $callbackData['STATUS'] ?? 'FAILED';
            $amount = $callbackData['TXNAMOUNT'] ?? 0;

            // Update payment record
            $payment = Payment::where('transaction_id', 'like', '%' . $orderId . '%')->firstOrFail();

            if ($status === 'TXN_SUCCESS') {
                $payment->update([
                    'status' => 'completed',
                    'paytm_transaction_id' => $transactionId,
                    'response_data' => $callbackData,
                    'completed_at' => now(),
                ]);

                // Update order status
                $order = $payment->order;
                $order->update([
                    'payment_status' => 'completed',
                    'confirmed_at' => now(),
                ]);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'failure_reason' => $callbackData['RESPMSG'] ?? 'Unknown error',
                    'response_data' => $callbackData,
                    'failed_at' => now(),
                ]);

                $order = $payment->order;
                $order->update(['payment_status' => 'failed']);
            }

            return [
                'success' => $status === 'TXN_SUCCESS',
                'transaction_id' => $transactionId,
                'order_id' => $orderId,
                'status' => $status,
                'message' => $callbackData['RESPMSG'] ?? 'Payment processed',
            ];
        } catch (Exception $e) {
            throw new Exception('Callback verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate checksum for Paytm
     */
    public function generateChecksum(array $params): string
    {
        $salt = $this->merchantKey;
        
        ksort($params);
        $string = implode('|', array_values($params));
        $string = $salt . '|' . $string;
        
        $hash = hash('SHA256', $string, true);
        $checksum = base64_encode($hash);
        
        return $checksum;
    }

    /**
     * Verify checksum from Paytm response
     */
    public function verifyChecksum(array $params, $checksum): bool
    {
        $salt = $this->merchantKey;
        
        ksort($params);
        $string = implode('|', array_values($params));
        $string = $salt . '|' . $string;
        
        $hash = hash('SHA256', $string, true);
        $calculatedChecksum = base64_encode($hash);
        
        return $checksum === $calculatedChecksum;
    }

    /**
     * Get Paytm payment gateway URL
     */
    private function getPaymentUrl(array $params): string
    {
        $isProduction = config('app.env') === 'production';
        $baseUrl = $isProduction 
            ? 'https://securegw.paytm.in/theia/api/v1/showPaymentPage'
            : 'https://securegw-stage.paytm.in/theia/api/v1/showPaymentPage';

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Get payment by order ID
     */
    public function getPaymentByOrder(int $orderId): ?Payment
    {
        return Payment::where('order_id', $orderId)->latest()->first();
    }
}
