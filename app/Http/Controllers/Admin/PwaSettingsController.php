<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lets an admin fully manage the installable PWA experience — the app
 * icon(s), splash/maskable icon, app name, and theme/background colors —
 * without touching any code. See PwaManifestController for where these
 * settings actually get turned into the manifest served to browsers.
 */
class PwaSettingsController extends Controller
{
    public const ASSET_KEYS = [
        'pwa_icon_192' => 'App Icon (192×192)',
        'pwa_icon_512' => 'App Icon / Splash Icon (512×512)',
        'pwa_maskable_icon' => 'Maskable Icon (Android adaptive, optional)',
    ];

    public function __construct(protected MediaUploadService $media) {}

    public function edit(): View
    {
        return view('admin.pwa.edit', [
            'settings' => SystemSetting::allSettings(),
            'assetKeys' => self::ASSET_KEYS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'pwa_icon_192' => MediaUploadService::logoRules(1024),
            'pwa_icon_512' => MediaUploadService::logoRules(2048),
            'pwa_maskable_icon' => MediaUploadService::logoRules(2048),
            'pwa_app_name' => ['nullable', 'string', 'max:45'],
            'pwa_short_name' => ['nullable', 'string', 'max:12'],
            'pwa_theme_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'pwa_background_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        foreach (array_keys(self::ASSET_KEYS) as $key) {
            if ($request->hasFile($key)) {
                $previous = SystemSetting::get($key);
                $path = $this->media->replace($request->file($key), 'pwa', $previous);
                SystemSetting::set($key, $path, 'string', 'pwa');
            }
        }

        // Each icon card and the details card below are independent
        // <form>s on the same page (forms must never be nested — see
        // BrandingController), so a single submission only ever contains
        // a subset of these fields. Only touch the ones actually present,
        // otherwise submitting one form would blank out the others.
        foreach (['pwa_app_name', 'pwa_short_name', 'pwa_theme_color', 'pwa_background_color'] as $field) {
            if ($request->has($field)) {
                SystemSetting::set($field, $request->input($field, ''), 'string', 'pwa');
            }
        }

        ActivityLog::record(auth()->id(), 'admin_updated_pwa_settings');

        return back()->with('status', 'pwa-updated');
    }

    public function reset(string $key): RedirectResponse
    {
        if (! array_key_exists($key, self::ASSET_KEYS)) {
            abort(404);
        }

        $this->media->forget(SystemSetting::get($key));
        SystemSetting::set($key, '', 'string', 'pwa');

        ActivityLog::record(auth()->id(), 'admin_reset_pwa_asset', ['key' => $key]);

        return back()->with('status', 'pwa-reset');
    }
}
