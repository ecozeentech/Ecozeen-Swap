<?php

namespace App\Models;

use App\Casts\ResilientEncrypted;
use App\Casts\ResilientEncryptedArray;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'referral_code',
        'referred_by',
        'phone',
        'avatar',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'password',
        'kyc_status',
        'display_currency',
        'daily_trade_limit',
        'current_balance_usd_equivalent',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => ResilientEncrypted::class,
            'two_factor_recovery_codes' => ResilientEncryptedArray::class,
            'two_factor_confirmed_at' => 'datetime',
            'daily_trade_limit' => 'decimal:2',
            'current_balance_usd_equivalent' => 'decimal:2',
            'last_login_at' => 'datetime',
            'is_suspended' => 'boolean',
            'suspended_at' => 'datetime',
        ];
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function kycDocuments(): HasMany
    {
        return $this->hasMany(KycDocument::class);
    }

    public function giftCards(): HasMany
    {
        return $this->hasMany(GiftCard::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function trustedIps(): HasMany
    {
        return $this->hasMany(UserTrustedIp::class);
    }

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class);
    }

    /**
     * The user who referred this account, if any.
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by');
    }

    /**
     * Accounts this user has referred.
     */
    public function referredUsers(): HasMany
    {
        return $this->hasMany(self::class, 'referred_by');
    }

    /**
     * Commissions this user has earned as a referrer.
     */
    public function referralCommissions(): HasMany
    {
        return $this->hasMany(ReferralCommission::class, 'referrer_id');
    }

    public function referralWithdrawals(): HasMany
    {
        return $this->hasMany(ReferralWithdrawal::class);
    }

    public function referralLink(): string
    {
        return route('register', ['ref' => $this->referral_code]);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    public function isKycVerified(): bool
    {
        return $this->kyc_status === 'verified';
    }

    /**
     * The fiat currency code the dashboard should display totals in:
     * the user's own preference if they've set one, otherwise the
     * platform-wide admin default, otherwise USD.
     */
    public function displayCurrencyCode(): string
    {
        return $this->display_currency
            ?: SystemSetting::get('default_display_currency', 'USD');
    }

    /**
     * Human-friendly KYC label. The stored value stays 'unverified' for
     * backwards compatibility, but KYC is entirely optional — it only
     * affects trading limits and is never required to use the platform.
     */
    public function kycLabel(): string
    {
        return self::kycStatusOptions()[$this->kyc_status] ?? ucfirst($this->kyc_status);
    }

    /**
     * @return array<string, string>
     */
    public static function kycStatusOptions(): array
    {
        return [
            'unverified' => 'Not Submitted',
            'pending' => 'Pending Review',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
        ];
    }

    public function walletFor(string $currencyType, string $currencyCode): ?Wallet
    {
        return $this->wallets()
            ->where('currency_type', $currencyType)
            ->where('currency_code', $currencyCode)
            ->first();
    }

    public function avatarUrl(): ?string
    {
        if ($this->avatar && str_starts_with($this->avatar, 'http')) {
            return $this->avatar;
        }

        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    public function countryName(): ?string
    {
        return $this->country ? (config('countries')[$this->country] ?? $this->country) : null;
    }

    public function fullAddress(): ?string
    {
        $parts = array_filter([$this->address, $this->city, $this->state, $this->countryName(), $this->postal_code]);

        return $parts ? implode(', ', $parts) : null;
    }

    protected static function booted(): void
    {
        // Relying solely on the DB column default leaves this attribute
        // unset in-memory immediately after create(), which would make
        // fresh unverified users appear to have a $0 trading limit until
        // the model is reloaded. Setting it explicitly here avoids that.
        static::creating(function (User $user) {
            if ($user->daily_trade_limit === null) {
                $user->daily_trade_limit = '100000.00';
            }

            if (empty($user->referral_code)) {
                $user->referral_code = self::generateUniqueReferralCode();
            }
        });
    }

    public static function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }
}
