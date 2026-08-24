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
        'currency_type',
        'currency_code',
        'balance',
        'reserved_balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:8',
            'reserved_balance' => 'decimal:8',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function isFiat(): bool
    {
        return $this->currency_type === 'fiat';
    }

    public function isCrypto(): bool
    {
        return $this->currency_type === 'crypto';
    }

    public function availableBalance(): string
    {
        return bcsub((string) $this->balance, (string) $this->reserved_balance, 8);
    }
}
