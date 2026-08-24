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

        return $this->logo ? asset('storage/'.$this->logo) : asset('images/crypto-placeholder.svg');
    }
}
