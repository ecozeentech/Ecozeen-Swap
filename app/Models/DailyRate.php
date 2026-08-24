<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'crypto_asset_id',
        'fiat_currency_id',
        'buy_rate',
        'sell_rate',
        'starts_at',
        'expires_at',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'buy_rate' => 'decimal:8',
            'sell_rate' => 'decimal:8',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function cryptoAsset(): BelongsTo
    {
        return $this->belongsTo(CryptoAsset::class);
    }

    public function fiatCurrency(): BelongsTo
    {
        return $this->belongsTo(FiatCurrency::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('expires_at', '>', now());
    }

    public function secondsRemaining(): int
    {
        return max(0, now()->diffInSeconds($this->expires_at, false));
    }
}
