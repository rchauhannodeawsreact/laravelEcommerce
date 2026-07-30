<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'total_added',
        'total_spent',
        'last_refund_at',
    ];

    protected $casts = [
        'balance' => 'float',
        'total_added' => 'float',
        'total_spent' => 'float',
        'last_refund_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->user->walletTransactions();
    }

    // Methods
    public function addFunds(float $amount, string $description, ?string $reference_type = null, ?int $reference_id = null)
    {
        $this->balance += $amount;
        $this->total_added += $amount;
        $this->save();

        WalletTransaction::create([
            'user_id' => $this->user_id,
            'type' => 'credit',
            'amount' => $amount,
            'description' => $description,
            'reference_type' => $reference_type,
            'reference_id' => $reference_id,
            'status' => 'completed',
        ]);
    }

    public function deductFunds(float $amount, string $description, ?string $reference_type = null, ?int $reference_id = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient wallet balance');
        }

        $this->balance -= $amount;
        $this->total_spent += $amount;
        $this->save();

        WalletTransaction::create([
            'user_id' => $this->user_id,
            'type' => 'debit',
            'amount' => $amount,
            'description' => $description,
            'reference_type' => $reference_type,
            'reference_id' => $reference_id,
            'status' => 'completed',
        ]);
    }
}
