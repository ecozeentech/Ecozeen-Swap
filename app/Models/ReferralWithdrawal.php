<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_account_id',
        'amount',
        'status',
        'reference',
        'admin_note',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Requests that still "reserve" part of the user's balance — i.e.
     * everything except a rejected request, which releases the hold.
     */
    public function scopeReserving($query)
    {
        return $query->whereIn('status', ['pending', 'approved', 'paid']);
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'REFWD-'.strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }
}
