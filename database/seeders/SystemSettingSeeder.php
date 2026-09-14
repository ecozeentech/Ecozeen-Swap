<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Services\ReferralService;
use App\Support\Features;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Features::all() as $key => $label) {
            SystemSetting::set($key, true, 'boolean', 'features');
        }

        SystemSetting::set('coming_soon_message', 'This feature is coming soon. Please check back shortly.', 'string', 'features');
        SystemSetting::set('bank_transfer_memo_notice', 'Do not reference cryptocurrency or crypto payments in your bank transfer memo/description.', 'string', 'security');
        SystemSetting::set('giftcard_buyback_rate', 75, 'integer', 'giftcards');
        SystemSetting::set('support_widget_type', 'tawkto', 'string', 'support');
        SystemSetting::set('tawkto_embed_url', '', 'string', 'support');
        SystemSetting::set('custom_chat_url', '', 'string', 'support');
        SystemSetting::set('company_name', 'Ecozeen Tech Ltd', 'string', 'branding');
        SystemSetting::set('company_reg_ng', '1835204', 'string', 'branding');
        SystemSetting::set('company_reg_uk', '16582062', 'string', 'branding');
        SystemSetting::set('default_display_currency', 'USD', 'string', 'display');
        SystemSetting::set('contact_phone', '+1 (555) 010-2024', 'string', 'contact');
        SystemSetting::set('contact_email', 'support@ecozeenswap.com', 'string', 'contact');
        SystemSetting::set('contact_address', '150 Crypto Blvd, Suite 400, Financial District, New York, NY 10005', 'string', 'contact');
        SystemSetting::set('pwa_app_name', config('app.name', 'Ecozeen Swap'), 'string', 'pwa');
        SystemSetting::set('pwa_short_name', 'EcozeenSwap', 'string', 'pwa');
        SystemSetting::set('pwa_theme_color', '#2563EB', 'string', 'pwa');
        SystemSetting::set('pwa_background_color', '#F8FAFC', 'string', 'pwa');
        SystemSetting::set('referral_commission_rate', ReferralService::DEFAULT_COMMISSION_RATE, 'string', 'referral');
        SystemSetting::set('referral_min_withdrawal', ReferralService::DEFAULT_MIN_WITHDRAWAL, 'string', 'referral');
        SystemSetting::set('referral_signup_bonus_enabled', false, 'boolean', 'referral');
        SystemSetting::set('referral_signup_bonus_amount', ReferralService::DEFAULT_SIGNUP_BONUS, 'string', 'referral');
    }
}
