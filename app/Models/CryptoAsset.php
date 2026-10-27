<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
        'logo',
        'network',
        'decimal_places',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'decimal_places' => 'integer',
        ];
    }

    public function dailyRates(): HasMany
    {
        return $this->hasMany(DailyRate::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function logoUrl(): string
    {
        if ($this->logo && str_starts_with($this->logo, 'http')) {
            return $this->logo;
        }

        if ($this->logo) {
            return asset('storage/'.$this->logo);
        }

        return self::bundledLogoUrl($this->symbol) ?? asset('images/crypto-placeholder.svg');
    }

    /**
     * A small set of bundled, brand-colored icons for well-known coins
     * (BTC, ETH, USDT, BNB, etc.) so a newly added asset — or one an
     * admin just hasn't gotten around to uploading a logo for yet —
     * shows something recognizable instead of the generic grey
     * placeholder. Purely a fallback: an admin-uploaded logo (set via
     * Admin > Crypto Assets) always takes priority over this.
     */
    public static function bundledLogoUrl(string $symbol): ?string
    {
        $path = 'images/crypto/'.strtolower($symbol).'.svg';

        return file_exists(public_path($path)) ? asset($path) : null;
    }
}
