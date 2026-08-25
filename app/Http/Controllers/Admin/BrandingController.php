<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandingController extends Controller
{
    public const ASSET_KEYS = [
        'site_logo' => 'Platform Logo',
        'site_logo_dark' => 'Platform Logo (dark mode)',
        'site_favicon' => 'Favicon',
        'site_og_image' => 'Social Share Image (Open Graph)',
    ];

    public function __construct(protected MediaUploadService $media) {}

    public function edit(): View
    {
        return view('admin.branding.edit', [
            'settings' => SystemSetting::allSettings(),
            'assetKeys' => self::ASSET_KEYS,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'site_logo' => MediaUploadService::logoRules(2048),
            'site_logo_dark' => MediaUploadService::logoRules(2048),
            'site_favicon' => MediaUploadService::logoRules(1024),
            // The Open Graph share image is rendered by social platforms'
            // own crawlers (never our own <img> tags), so SVG isn't safe
            // to allow here — keep it to the strict raster 'image' rule.
            'site_og_image' => ['nullable', 'image', 'max:4096'],
        ]);

        foreach (array_keys(self::ASSET_KEYS) as $key) {
            if ($request->hasFile($key)) {
                $previous = SystemSetting::get($key);
                $path = $this->media->replace($request->file($key), 'branding', $previous);
                SystemSetting::set($key, $path, 'string', 'branding');
            }
        }

        ActivityLog::record(auth()->id(), 'admin_updated_branding');

        return back()->with('status', 'branding-updated');
    }

    public function reset(string $key): RedirectResponse
    {
        if (! array_key_exists($key, self::ASSET_KEYS)) {
            abort(404);
        }

        $this->media->forget(SystemSetting::get($key));
        SystemSetting::set($key, '', 'string', 'branding');

        ActivityLog::record(auth()->id(), 'admin_reset_branding_asset', ['key' => $key]);

        return back()->with('status', 'branding-reset');
    }
}
