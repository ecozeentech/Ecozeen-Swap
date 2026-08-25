<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Behaves like Laravel's built-in 'encrypted' cast, except a value that
 * fails to decrypt (e.g. because APP_KEY was rotated after the value was
 * written, or a database dump was restored into an environment with a
 * different key) is treated as "unset" instead of throwing and turning
 * every page that touches the model into a 500 error.
 */
class ResilientEncrypted implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            Log::warning("Could not decrypt attribute [{$key}] on ".get_class($model)."#{$model->getKey()}: {$e->getMessage()}");

            return null;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        return Crypt::encryptString((string) $value);
    }
}
