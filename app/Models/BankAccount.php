<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'account_name',
        'account_number',
        'is_default',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'account_number' => 'encrypted',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maskedAccountNumber(): string
    {
        $value = (string) $this->account_number;

        return str_repeat('*', max(0, strlen($value) - 4)).substr($value, -4);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
