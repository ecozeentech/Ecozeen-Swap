<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CryptoAssetController;
use App\Http\Controllers\Admin\DailyRateController;
use App\Http\Controllers\Admin\FeatureToggleController;
use App\Http\Controllers\Admin\FiatCurrencyController;
use App\Http\Controllers\Admin\GiftCardVerificationController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\BuyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Security\ActivityLogController;
use App\Http\Controllers\Security\IpVerificationController;
use App\Http\Controllers\Security\KycController;
use App\Http\Controllers\Security\TwoFactorController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SwapController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Payment Gateway Webhooks (CSRF-exempt, signature-verified in controller)
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:webhooks')->group(function () {
    Route::post('/webhook/paystack', [WebhookController::class, 'paystack'])->name('webhook.paystack');
    Route::post('/webhook/flutterwave', [WebhookController::class, 'flutterwave'])->name('webhook.flutterwave');
});

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile & Security
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('security')->name('security.')->group(function () {
        Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor');
        Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
        Route::delete('/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
        Route::post('/two-factor/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.recovery-codes');

        Route::get('/kyc', [KycController::class, 'show'])->name('kyc');
        Route::post('/kyc', [KycController::class, 'store'])->name('kyc.store');

        Route::get('/activity', [ActivityLogController::class, 'show'])->name('activity');

        Route::match(['get', 'post'], '/verify-ip/{trustedIp}', [IpVerificationController::class, 'verify'])->name('verify-ip');
    });

    // Wallet
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/fund', [WalletController::class, 'fundForm'])->name('fund');
        Route::post('/fund', [WalletController::class, 'fund'])->middleware(['verified', 'feature:deposits_enabled'])->name('fund.store');
        Route::get('/fund/{transaction}/bank', [WalletController::class, 'fundBankForm'])->name('fund.bank');
        Route::post('/fund/{transaction}/proof', [WalletController::class, 'uploadProof'])->name('fund.proof');
        Route::get('/fund/callback/{gateway}', [WalletController::class, 'fundCallback'])->name('fund.callback');
        Route::get('/deposit/{cryptoAsset}', [WalletController::class, 'depositAddress'])->name('deposit');
        Route::get('/withdraw', [WalletController::class, 'withdrawForm'])->middleware('feature:withdrawals_enabled')->name('withdraw');
        Route::post('/withdraw', [WalletController::class, 'withdraw'])
            ->middleware(['verified', 'trusted.ip', 'feature:withdrawals_enabled', 'throttle:withdraw'])
            ->name('withdraw.store');
    });

    // Buy
    Route::get('/buy', [BuyController::class, 'index'])->middleware('feature:buy_enabled')->name('buy.index');
    Route::post('/buy', [BuyController::class, 'store'])
        ->middleware(['verified', 'trusted.ip', 'feature:buy_enabled', 'throttle:trade'])
        ->name('buy.store');

    // Sell
    Route::get('/sell', [SellController::class, 'index'])->middleware('feature:sell_enabled')->name('sell.index');
    Route::post('/sell', [SellController::class, 'store'])
        ->middleware(['verified', 'trusted.ip', 'feature:sell_enabled', 'throttle:trade'])
        ->name('sell.store');
    Route::get('/sell/{transaction}', [SellController::class, 'show'])->middleware('feature:sell_enabled')->name('sell.show');

    // Swap
    Route::get('/swap', [SwapController::class, 'index'])->middleware('feature:swap_enabled')->name('swap.index');
    Route::post('/swap/quote', [SwapController::class, 'quote'])->middleware(['verified', 'feature:swap_enabled'])->name('swap.quote');
    Route::post('/swap/execute', [SwapController::class, 'execute'])
        ->middleware(['verified', 'trusted.ip', 'feature:swap_enabled', 'throttle:trade'])
        ->name('swap.execute');

    // Gift Cards
    Route::get('/giftcards', [GiftCardController::class, 'index'])->middleware('feature:giftcard_enabled')->name('giftcards.index');
    Route::post('/giftcards', [GiftCardController::class, 'store'])->middleware(['verified', 'feature:giftcard_enabled'])->name('giftcards.store');

    // Invoicing
    Route::get('/invoices', [InvoiceController::class, 'index'])->middleware('feature:invoicing_enabled')->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->middleware('feature:invoicing_enabled')->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->middleware(['verified', 'feature:invoicing_enabled'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->middleware('feature:invoicing_enabled')->name('invoices.show');

    // Support
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/suspend', [UserManagementController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/unsuspend', [UserManagementController::class, 'unsuspend'])->name('users.unsuspend');
    Route::post('/kyc-documents/{kycDocument}/approve', [UserManagementController::class, 'approveKyc'])->name('kyc.approve');
    Route::post('/kyc-documents/{kycDocument}/reject', [UserManagementController::class, 'rejectKyc'])->name('kyc.reject');

    Route::get('/crypto', [CryptoAssetController::class, 'index'])->name('crypto.index');
    Route::post('/crypto', [CryptoAssetController::class, 'store'])->name('crypto.store');
    Route::put('/crypto/{cryptoAsset}', [CryptoAssetController::class, 'update'])->name('crypto.update');
    Route::delete('/crypto/{cryptoAsset}', [CryptoAssetController::class, 'destroy'])->name('crypto.destroy');

    Route::get('/fiat', [FiatCurrencyController::class, 'index'])->name('fiat.index');
    Route::post('/fiat', [FiatCurrencyController::class, 'store'])->name('fiat.store');
    Route::put('/fiat/{fiatCurrency}', [FiatCurrencyController::class, 'update'])->name('fiat.update');

    Route::get('/rates', [DailyRateController::class, 'index'])->name('rates.index');
    Route::post('/rates', [DailyRateController::class, 'store'])->name('rates.store');
    Route::put('/rates/{dailyRate}', [DailyRateController::class, 'update'])->name('rates.update');

    Route::get('/features', [FeatureToggleController::class, 'index'])->name('features.index');

    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{transaction}/mark-paid', [AdminTransactionController::class, 'markPaid'])->name('transactions.mark-paid');
    Route::post('/transactions/{transaction}/confirm-sell', [AdminTransactionController::class, 'confirmSell'])->name('transactions.confirm-sell');
    Route::post('/transactions/{transaction}/reject', [AdminTransactionController::class, 'reject'])->name('transactions.reject');

    Route::get('/giftcards', [GiftCardVerificationController::class, 'index'])->name('giftcards.index');
    Route::post('/giftcards/{giftCard}/approve', [GiftCardVerificationController::class, 'approve'])->name('giftcards.approve');
    Route::post('/giftcards/{giftCard}/reject', [GiftCardVerificationController::class, 'reject'])->name('giftcards.reject');

    Route::get('/logs', [SystemLogController::class, 'index'])->name('logs.index');

    Route::get('/gateways', [PaymentGatewayController::class, 'index'])->name('gateways.index');
    Route::put('/gateways/{paymentGateway}', [PaymentGatewayController::class, 'update'])->name('gateways.update');

    Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SystemSettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
