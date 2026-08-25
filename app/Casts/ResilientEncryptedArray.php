<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Array counterpart to ResilientEncrypted — behaves like Laravel's
 * 'encrypted:array' cast, but a value that fails to decrypt is treated as
 * an empty array instead of throwing a DecryptException that would 500
 * every page rendering the model (e.g. admin/gateways after an APP_KEY
 * rotation or a database restore from a different environment).
 */
class ResilientEncryptedArray implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return [];
        }

        try {
            $decrypted = Crypt::decryptString($value);
        } catch (DecryptException $e) {
            Log::warning("Could not decrypt attribute [{$key}] on ".get_class($model)."#{$model->getKey()}: {$e->getMessage()}");

            return [];
        }

        return json_decode($decrypted, true) ?? [];
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        return Crypt::encryptString(json_encode($value));
    }
}
