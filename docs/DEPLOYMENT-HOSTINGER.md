# Deploying Ecozeen Swap to Hostinger Business Shared Hosting

This guide walks through deploying this Laravel 11 application to a **Hostinger Business**
shared hosting plan. Shared hosting can't run a persistent queue worker, Redis, or WebSockets —
this app is built to work fine without them (database session/cache/queue drivers, no
long-running processes required), which is why it's a good fit here.

If you outgrow shared hosting later (need real-time queue workers, high traffic, WebSockets),
move to a Hostinger VPS or any regular VPS/cloud host — the app doesn't need any code changes,
just different infrastructure.

---

## 0. Checklist before you start

- A Hostinger **Business** (or higher) plan — it includes SSH access, Composer, Git, and a
  selectable PHP version, which are all required.
- A domain pointed at your Hostinger hosting (either bought through Hostinger or with its
  nameservers/DNS pointed at Hostinger).
- This repository pushed to GitHub (already done — `main` branch).
- Local machine with Node.js/npm installed (to build frontend assets before upload).

---

## 1. Configure hPanel

1. **PHP version** — hPanel → **Advanced → PHP Configuration** → set PHP to **8.3** or **8.4**
   (Laravel 11 requires 8.2+; 8.3/8.4 is the sweet spot for package compatibility).
   In the same screen, under **PHP Extensions**, make sure these are enabled:
   `bcmath, ctype, fileinfo, mbstring, openssl, pdo_mysql, tokenizer, xml, gd, intl, zip`.
2. **SSH access** — hPanel → **Advanced → SSH Access** → enable it and note the host, username,
   and port (commonly `65002` on Hostinger).
3. **MySQL database** — hPanel → **Databases → MySQL Databases**:
   - Create a database (e.g. `u123456789_ecozeen`).
   - Create a database user with a strong, generated password.
   - Attach the user to the database with **all privileges** (easy to miss — causes
     "access denied" errors later if skipped).
4. **SSL** — hPanel → **Security → SSL** → issue the free SSL certificate for your domain (can
   be done now or after the app is live).

---

## 2. Get the code onto the server (SSH + Git)

Do **not** use hPanel's built-in "Git" auto-deploy feature for this app — it deploys straight
into `public_html`, which would expose `.env`, `app/`, `vendor/`, etc. to the public internet.
Instead, clone over SSH into `public_html` and use an `.htaccess` rewrite (step 6) to route
requests into Laravel's `public/` folder, keeping everything else safely non-web-accessible in
practice (with a hard `.env` deny rule as a second layer of protection).

```bash
# Connect (use the host/port/username from hPanel → SSH Access)
ssh u123456789@your-server-ip -p 65002

# Go to your domain's web root
cd domains/ecozeenswap.com/public_html   # path shown in hPanel; may just be ~/public_html

# Clone the repo into a temp folder, then move everything (incl. dotfiles) up
git clone https://github.com/ecozeentech/Ecozeen-Swap.git tmp
mv tmp/{.,}* . 2>/dev/null
rm -rf tmp
```

> If the repo is private, generate a deploy key on the server (`ssh-keygen -t ed25519`), add the
> public key under GitHub → repo → **Settings → Deploy keys**, and clone with the SSH URL
> (`git@github.com:ecozeentech/Ecozeen-Swap.git`) instead of HTTPS.

---

## 3. Install PHP dependencies

```bash
composer install --optimize-autoloader --no-dev
```

If `composer` isn't found, try `php composer.phar install ...` or install it locally to the
project first (`curl -sS https://getcomposer.org/installer | php`).

---

## 4. Build & upload frontend assets

`public/build/` (compiled Tailwind/Alpine assets) is intentionally **not** committed to git.
Build it locally and upload it — this is the most reliable option on shared hosting:

```bash
# On your local machine, inside the project
npm install
npm run build
```

Then upload the generated `public/build/` folder to the same path on the server (via SFTP/File
Manager, or `scp -P 65002 -r public/build u123456789@your-server-ip:domains/ecozeenswap.com/public_html/public/`).

> Alternative: if your plan exposes Node.js over SSH (`node -v` / `npm -v` work), you can instead
> run `npm install && npm run build` directly on the server inside `public_html`.

---

## 5. Configure the environment

```bash
cp .env.example .env
nano .env
```

Set at minimum:

```dotenv
APP_NAME="Ecozeen Swap"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://ecozeenswap.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_ecozeen
DB_USERNAME=u123456789_ecozeen
DB_PASSWORD=the-strong-password-you-generated

# Shared hosting has no persistent worker/Redis — keep these on database drivers
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com   # or use Mailgun/Brevo/SendGrid for better deliverability
MAIL_PORT=587
MAIL_USERNAME=no-reply@ecozeenswap.com
MAIL_PASSWORD=your-mailbox-password
MAIL_FROM_ADDRESS="no-reply@ecozeenswap.com"
MAIL_FROM_NAME="${APP_NAME}"

ADMIN_DEFAULT_PASSWORD=set-a-strong-password-here

PAYSTACK_PUBLIC_KEY=
PAYSTACK_SECRET_KEY=
FLUTTERWAVE_PUBLIC_KEY=
FLUTTERWAVE_SECRET_KEY=
FLUTTERWAVE_SECRET_HASH=
TAWKTO_EMBED_URL=
```

`APP_DEBUG=false` is mandatory in production — leaving it `true` leaks stack traces and env
values to anyone who triggers an error.

Generate the app key:

```bash
php artisan key:generate --force
```

---

## 6. Route requests into Laravel's `public/` folder

Create `.htaccess` in the project root (same level as `artisan`, **not** inside `public/`):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Send every request into Laravel's public/ folder
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L,QSA]
</IfModule>

# Never serve the environment file, even if something else misconfigures
<Files .env>
    Require all denied
</Files>
```

If your hosting plan lets you set a custom **document root** per domain (hPanel → **Websites**
→ your domain → look for a "Document Root" option), point it at `public_html/public` instead —
that's cleaner than the rewrite above and skips this step entirely.

---

## 7. Database, permissions, and Laravel caches

```bash
# Run migrations and seed roles/admin user/starter assets/rates/settings
php artisan migrate --force
php artisan db:seed --force

# Laravel needs to write logs/cache/sessions here
chmod -R 775 storage bootstrap/cache

# Only needed because KYC docs, gift-card images, and deposit proofs use storage/app
php artisan storage:link

# Compile config/routes/views for faster responses
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Re-run the three `cache` commands after every future deploy that changes `.env`, routes, or
> config files — otherwise Laravel keeps serving the old cached versions.

---

## 8. Cron jobs

Shared hosting can't run a long-lived `php artisan queue:work` process. This app already
defaults to `QUEUE_CONNECTION=database`, so add a cron job in hPanel → **Advanced → Cron Jobs**
to drain the queue every minute (used for KYC/transaction status emails and the new-IP
notification):

```
* * * * * cd /home/u123456789/domains/ecozeenswap.com/public_html && php artisan queue:work --stop-when-empty >> /dev/null 2>&1
```

(Adjust the `cd` path to your actual project path from step 2.) If you'd rather not manage a
cron job at all, set `QUEUE_CONNECTION=sync` in `.env` instead — notifications will just send
inline during the request instead of being queued. Fine for this app's traffic level.

Laravel's scheduler isn't required by anything in this app today (rate expiry is handled by a
query scope, not a scheduled job), but it's good practice to add it anyway for future use:

```
* * * * * cd /home/u123456789/domains/ecozeenswap.com/public_html && php artisan schedule:run >> /dev/null 2>&1
```

---

## 9. Go live

1. Visit `https://ecozeenswap.com/.env` — it must return a 403, not the file contents. If it
   doesn't, double-check the `.htaccess` block from step 6.
2. Visit `https://ecozeenswap.com` — you should see the landing page.
3. Log in to `/admin` with:
   - **Email:** `admin@ecozeenswap.com`
   - **Password:** whatever you set as `ADMIN_DEFAULT_PASSWORD` before seeding
   - **Immediately** change this password from `/profile`.
4. From the admin panel, configure:
   - `/admin/gateways` — Paystack/Flutterwave keys and bank transfer account details
   - `/admin/rates` — real daily buy/sell rates for each crypto/fiat pair
   - `/admin/crypto-wallets` — **required before Deposit/Sell will work** — add the platform's
     real receiving address for every active crypto asset (e.g. your BTC/ETH/USDT hot wallet).
     Every user sees the same address(es) here; there's no per-user generated address.
   - `/admin/branding` — replace the placeholder logo/favicon with your final artwork if desired
   - `/admin/settings` — support widget (Tawk.to URL), coming-soon message, gift card buyback %
   - `/admin/features` — disable any feature you're not ready to launch yet
5. Tell users to add at least one bank account from **Profile → Bank Accounts** before they try
   to sell crypto or withdraw fiat — both flows require selecting a saved bank account as the
   settlement destination.

---

## 10. Redeploying updates later

This is always **incremental** — `git pull` only brings in the new commits, it never wipes or
recreates anything you already have. Existing files, `.env`, uploaded logos/KYC docs/gift card
images (all in `storage/app/`), and all your data stay exactly as they are.

```bash
ssh u123456789@your-server-ip -p 65002
cd domains/ecozeenswap.com/public_html
git pull origin main
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:clear && php artisan config:cache
php artisan route:clear && php artisan route:cache
php artisan view:clear && php artisan view:cache
```

`php artisan migrate --force` only ever adds new tables/columns for new features — it never drops
existing data. If a past migration run partially failed and left a table it's trying to re-create,
Laravel skips already-applied migrations automatically (tracked in the `migrations` table), so
this is safe to run after every update without touching your existing rows.

If the update touched frontend assets (JS/CSS/Blade — almost every update does), rebuild locally
and re-upload `public/build/` as in step 4:

```bash
# On your local machine, inside the project
git pull origin main
npm install
npm run build
```

Then upload the freshly generated `public/build/` folder over the server's existing one (it's
safe to overwrite — the old, stale `public/build/` is exactly what you're replacing):

```bash
scp -P 65002 -r public/build u123456789@your-server-ip:domains/ecozeenswap.com/public_html/public/
```

**Don't skip this step.** A very common shared-hosting mistake is running `git pull` and the
`artisan` commands on the server but forgetting to rebuild and re-upload `public/build/` — the
server then keeps serving the *old* compiled JS/CSS forever, so PHP-level fixes land but anything
that depends on the frontend bundle (Livewire interactivity, styling, new UI) silently keeps the
old broken behavior. If something still looks unfixed after a deploy, this is the first thing to
check.

Finally, clear your **browser cache** (or just hard-refresh with Ctrl+Shift+R / Cmd+Shift+R) when
testing — browsers aggressively cache `public/build/` assets, so a stale tab can look "not fixed"
even after a correct deploy.

---

## Troubleshooting

| Symptom | Likely cause |
|---|---|
| Blank page / 500 error | Check `storage/logs/laravel.log` first. Usually `storage`/`bootstrap/cache` permissions, or a missing `APP_KEY`. |
| Redirect loop | `.htaccess` rewrite fighting itself — try `RewriteRule ^(.*)$ /public/$1 [L]` (leading slash). |
| "Vite manifest not found" | `public/build/` wasn't uploaded — repeat step 4. |
| CSS/JS won't load over HTTPS | Set `APP_URL=https://...` in `.env` and re-run `php artisan config:cache`. |
| "419 Page Expired" on forms | `APP_URL` doesn't match the domain visitors use, or session cookie/domain mismatch — fix `APP_URL` and re-cache config. |
| `composer install` complains about PHP version | Bump PHP to 8.3/8.4 in hPanel → PHP Configuration. |
| Webhooks (Paystack/Flutterwave) not firing | Confirm the endpoint is reachable over HTTPS (`https://ecozeenswap.com/webhook/paystack`) and the secret keys in `/admin/gateways` match the gateway dashboard. |

## Limitations to be aware of on shared hosting

- No persistent queue worker or WebSockets — this app doesn't need them, but if you later add
  features that do, you'll need a VPS.
- No Redis — the app already defaults to database-backed session/cache/queue, so this is a
  non-issue here.
- Outbound mail from shared IPs can land in spam. For anything beyond low volume, point
  `MAIL_MAILER` at a transactional provider (Mailgun, Brevo, SendGrid, Postmark) instead of
  Hostinger's own SMTP.
