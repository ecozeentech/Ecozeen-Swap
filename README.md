# Ecozeen Swap

**Ecozeen Swap** (EcozeenSwap.com) is a single-vendor cryptocurrency platform built for **Ecozeen Tech Ltd**
(Nigeria RC 1835204 · UK 16582062). Users buy, sell, and swap digital assets **directly with the platform** —
there is no peer-to-peer marketplace, no third-party sellers, and no user-to-user ad listings. Ecozeen Swap
management is always the counter-party to every trade.

## Tech Stack

- **Backend:** PHP 8.2+/Laravel 11, MySQL 8, Eloquent ORM
- **Frontend:** Blade + Tailwind CSS 3, Alpine.js 3, Livewire 3 (no jQuery)
- **Auth:** Laravel Breeze (Blade stack) customized for username-or-email login, with Google-Authenticator-based
  2FA (`pragmarx/google2fa` + `bacon/bacon-qr-code`)
- **Roles & Permissions:** `spatie/laravel-permission` (`super-admin`, `admin`, `user`)
- **Activity Logging:** `spatie/laravel-activitylog` plus a dedicated `activity_logs` table used for
  login history / security auditing surfaced in the UI
- **API Tokens:** Laravel Sanctum (cookie-based, for the app's own AJAX/Livewire traffic)
- **PWA:** Hand-rolled manifest + service worker (offline app-shell caching, install prompt) — no third-party
  package was required (`silviolleite/laravel-pwa` is not compatible with current Composer resolution)
- **Payments:** Paystack & Flutterwave (webhooks + redirect checkout) and manual bank transfer with proof upload
- **Queues:** Database queue driver (Redis is installed and supported, just change `QUEUE_CONNECTION`)

## Business Rules Implemented

1. **Single vendor model** — all buy/sell pricing comes from admin-set `daily_rates`; there is no way for one
   user to trade with another.
2. **24-hour rate timer** — `App\Services\RateService::setRate()` deactivates the previous rate and creates a
   new one with a configurable expiry (defaults to 24h). The dashboard's `<livewire:rate-timer-widget />`
   shows a live per-second countdown for every active pair.
3. **KYC flexibility** — `kyc_status` never blocks trading; unverified/pending users simply have a lower
   `daily_trade_limit` (enforced in `TradeService::assertWithinDailyLimit()`). A persistent banner reads
   *"KYC pending – trading limits apply"*.
4. **Bank transfer memo notice** — shown on the Buy page and the bank-transfer instructions page, and
   configurable from `/admin/settings`.
5. **Feature toggles** — every major feature (`buy`, `sell`, `swap`, `giftcards`, `invoicing`, `withdrawals`,
   `deposits`, new `registration`) can be disabled from `/admin/features` (Livewire grid). Disabled routes are
   intercepted by the `feature` middleware and render a customizable "Coming Soon" page.

## Local Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Install JS dependencies
npm install

# 3. Copy the environment file and generate an app key
cp .env.example .env
php artisan key:generate

# 4. Configure your database in .env (MySQL 8 recommended), then:
php artisan migrate --seed

# 5. Build frontend assets
npm run build          # production build
# or
npm run dev            # Vite dev server with HMR

# 6. Generate PWA icons (already committed under public/images/icons,
#    regenerate only if you change the logo/branding)
php -r '
function makeIcon($size, $path) {
    $im = imagecreatetruecolor($size, $size);
    $bg = imagecolorallocate($im, 0x2D, 0x6A, 0x4F);
    imagefill($im, 0, 0, $bg);
    imagepng($im, $path);
}
makeIcon(192, "public/images/icons/icon-192.png");
makeIcon(512, "public/images/icons/icon-512.png");
'

# 7. Serve the app
php artisan serve

# 8. (Recommended) Run the queue worker for notifications/background jobs
php artisan queue:work
```

### Default Admin Login

The `AdminUserSeeder` creates a super-admin you can log into `/admin` with:

- **Email:** `admin@ecozeenswap.com`
- **Username:** `ecozeenadmin`
- **Password:** value of `ADMIN_DEFAULT_PASSWORD` in `.env` (defaults to `ChangeMe123!`)

**Change this password immediately in any non-local environment.**

### Running Tests

```bash
php artisan test
```

The test suite runs against an in-memory SQLite database (configured in `phpunit.xml`) and covers
authentication (username/email login, registration, profile), and the core trading engine (rate
lifecycle, buy/sell quoting, admin settlement, cross-asset swaps, daily KYC limits, feature toggles).

### Code Style

```bash
./vendor/bin/pint
```

## Deploying to Production

For a step-by-step guide to deploying this app on **Hostinger Business shared hosting**
(SSH + Git deploy, `.htaccess` routing, database-backed queue/cache/session since shared hosting
has no Redis or persistent workers, cron jobs, SSL, and post-deploy admin setup), see
[`docs/DEPLOYMENT-HOSTINGER.md`](docs/DEPLOYMENT-HOSTINGER.md).

## Architecture Notes

- **Services layer** (`app/Services`) contains all business logic — `RateService`, `WalletService`,
  `TradeService`, `PaystackService`, `FlutterwaveService`, `CryptoAddressService`, `UserOnboardingService`.
  Controllers stay thin and only orchestrate HTTP concerns.
- **All financial mutations** (`WalletService::credit/debit`, `TradeService::completeBuy/confirmSell/executeSwap`)
  run inside `DB::transaction()` with `lockForUpdate()` row locks to prevent race conditions on concurrent
  requests.
- **Feature flags** live in the `system_settings` table (`App\Models\SystemSetting`, cached indefinitely and
  invalidated on save) and are exposed via `App\Support\Features`.
- **Suspicious activity detection**: `App\Http\Middleware\EnsureIpIsTrusted` blocks trading routes (buy/sell/swap
  /withdraw) the first time it sees a new IP for a user, emails a confirmation link/code
  (`App\Notifications\NewIpDetected`), and only unblocks once verified via `/security/verify-ip/{trustedIp}`.
- **2FA**: TOTP secrets/recovery codes are stored `encrypted` on the `users` table. Login flow: credentials are
  checked, and if 2FA is enabled the user is parked in the session and redirected to `/two-factor-challenge`
  before `Auth::login()` is ever called.
- **Crypto custody**: this project does not integrate a real blockchain node. `CryptoAddressService` generates
  and persists a stable, per-user deposit address per asset — swap this out for your custody provider's
  address-generation API (BitGo, Fireblocks, a self-hosted HD wallet service, etc.) before going to production.
- **Encryption**: KYC document paths, gift card numbers/PINs, and payment gateway credentials are stored using
  Laravel's `encrypted` / `encrypted:array` Eloquent casts.

## Directory Highlights

```
app/Http/Controllers/            User-facing controllers (Buy, Sell, Swap, Wallet, GiftCard, Invoice, ...)
app/Http/Controllers/Admin/      Admin panel controllers (/admin/*)
app/Http/Controllers/Security/   2FA, KYC upload, activity log, IP verification
app/Http/Middleware/             feature toggle, admin guard, suspicious-IP guard
app/Livewire/                    Rate timer, dashboard stats, buy/sell/swap calculators, admin toggle grid & rate form
app/Models/                      Eloquent models for every table in the schema
app/Services/                    Business logic (rates, wallets, trades, payments, addresses, onboarding)
resources/views/layouts/         app.blade.php (sidebar + bottom nav), admin.blade.php, guest.blade.php
database/seeders/                Roles, admin user, fiat/crypto assets, settings, gateways, sample rates
public/manifest.json             PWA manifest
public/service-worker.js         PWA offline app-shell cache
```
