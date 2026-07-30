<?php

namespace App\Http\Controllers\Api;

use App\Models\Coin;
use App\Models\CoinTransaction;
use App\Models\CoinTransfer;
use App\Services\CoinService;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    protected $coinService;

    public function __construct(CoinService $coinService)
    {
        $this->coinService = $coinService;
    }

    /**
     * Get coin balance
     */
    public function balance()
    {
        $coins = Coin::where('user_id', auth()->id())->firstOrFail();

        return $this->success($coins, 'Coin balance retrieved');
    }

    /**
     * Get coin transactions
     */
    public function transactions(Request $request)
    {
        $transactions = CoinTransaction::where('user_id', auth()->id())
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->input('type'));
            })
            ->when($request->filled('date_from'), function ($q) use ($request) {
                $q->where('created_at', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($q) use ($request) {
                $q->where('created_at', '<=', $request->input('date_to'));
            })
            ->latest()
            ->paginate($request->input('per_page', 15));

        return $this->success($transactions, 'Transactions retrieved');
    }

    /**
     * Redeem coins
     */
    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'coins_to_redeem' => 'required|numeric|min:1',
        ]);

        try {
            $coin = Coin::where('user_id', auth()->id())->firstOrFail();

            if ($coin->available_balance < $validated['coins_to_redeem']) {
                return $this->error('Insufficient coin balance', 422);
            }

            $this->coinService->redeemCoins(
                auth()->id(),
                $validated['coins_to_redeem'],
                'Coins redeemed for discount'
            );

            $discount = $this->coinService->convertCoinsToDiscount($validated['coins_to_redeem']);
            $coin->refresh();

            return $this->success([
                'coins_redeemed' => $validated['coins_to_redeem'],
                'discount_amount' => $discount,
                'new_balance' => $coin->available_balance,
            ], 'Coins redeemed successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Transfer coins
     */
    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'recipient_email' => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:1',
            'message' => 'nullable|string|max:500',
        ]);

        try {
            $recipient = \App\Models\User::where('email', $validated['recipient_email'])->firstOrFail();

            if ($recipient->id === auth()->id()) {
                return $this->error('Cannot transfer coins to yourself', 422);
            }

            $transfer = $this->coinService->transferCoins(
                auth()->id(),
                $recipient->id,
                $validated['amount'],
                $validated['message'] ?? null
            );

            return $this->success($transfer, 'Coins transferred successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Get coin transfers
     */
    public function transfers(Request $request)
    {
        $transfers = CoinTransfer::where(function ($q) {
            $q->where('sender_id', auth()->id())
              ->orWhere('receiver_id', auth()->id());
        })
        ->with(['sender', 'receiver'])
        ->latest()
        ->paginate($request->input('per_page', 15));

        return $this->success($transfers, 'Transfers retrieved');
    }
}
