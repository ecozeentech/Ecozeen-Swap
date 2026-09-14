<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

/**
 * Serves the Web App Manifest generated live from admin-managed PWA
 * settings (see Admin\PwaSettingsController) instead of a static file, so
 * uploading a new icon or renaming the app takes effect immediately with
 * no deploy needed.
 */
class PwaManifestController extends Controller
{
    public function show(): JsonResponse
    {
        $icons = [
            [
                'src' => SystemSetting::assetUrl('pwa_icon_192', 'images/icons/icon-192.png'),
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src' => SystemSetting::assetUrl('pwa_icon_512', 'images/icons/icon-512.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'any',
            ],
        ];

        if (SystemSetting::get('pwa_maskable_icon')) {
            $icons[] = [
                'src' => SystemSetting::assetUrl('pwa_maskable_icon', 'images/icons/icon-512.png'),
                'sizes' => '512x512',
                'type' => 'image/png',
                'purpose' => 'maskable',
            ];
        }

        $manifest = [
            'name' => SystemSetting::get('pwa_app_name') ?: config('app.name'),
            'short_name' => SystemSetting::get('pwa_short_name') ?: Str::limit(config('app.name'), 12, ''),
            'description' => 'Buy, sell, and swap crypto directly with Ecozeen Swap — your single trusted vendor.',
            'start_url' => '/dashboard',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'background_color' => SystemSetting::get('pwa_background_color') ?: '#F8FAFC',
            'theme_color' => SystemSetting::get('pwa_theme_color') ?: '#2563EB',
            'icons' => $icons,
        ];

        return response()->json($manifest)->header('Content-Type', 'application/manifest+json');
    }
}
