<?php

use App\Http\Controllers\Api\RateApiController;
use App\Http\Controllers\Api\WalletApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Lightweight JSON endpoints consumed by the app's own Alpine.js/Livewire
| frontend (rate polling, PWA offline fallbacks). Authenticated with
| Sanctum's stateful cookie guard so no separate token is required for
| same-origin requests from the Blade frontend.
|
*/

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('/rates', [RateApiController::class, 'index']);
    Route::get('/wallets', [WalletApiController::class, 'index']);
});
