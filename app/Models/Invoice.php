<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'crypto_asset_id',
        'amount_crypto',
        'fiat_amount',
        'fiat_currency',
        'recipient_email',
        'recipient_name',
        'description',
        'status',
        'due_date',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_crypto' => 'decimal:8',
            'fiat_amount' => 'decimal:2',
            'due_date' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cryptoAsset(): BelongsTo
    {
        return $this->belongsTo(CryptoAsset::class);
    }

    public static function generateInvoiceNumber(): string
    {
        return 'INV-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status === 'sent';
    }
}
