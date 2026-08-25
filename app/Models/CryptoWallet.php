<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An admin-managed platform receiving address for a crypto asset. Every
 * user depositing or selling that asset sends to the same address(es) —
 * there is no per-user generated address, matching the single-vendor model.
 */
class CryptoWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'crypto_asset_id',
        'wallet_address',
        'label',
        'memo_tag',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function cryptoAsset(): BelongsTo
    {
        return $this->belongsTo(CryptoAsset::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
