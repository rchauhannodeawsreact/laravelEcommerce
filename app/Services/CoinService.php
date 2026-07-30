<?php

namespace App\Services;

use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\CoinTransfer;
use Exception;

class CoinService
{
    /**
     * Add coins to user account
     */
    public function addCoins(int $userId, float $amount, string $description, string $type = 'earning', ?string $referenceType = null, ?int $referenceId = null): bool
    {
        try {
            $coin = Coin::firstOrCreate(['user_id' => $userId]);
            
            $coin->total_balance += $amount;
            $coin->available_balance += $amount;
            $coin->last_updated_at = now();
            $coin->save();

            CoinTransaction::create([
                'user_id' => $userId,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'status' => 'completed',
            ]);

            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to add coins: ' . $e->getMessage());
        }
    }

    /**
     * Redeem coins for discount
     */
    public function redeemCoins(int $userId, float $amount, string $description, ?int $orderId = null): bool
    {
        try {
            $coin = Coin::where('user_id', $userId)->firstOrFail();

            if ($coin->available_balance < $amount) {
                throw new Exception('Insufficient coin balance. Available: ' . $coin->available_balance);
            }

            $coin->available_balance -= $amount;
            $coin->total_balance -= $amount;
            $coin->last_updated_at = now();
            $coin->save();

            CoinTransaction::create([
                'user_id' => $userId,
                'type' => 'redemption',
                'amount' => -$amount,
                'description' => $description,
                'reference_type' => 'order',
                'reference_id' => $orderId,
                'status' => 'completed',
            ]);

            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to redeem coins: ' . $e->getMessage());
        }
    }

    /**
     * Transfer coins between users
     */
    public function transferCoins(int $senderId, int $receiverId, float $amount, ?string $message = null): CoinTransfer
    {
        try {
            $senderCoins = Coin::where('user_id', $senderId)->firstOrFail();

            if ($senderCoins->available_balance < $amount) {
                throw new Exception('Insufficient balance for transfer');
            }

            // Deduct from sender
            $senderCoins->available_balance -= $amount;
            $senderCoins->locked_balance += $amount;
            $senderCoins->save();

            // Create transfer record
            $transfer = CoinTransfer::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'amount' => $amount,
                'message' => $message,
                'status' => 'pending',
            ]);

            // Add to receiver
            $this->addCoins($receiverId, $amount, "Received coins from transfer (ID: {$transfer->id})", 'transfer_received', 'coin_transfer', $transfer->id);

            // Complete transfer
            $senderCoins->locked_balance -= $amount;
            $senderCoins->save();

            $transfer->update(['status' => 'completed', 'completed_at' => now()]);

            CoinTransaction::create([
                'user_id' => $senderId,
                'type' => 'transfer_sent',
                'amount' => -$amount,
                'description' => "Transferred coins to user {$receiverId}",
                'reference_type' => 'coin_transfer',
                'reference_id' => $transfer->id,
                'status' => 'completed',
            ]);

            return $transfer;
        } catch (Exception $e) {
            throw new Exception('Transfer failed: ' . $e->getMessage());
        }
    }

    /**
     * Get user coin balance
     */
    public function getBalance(int $userId): ?Coin
    {
        return Coin::where('user_id', $userId)->first();
    }

    /**
     * Award purchase coins
     */
    public function awardPurchaseCoins(int $userId, float $orderAmount, int $orderId): bool
    {
        $coinRate = config('coin-system.purchase_coin_rate', 1); // 1 coin per rupee by default
        $coinsToAward = floor($orderAmount * $coinRate / 100); // Per 100 rupees

        return $this->addCoins(
            $userId,
            $coinsToAward,
            "Purchase reward for order #{$orderId}",
            'earning',
            'order',
            $orderId
        );
    }

    /**
     * Award referral coins
     */
    public function awardReferralCoins(int $referrerId, int $referredUserId): bool
    {
        $referrerCoins = config('coin-system.referrer_reward_coins', 500);
        $referredCoins = config('coin-system.referred_reward_coins', 250);

        $this->addCoins(
            $referrerId,
            $referrerCoins,
            "Referral bonus for inviting user #{$referredUserId}",
            'referral',
            'referral',
            $referredUserId
        );

        return $this->addCoins(
            $referredUserId,
            $referredCoins,
            "Welcome bonus from referral",
            'referral',
            'referral',
            $referrerId
        );
    }

    /**
     * Convert coins to discount amount
     */
    public function convertCoinsToDiscount(float $coins): float
    {
        $conversionRate = config('coin-system.conversion_rate', 1); // 1 coin = 0.50 rupees
        return $coins * $conversionRate;
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(int $userId, int $limit = 50)
    {
        return CoinTransaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
