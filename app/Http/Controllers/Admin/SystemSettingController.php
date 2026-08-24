<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', ['settings' => SystemSetting::allSettings()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'coming_soon_message' => ['nullable', 'string', 'max:500'],
            'support_widget_type' => ['required', 'in:tawkto,custom_url,none'],
            'tawkto_embed_url' => ['nullable', 'url'],
            'custom_chat_url' => ['nullable', 'url'],
            'giftcard_buyback_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bank_transfer_memo_notice' => ['nullable', 'string', 'max:500'],
        ]);

        SystemSetting::set('coming_soon_message', $request->input('coming_soon_message', 'This feature is coming soon. Please check back shortly.'), 'string', 'features');
        SystemSetting::set('support_widget_type', $request->input('support_widget_type'), 'string', 'support');
        SystemSetting::set('tawkto_embed_url', $request->input('tawkto_embed_url', ''), 'string', 'support');
        SystemSetting::set('custom_chat_url', $request->input('custom_chat_url', ''), 'string', 'support');
        SystemSetting::set('giftcard_buyback_rate', $request->input('giftcard_buyback_rate'), 'integer', 'giftcards');
        SystemSetting::set('bank_transfer_memo_notice', $request->input(
            'bank_transfer_memo_notice',
            'Do not reference cryptocurrency or crypto payments in your bank transfer memo/description.'
        ), 'string', 'security');

        ActivityLog::record(auth()->id(), 'admin_updated_system_settings');

        return back()->with('status', 'settings-updated');
    }
}
