<?php

namespace App\Support;

use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

final class HomePageCache
{
    public const HTML = 'home_response_html';

    private const HTML_LOCK = 'home_response_html:render-lock';
    private const SEO = 'home_seo_settings';
    private const POSTS = 'home_posts';
    private const PRODUCTS = 'home_products';
    private const PRODUCT_FAN = 'home_product_fan_products';
    private const TESTIMONIALS = 'home_testimonials';
    private const ASSET_VERSIONS = 'assets_version_hashes_v6';

    public static function render(Closure $renderer): string
    {
        $cache = self::store();
        $cached = $cache->get(self::HTML);

        if (is_string($cached)) {
            return $cached;
        }

        try {
            return $cache->lock(self::HTML_LOCK, 15)->block(3, fn (): string => $cache->remember(
                self::HTML,
                now()->addMinutes(5),
                $renderer,
            ));
        } catch (LockTimeoutException) {
            return $cache->get(self::HTML) ?? $renderer();
        }
    }

    public static function remember(string $key, Closure $resolver): mixed
    {
        return self::store()->remember($key, now()->addHour(), $resolver);
    }

    public static function assetVersions(Closure $resolver): array
    {
        return self::store()->remember(self::ASSET_VERSIONS, now()->addDay(), $resolver);
    }

    public static function invalidatePosts(): void
    {
        self::forget([self::HTML, self::POSTS]);
    }

    public static function invalidateProducts(): void
    {
        self::forget([self::HTML, self::PRODUCTS, self::PRODUCT_FAN]);
    }

    public static function invalidateTestimonials(): void
    {
        self::forget([self::HTML, self::TESTIMONIALS]);
    }

    public static function invalidateSettings(): void
    {
        self::forget([self::HTML, self::SEO]);
    }

    public static function invalidateResource(string $resource): void
    {
        match ($resource) {
            'posts' => self::invalidatePosts(),
            'products' => self::invalidateProducts(),
            'testimonials' => self::invalidateTestimonials(),
            'settings' => self::invalidateSettings(),
            default => null,
        };
    }

    private static function forget(array $keys): void
    {
        $cache = self::store();

        foreach ($keys as $key) {
            $cache->forget($key);
        }
    }

    private static function store()
    {
        return Cache::store(config('cache.public_store', 'file'));
    }
}
