<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    public const CACHE_KEY = 'system_settings.all';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function allSettings(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return static::query()->get()->keyBy('key');
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = self::allSettings()->get($key);

        if (! $setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    public static function set(string $key, mixed $value, string $type = 'string', string $group = 'general'): self
    {
        $stored = is_array($value) ? json_encode($value) : (is_bool($value) ? ($value ? '1' : '0') : (string) $value);

        return self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $stored, 'type' => $type, 'group' => $group]
        );
    }

    protected static function castValue(?string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'json' => json_decode((string) $value, true) ?? [],
            default => $value,
        };
    }
}
