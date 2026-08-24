<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class GiftCardProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'description',
        'countries',
        'currency',
        'min_amount',
        'max_amount',
        'rate_override',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'countries' => 'array',
            'min_amount' => 'decimal:2',
            'max_amount' => 'decimal:2',
            'rate_override' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GiftCardProduct $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name).'-'.Str::random(4);
            }
        });
    }

    public function giftCards(): HasMany
    {
        return $this->hasMany(GiftCard::class);
    }

    public function logoUrl(): string
    {
        if ($this->logo && str_starts_with($this->logo, 'http')) {
            return $this->logo;
        }

        return $this->logo ? asset('storage/'.$this->logo) : asset('images/giftcard-placeholder.svg');
    }

    public function isAvailableEverywhere(): bool
    {
        return empty($this->countries);
    }

    public function isAvailableIn(?string $countryCode): bool
    {
        if ($this->isAvailableEverywhere() || ! $countryCode) {
            return true;
        }

        return in_array($countryCode, $this->countries, true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
