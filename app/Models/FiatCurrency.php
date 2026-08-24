<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiatCurrency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'is_active',
        'exchange_rate_to_usd',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'exchange_rate_to_usd' => 'decimal:8',
        ];
    }

    public function dailyRates(): HasMany
    {
        return $this->hasMany(DailyRate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
