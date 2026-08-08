<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class ResponsiveImage
{
    public static function srcset(?string $url, array $widths = [320, 640]): string
    {
        $url = AssetUrl::relative($url);
        if (! is_string($url) || $url === '') {
            return '';
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension === '') {
            return '';
        }

        $base = substr($path, 0, -strlen($extension) - 1);
        $candidates = [];
        foreach (array_values(array_unique(array_map('intval', $widths))) as $width) {
            if ($width < 1) {
                continue;
            }
            $candidate = $base.'-'.$width.'w.webp';
            if (self::exists($candidate)) {
                $candidates[] = $candidate.' '.$width.'w';
            }
        }

        return implode(', ', $candidates);
    }

    private static function exists(string $url): bool
    {
        if (str_starts_with($url, '/storage/')) {
            return Storage::disk('public')->exists(ltrim(substr($url, strlen('/storage/')), '/'));
        }

        return is_file(public_path(ltrim($url, '/')));
    }
}
