<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'type',
        'amount',
        'fee',
        'currency_code',
        'status',
        'reference',
        'metadata',
        'admin_note',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:8',
            'fee' => 'decimal:8',
            'metadata' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function depositProof(): HasOne
    {
        return $this->hasOne(DepositProof::class);
    }

    /**
     * Best-effort USD-equivalent value of this transaction, used for
     * cross-currency admin totals. Prefers the value already stamped at
     * creation time (buy/sell/swap always store one); falls back to a
     * live conversion for transaction types/older rows that don't.
     */
    public function usdEquivalent(): float
    {
        if (isset($this->metadata['usd_equivalent'])) {
            return (float) $this->metadata['usd_equivalent'];
        }

        $fiat = FiatCurrency::query()->where('code', $this->currency_code)->first();
        if ($fiat) {
            return round((float) $this->amount * (float) $fiat->exchange_rate_to_usd, 2);
        }

        $crypto = CryptoAsset::query()->where('symbol', $this->currency_code)->first();
        $usd = $crypto ? FiatCurrency::query()->where('code', 'USD')->first() : null;

        if ($crypto && $usd) {
            $rate = DailyRate::query()
                ->where('crypto_asset_id', $crypto->id)
                ->where('fiat_currency_id', $usd->id)
                ->latest('starts_at')
                ->first();

            if ($rate) {
                return round((float) $this->amount * (float) $rate->buy_rate, 2);
            }
        }

        return 0.0;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public static function generateReference(string $prefix = 'TXN'): string
    {
        return strtoupper($prefix).'-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(6)), 0, 10));
    }
}
