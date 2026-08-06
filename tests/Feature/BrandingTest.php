<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.url' => 'http://localhost',
            'app.prod_url' => 'https://lanyardjogja.com',
            'cache.public_store' => 'array',
        ]);
    }

    public function test_public_shell_uses_lanyard_jogja_branding_and_favicons(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('Lanyard Jogja');
        $response->assertSee('https://lanyardjogja.com/', false);
        $response->assertSee('/uploads/lanyard-jogja-logo.webp', false);
        $response->assertSee('/favicon.ico', false);
        $response->assertSee('/favicon-32x32.png', false);
        $response->assertSee('/apple-touch-icon.png', false);
        $response->assertSee('href="'.route('services').'"', false);
        $response->assertSee('href="'.route('warranty').'"', false);
        $response->assertSee('href="'.route('faq').'"', false);
        $response->assertSee('Halo%20Lanyard%20Jogja', false);
        $response->assertSee('site-header__desktop-cta', false);
        $response->assertSee('Pesan via WhatsApp');
        $response->assertDontSee('Pusat Tali Lanyard');
    }

    public function test_public_palette_tokens_and_active_surfaces_use_warm_branding(): void
    {
        $tokens = file_get_contents(public_path('css/brand-tokens.css'));
        $header = file_get_contents(public_path('css/header.css'));
        $homepage = file_get_contents(public_path('css/homepage.css'));
        $buildScript = file_get_contents(base_path('scripts/build-public-assets.mjs'));

        foreach (['#f2ead8', '#fdf8f0', '#2a1a0a', '#b05d32', '#8e4726', '#d8c4a8', '#fcd53f'] as $token) {
            $this->assertStringContainsString($token, $tokens);
        }

        $this->assertStringContainsString('background: transparent;', $header);
        $this->assertStringContainsString('background: #d8c4a8;', $header);
        $this->assertStringContainsString('color: #2a1a0a;', $header);
        $this->assertStringNotContainsString('#0a3d2c', $header);
        $this->assertStringContainsString('--lb-green: var(--brand-primary);', $homepage);
        $this->assertStringContainsString('brand-tokens.css', $buildScript);

        $activeCssFiles = [
            'header.css', 'footer.css', 'homepage.css', 'area-index.css',
            'service-pages.css', 'shipping-index.css', 'faq-index.css',
            'ordering-index.css', 'terms-index.css', 'privacy-index.css',
            'product-index.css', 'product-detail.css', 'blog-archive.css',
            'blog-show.css', 'home-about.css', 'home-faq.css', 'home-gallery.css',
            'home-locations.css', 'home-pricing.css', 'home-products.css',
        ];
        $activeCss = collect($activeCssFiles)
            ->map(fn (string $file) => file_get_contents(public_path('css/'.$file)))
            ->implode("\n");

        foreach (['#117a3a', '#3f8c1b', '#2f7312', '#073b29', '#111c15', '#eff7eb', '#f7fbf5', '#e9eeeb', '#eaf5ee'] as $legacyColor) {
            $this->assertStringNotContainsString($legacyColor, strtolower($activeCss));
        }
    }

    public function test_manifest_and_generated_brand_assets_have_expected_formats(): void
    {
        $manifest = json_decode(
            file_get_contents(public_path('site.webmanifest')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertSame('Lanyard Jogja', $manifest['name']);
        $this->assertSame('/favicon-192x192.png', $manifest['icons'][0]['src']);
        $this->assertSame('/favicon-512x512.png', $manifest['icons'][1]['src']);

        $expectedImages = [
            'uploads/lanyard-jogja-logo.webp' => [500, 122, 'image/webp'],
            'uploads/lanyard-jogja-favicon.webp' => [512, 512, 'image/webp'],
            'favicon-32x32.png' => [32, 32, 'image/png'],
            'favicon-96x96.png' => [96, 96, 'image/png'],
            'apple-touch-icon.png' => [180, 180, 'image/png'],
            'favicon-192x192.png' => [192, 192, 'image/png'],
            'favicon-512x512.png' => [512, 512, 'image/png'],
        ];

        foreach ($expectedImages as $path => [$width, $height, $mime]) {
            $metadata = getimagesize(public_path($path));
            $this->assertIsArray($metadata, $path);
            $this->assertSame($width, $metadata[0], $path);
            $this->assertSame($height, $metadata[1], $path);
            $this->assertSame($mime, $metadata['mime'], $path);
        }

        $ico = file_get_contents(public_path('favicon.ico'));
        $this->assertIsString($ico);
        $this->assertSame("\x00\x00\x01\x00\x01\x00", substr($ico, 0, 6));
    }

    public function test_public_api_exposes_lanyard_jogja_identity_and_asset_paths(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('app', 'lanyardjogja-laravel');

        $this->getJson('/api/public-settings')
            ->assertOk()
            ->assertJsonPath('settings.site_title', 'Lanyard Jogja')
            ->assertJsonPath('settings.site_logo', '/uploads/lanyard-jogja-logo.webp')
            ->assertJsonPath('settings.site_favicon', '/uploads/lanyard-jogja-favicon.webp')
            ->assertJsonPath('settings.og_image', '/uploads/lanyard-jogja-logo.webp')
            ->assertJsonPath('settings.contact_email', 'info@lanyardjogja.com');
    }

    public function test_rebrand_migration_updates_only_legacy_brand_settings(): void
    {
        foreach ([
            'site_title' => 'Pusat Tali Lanyard',
            'contact_email' => 'hello@pusattalilanyard.com',
            'site_logo' => '/uploads/custom-logo.webp',
            'contact_phone' => '+62 822-1020-0700',
            'social_instagram' => 'https://instagram.com/pusattalilanyard',
        ] as $key => $value) {
            DB::table('setting')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'createdAt' => now(), 'updatedAt' => now()],
            );
        }

        $migration = require database_path('migrations/2026_08_03_000001_rebrand_active_settings_to_lanyard_jogja.php');
        $migration->up();

        $settings = DB::table('setting')->pluck('value', 'key');
        $this->assertSame('Lanyard Jogja', $settings['site_title']);
        $this->assertSame('https://lanyardjogja.com', $settings['site_url']);
        $this->assertSame('info@lanyardjogja.com', $settings['contact_email']);
        $this->assertSame('/uploads/lanyard-jogja-logo.webp', $settings['site_logo']);
        $this->assertSame('+62 822-1020-0700', $settings['contact_phone']);
        $this->assertSame('', $settings['social_instagram']);
    }
}
