<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('setting')) {
            return;
        }

        $now = now();
        $values = [
            'site_title' => 'Lanyard Jogja',
            'site_url' => 'https://lanyardjogja.com',
            'logo_url' => '/uploads/lanyard-jogja-logo.webp',
            'site_logo' => '/uploads/lanyard-jogja-logo.webp',
            'site_logo_header' => '/uploads/lanyard-jogja-logo.webp',
            'site_logo_mobile' => '/uploads/lanyard-jogja-logo.webp',
            'favicon_url' => '/uploads/lanyard-jogja-favicon.webp',
            'site_favicon' => '/uploads/lanyard-jogja-favicon.webp',
            'og_image' => '/uploads/lanyard-jogja-logo.webp',
            'contact_email' => 'info@lanyardjogja.com',
        ];

        foreach ($values as $key => $value) {
            $existing = DB::table('setting')->where('key', $key)->first();
            if ($existing === null) {
                DB::table('setting')->insert([
                    'key' => $key,
                    'value' => $value,
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ]);
                continue;
            }

            DB::table('setting')->where('key', $key)->update([
                'value' => $value,
                'updatedAt' => $now,
            ]);
        }

        foreach ([
            'site_description' => 'Pusat produksi tali lanyard custom untuk kantor, sekolah, komunitas, dan event dengan cetak tajam serta proses cepat di Jogja.',
            'seo_meta_title' => 'Lanyard Jogja | Cetak Lanyard Custom Cepat Berkualitas',
            'seo_meta_description' => 'Cetak tali lanyard custom dari Jogja untuk kantor, sekolah, komunitas, dan event. Desain bebas, hasil tajam, proses cepat, dan pengiriman ke seluruh Indonesia.',
            'footer_text' => '© 2026 Lanyard Jogja. All rights reserved.',
        ] as $key => $value) {
            if (DB::table('setting')->where('key', $key)->exists()) {
                DB::table('setting')->where('key', $key)->update([
                    'value' => $value,
                    'updatedAt' => $now,
                ]);
            }
        }

        foreach ([
            'google_analytics_measurement_id',
            'google_analytics_property_id',
            'google_search_console_site_url',
            'google_site_verification',
            'bing_site_verification',
            'google_spreadsheet_id',
            'google_spreadsheet_range',
            'google_service_account_json',
            'google_products_last_synced_at',
            'google_products_last_sync_summary',
            'social_instagram',
            'social_facebook',
            'social_tiktok',
        ] as $key) {
            DB::table('setting')->where('key', $key)->update([
                'value' => '',
                'updatedAt' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Active brand migrations intentionally do not restore stale identity or credentials.
    }
};
