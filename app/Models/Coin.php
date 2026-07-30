<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_balance',
        'available_balance',
        'locked_balance',
        'pending_balance',
        'last_updated_at',
    ];

    protected $casts = [
        'total_balance' => 'float',
        'available_balance' => 'float',
        'locked_balance' => 'float',
        'pending_balance' => 'float',
        'last_updated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->user->coinTransactions();
    }

    // Methods
    public function addCoins(float $amount, string $description, string $type = 'earning', ?string $reference_type = null, ?int $reference_id = null)
    {
        $this->total_balance += $amount;
        $this->available_balance += $amount;
        $this->save();

        CoinTransaction::create([
            'user_id' => $this->user_id,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'reference_type' => $reference_type,
            'reference_id' => $reference_id,
            'status' => 'completed',
        ]);
    }

    public function redeemCoins(float $amount, string $description, ?int $order_id = null)
    {
        if ($this->available_balance < $amount) {
            throw new \Exception('Insufficient coin balance');
        }

        $this->available_balance -= $amount;
        $this->total_balance -= $amount;
        $this->save();

        CoinTransaction::create([
            'user_id' => $this->user_id,
            'type' => 'redemption',
            'amount' => -$amount,
            'description' => $description,
            'reference_type' => 'order',
            'reference_id' => $order_id,
            'status' => 'completed',
        ]);
    }
}
