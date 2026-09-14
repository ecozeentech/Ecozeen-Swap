<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'transaction_id',
        'type',
        'amount',
        'rate_applied',
        'description',
        'is_reversed',
        'reversed_by',
        'reversed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'rate_applied' => 'decimal:3',
            'is_reversed' => 'boolean',
            'reversed_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function reversedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_reversed', false);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'trade_commission' => 'Trade Commission',
            'signup_bonus' => 'Signup Bonus',
            'manual_adjustment' => 'Manual Adjustment',
            default => ucfirst($this->type),
        };
    }
}
