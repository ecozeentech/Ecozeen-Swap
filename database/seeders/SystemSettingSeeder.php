<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
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
    }
}
