<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Small helper for the many "upload a logo/image and replace the previous
 * one" flows across the admin panel (crypto/fiat logos, gift card product
 * logos, blog featured images, user avatars, platform branding assets).
 */
class MediaUploadService
{
    /**
     * Validation rules for admin-uploaded logos/icons/branding assets
     * (crypto & fiat logos, gift card product logos, blog cover images,
     * platform branding). Deliberately broader than Laravel's built-in
     * 'image' rule — it also accepts SVG, which is the most common format
     * for crypto/fiat icon packs and flag icons, and which Laravel's
     * 'image' rule rejects outright. This is safe here because every one
     * of these assets is uploaded by a trusted admin (never a public
     * user) and is always rendered via <img src="...">, a context in
     * which browsers never execute scripts embedded in an SVG.
     *
     * User-submitted uploads (KYC documents, deposit proofs, gift card
     * screenshots, avatars) should keep using the stricter 'image' rule
     * instead of this helper.
     */
    public static function logoRules(int $maxKilobytes = 2048): array
    {
        return ['nullable', 'mimes:jpg,jpeg,png,webp,svg', 'max:'.$maxKilobytes];
    }

    public function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    public function replace(UploadedFile $file, string $directory, ?string $previousPath = null): string
    {
        $path = $this->store($file, $directory);

        $this->forget($previousPath);

        return $path;
    }

    public function forget(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http') && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
