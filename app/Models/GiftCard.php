<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GiftCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_type',
        'card_number_hashed',
        'pin',
        'card_image',
        'face_value',
        'face_value_currency',
        'rate_applied',
        'selling_price',
        'payout_currency',
        'status',
        'admin_note',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'card_number_hashed' => 'encrypted',
            'pin' => 'encrypted',
            'face_value' => 'decimal:2',
            'rate_applied' => 'decimal:4',
            'selling_price' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function maskedCardNumber(): string
    {
        $value = (string) $this->card_number_hashed;

        return str_repeat('*', max(0, strlen($value) - 4)).substr($value, -4);
    }
}
