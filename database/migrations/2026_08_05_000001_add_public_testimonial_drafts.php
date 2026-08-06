<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('testimonial')) {
            return;
        }

        $now = now();

        foreach ([
            [
                'name' => 'Rizky Pratama',
                'role' => 'Koordinator Komunitas',
                'quote' => 'Pesanan untuk kegiatan komunitas kami diproses dengan jelas dari awal. Timnya membantu menyesuaikan desain dan hasil akhirnya rapi saat dibagikan ke anggota.',
                'imageUrl' => '/images/testimonials/rizky-pratama.webp',
                'sortOrder' => 40,
            ],
            [
                'name' => 'Dewi Lestari',
                'role' => 'Pemilik Brand Lokal',
                'quote' => 'Kami butuh lanyard yang selaras dengan identitas brand. Warna dan detail desain diperhatikan dengan baik, jadi hasilnya terasa lebih profesional.',
                'imageUrl' => '/images/testimonials/dewi-lestari.webp',
                'sortOrder' => 50,
            ],
            [
                'name' => 'Farhan Ramadhan',
                'role' => 'Panitia Event',
                'quote' => 'Komunikasinya responsif dan kebutuhan acara kami dibantu sampai detail pengiriman. Lanyard datang rapi sehingga siap dipakai untuk hari kegiatan.',
                'imageUrl' => '/images/testimonials/farhan-ramadhan.webp',
                'sortOrder' => 60,
            ],
        ] as $testimonial) {
            DB::table('testimonial')->updateOrInsert(
                [
                    'name' => $testimonial['name'],
                    'role' => $testimonial['role'],
                ],
                [
                    ...$testimonial,
                    'published' => true,
                    'createdAt' => $now,
                    'updatedAt' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('testimonial')) {
            return;
        }

        DB::table('testimonial')
            ->whereIn('name', ['Rizky Pratama', 'Dewi Lestari', 'Farhan Ramadhan'])
            ->delete();
    }
};
