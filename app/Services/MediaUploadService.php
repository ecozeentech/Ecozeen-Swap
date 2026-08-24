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
