<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'credentials',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'credentials' => 'encrypted:array',
            'metadata' => 'array',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
