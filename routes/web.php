<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BlogCategoryController as AdminBlogCategoryController;
use App\Http\Controllers\Admin\BlogPostController as AdminBlogPostController;
use App\Http\Controllers\Admin\BrandingController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\CryptoAssetController;
use App\Http\Controllers\Admin\CryptoWalletController as AdminCryptoWalletController;
use App\Http\Controllers\Admin\DailyRateController;
use App\Http\Controllers\Admin\FeatureToggleController;
use App\Http\Controllers\Admin\FiatCurrencyController;
use App\Http\Controllers\Admin\GiftCardProductController;
use App\Http\Controllers\Admin\GiftCardVerificationController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\PwaSettingsController;
use App\Http\Controllers\Admin\ReferralController as AdminReferralController;
use App\Http\Controllers\Admin\ReferralWithdrawalController as AdminReferralWithdrawalController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BuyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PwaManifestController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\Security\ActivityLogController;
use App\Http\Controllers\Security\IpVerificationController;
use App\Http\Controllers\Security\KycController;
use App\Http\Controllers\Security\TwoFactorController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SwapController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WebhookController;
use App\Services\RateService;
use Illuminate\Support\Facades\Route;

Route::get('/', function (RateService $rates) {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome', ['activeRates' => $rates->allActiveRates()->take(6)]);
})->name('home');

/*
|--------------------------------------------------------------------------
| Public Content: About, Contact, Policies, Blog, Sitemap
|--------------------------------------------------------------------------
*/
Route::get('/about', [PageController::class, 'about'])->name('about.show');
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:6,1')->name('contact.store');
Route::get('/privacy-policy', fn () => app(PageController::class)->show('privacy-policy'))->name('policy.privacy');
Route::get('/terms-and-conditions', fn () => app(PageController::class)->show('terms-and-conditions'))->name('policy.terms');

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/category/{slug}', [BlogController::class, 'category'])->name('category');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Generated live from admin-managed PWA settings — see PwaManifestController.
// There is deliberately no static public/manifest.json; if one exists the
// webserver would serve it directly and this route would never run.
Route::get('/manifest.json', [PwaManifestController::class, 'show'])->name('pwa.manifest');

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

    // Profile > Bank Accounts (settlement accounts for sells/withdrawals)
    Route::prefix('profile/bank-accounts')->name('bank-accounts.')->group(function () {
        Route::get('/', [BankAccountController::class, 'index'])->name('index');
        Route::post('/', [BankAccountController::class, 'store'])->name('store');
        Route::put('/{bankAccount}', [BankAccountController::class, 'update'])->name('update');
        Route::post('/{bankAccount}/default', [BankAccountController::class, 'setDefault'])->name('set-default');
        Route::delete('/{bankAccount}', [BankAccountController::class, 'destroy'])->name('destroy');
    });

    // Referral Program
    Route::prefix('referrals')->name('referrals.')->middleware('feature:referrals_enabled')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
        Route::post('/withdraw', [ReferralController::class, 'store'])->middleware('verified')->name('withdraw');
        Route::delete('/withdraw/{withdrawal}', [ReferralController::class, 'cancel'])->name('withdraw.cancel');
    });

    // Wallet
    Route::prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/', [WalletController::class, 'index'])->name('index');
        Route::get('/fund', [WalletController::class, 'fundForm'])->middleware('feature:deposits_enabled')->name('fund');
        Route::post('/fund', [WalletController::class, 'fund'])->middleware(['verified', 'feature:deposits_enabled'])->name('fund.store');
        Route::get('/fund/{transaction}/bank', [WalletController::class, 'fundBankForm'])->name('fund.bank');
        Route::post('/fund/{transaction}/proof', [WalletController::class, 'uploadProof'])->name('fund.proof');
        Route::get('/fund/callback/{gateway}', [WalletController::class, 'fundCallback'])->name('fund.callback');
        Route::get('/deposit/{cryptoAsset}', [WalletController::class, 'depositAddress'])->middleware('feature:deposits_enabled')->name('deposit');
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
    Route::put('/users/{user}/profile', [UserManagementController::class, 'updateProfile'])->name('users.update-profile');
    Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
    Route::post('/users/{user}/suspend', [UserManagementController::class, 'suspend'])->name('users.suspend');
    Route::post('/users/{user}/unsuspend', [UserManagementController::class, 'unsuspend'])->name('users.unsuspend');
    Route::post('/kyc-documents/{kycDocument}/approve', [UserManagementController::class, 'approveKyc'])->name('kyc.approve');
    Route::post('/kyc-documents/{kycDocument}/reject', [UserManagementController::class, 'rejectKyc'])->name('kyc.reject');

    Route::get('/crypto', [CryptoAssetController::class, 'index'])->name('crypto.index');
    Route::post('/crypto', [CryptoAssetController::class, 'store'])->name('crypto.store');
    Route::put('/crypto/{cryptoAsset}', [CryptoAssetController::class, 'update'])->name('crypto.update');
    Route::post('/crypto/{cryptoAsset}/toggle', [CryptoAssetController::class, 'toggleActive'])->name('crypto.toggle');
    Route::delete('/crypto/{cryptoAsset}', [CryptoAssetController::class, 'destroy'])->name('crypto.destroy');

    Route::get('/fiat', [FiatCurrencyController::class, 'index'])->name('fiat.index');
    Route::post('/fiat', [FiatCurrencyController::class, 'store'])->name('fiat.store');
    Route::put('/fiat/{fiatCurrency}', [FiatCurrencyController::class, 'update'])->name('fiat.update');
    Route::post('/fiat/{fiatCurrency}/toggle', [FiatCurrencyController::class, 'toggleActive'])->name('fiat.toggle');
    Route::delete('/fiat/{fiatCurrency}', [FiatCurrencyController::class, 'destroy'])->name('fiat.destroy');

    Route::get('/rates', [DailyRateController::class, 'index'])->name('rates.index');
    Route::post('/rates', [DailyRateController::class, 'store'])->name('rates.store');
    Route::put('/rates/{dailyRate}', [DailyRateController::class, 'update'])->name('rates.update');
    Route::post('/rates/{dailyRate}/toggle', [DailyRateController::class, 'toggleActive'])->name('rates.toggle');
    Route::post('/rates/{dailyRate}/renew', [DailyRateController::class, 'renew'])->name('rates.renew');
    Route::delete('/rates/{dailyRate}', [DailyRateController::class, 'destroy'])->name('rates.destroy');

    Route::get('/features', [FeatureToggleController::class, 'index'])->name('features.index');

    Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{transaction}', [AdminTransactionController::class, 'show'])->name('transactions.show');
    Route::post('/transactions/{transaction}/mark-paid', [AdminTransactionController::class, 'markPaid'])->name('transactions.mark-paid');
    Route::post('/transactions/{transaction}/confirm-sell', [AdminTransactionController::class, 'confirmSell'])->name('transactions.confirm-sell');
    Route::post('/transactions/{transaction}/reject', [AdminTransactionController::class, 'reject'])->name('transactions.reject');

    Route::get('/giftcard-products', [GiftCardProductController::class, 'index'])->name('giftcard-products.index');
    Route::post('/giftcard-products', [GiftCardProductController::class, 'store'])->name('giftcard-products.store');
    Route::put('/giftcard-products/{giftCardProduct}', [GiftCardProductController::class, 'update'])->name('giftcard-products.update');
    Route::post('/giftcard-products/{giftCardProduct}/toggle', [GiftCardProductController::class, 'toggleActive'])->name('giftcard-products.toggle');
    Route::delete('/giftcard-products/{giftCardProduct}', [GiftCardProductController::class, 'destroy'])->name('giftcard-products.destroy');

    Route::get('/giftcards', [GiftCardVerificationController::class, 'index'])->name('giftcards.index');
    Route::post('/giftcards/{giftCard}/approve', [GiftCardVerificationController::class, 'approve'])->name('giftcards.approve');
    Route::post('/giftcards/{giftCard}/reject', [GiftCardVerificationController::class, 'reject'])->name('giftcards.reject');

    Route::get('/logs', [SystemLogController::class, 'index'])->name('logs.index');

    Route::get('/gateways', [PaymentGatewayController::class, 'index'])->name('gateways.index');
    Route::put('/gateways/{paymentGateway}', [PaymentGatewayController::class, 'update'])->name('gateways.update');

    Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SystemSettingController::class, 'update'])->name('settings.update');

    Route::get('/referrals', [AdminReferralController::class, 'index'])->name('referrals.index');
    Route::put('/referrals/settings', [AdminReferralController::class, 'updateSettings'])->name('referrals.settings');
    Route::post('/referrals/commissions/{commission}/reverse', [AdminReferralController::class, 'reverseCommission'])->name('referrals.reverse-commission');

    Route::get('/referrals/withdrawals', [AdminReferralWithdrawalController::class, 'index'])->name('referrals.withdrawals');
    Route::post('/referrals/withdrawals/{withdrawal}/approve', [AdminReferralWithdrawalController::class, 'approve'])->name('referrals.withdrawals.approve');
    Route::post('/referrals/withdrawals/{withdrawal}/paid', [AdminReferralWithdrawalController::class, 'markPaid'])->name('referrals.withdrawals.paid');
    Route::post('/referrals/withdrawals/{withdrawal}/reject', [AdminReferralWithdrawalController::class, 'reject'])->name('referrals.withdrawals.reject');

    Route::get('/branding', [BrandingController::class, 'edit'])->name('branding.edit');
    Route::put('/branding', [BrandingController::class, 'update'])->name('branding.update');
    Route::delete('/branding/{key}', [BrandingController::class, 'reset'])->name('branding.reset');

    Route::get('/pwa', [PwaSettingsController::class, 'edit'])->name('pwa.edit');
    Route::put('/pwa', [PwaSettingsController::class, 'update'])->name('pwa.update');
    Route::delete('/pwa/{key}', [PwaSettingsController::class, 'reset'])->name('pwa.reset');

    Route::get('/pages', [AdminPageController::class, 'index'])->name('pages.index');
    Route::get('/pages/create', [AdminPageController::class, 'create'])->name('pages.create');
    Route::post('/pages', [AdminPageController::class, 'store'])->name('pages.store');
    Route::get('/pages/{page}/edit', [AdminPageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [AdminPageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [AdminPageController::class, 'destroy'])->name('pages.destroy');

    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/posts', [AdminBlogPostController::class, 'index'])->name('posts.index');
        Route::get('/posts/create', [AdminBlogPostController::class, 'create'])->name('posts.create');
        Route::post('/posts', [AdminBlogPostController::class, 'store'])->name('posts.store');
        Route::get('/posts/{post}/edit', [AdminBlogPostController::class, 'edit'])->name('posts.edit');
        Route::put('/posts/{post}', [AdminBlogPostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [AdminBlogPostController::class, 'destroy'])->name('posts.destroy');

        Route::get('/categories', [AdminBlogCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminBlogCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{blogCategory}', [AdminBlogCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{blogCategory}', [AdminBlogCategoryController::class, 'destroy'])->name('categories.destroy');
    });

    Route::get('/crypto-wallets', [AdminCryptoWalletController::class, 'index'])->name('crypto-wallets.index');
    Route::post('/crypto-wallets', [AdminCryptoWalletController::class, 'store'])->name('crypto-wallets.store');
    Route::put('/crypto-wallets/{cryptoWallet}', [AdminCryptoWalletController::class, 'update'])->name('crypto-wallets.update');
    Route::post('/crypto-wallets/{cryptoWallet}/toggle', [AdminCryptoWalletController::class, 'toggleActive'])->name('crypto-wallets.toggle');
    Route::post('/crypto-wallets/{cryptoWallet}/default', [AdminCryptoWalletController::class, 'setDefault'])->name('crypto-wallets.set-default');
    Route::delete('/crypto-wallets/{cryptoWallet}', [AdminCryptoWalletController::class, 'destroy'])->name('crypto-wallets.destroy');

    Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])->name('contact-messages.index');
    Route::get('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'show'])->name('contact-messages.show');
    Route::post('/contact-messages/{contactMessage}/replied', [AdminContactMessageController::class, 'markReplied'])->name('contact-messages.mark-replied');
    Route::delete('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'destroy'])->name('contact-messages.destroy');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Catch-all for admin-created static pages (e.g. /faq)
|--------------------------------------------------------------------------
| Registered last so it never shadows a more specific route above.
*/
Route::get('/{slug}', [PageController::class, 'show'])->name('pages.show');
