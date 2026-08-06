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
        foreach ([
            'seo_meta_title' => 'Lanyard Custom Jogja Premium',
            'seo_meta_description' => 'Lanyard Jogja melayani cetak tali lanyard custom berkualitas tinggi dengan harga terjangkau di Jogja.',
        ] as $key => $value) {
            $query = DB::table('setting')->where('key', $key);
            if ($query->exists()) {
                $query->update(['value' => $value, 'updatedAt' => $now]);
            } else {
                DB::table('setting')->insert([
                    'key' => $key,
                    'value' => $value,
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep the active SEO copy stable when rolling back historical migrations.
    }
};
