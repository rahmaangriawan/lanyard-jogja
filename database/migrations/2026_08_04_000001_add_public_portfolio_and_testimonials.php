<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('portfolio')) {
            Schema::table('portfolio', function (Blueprint $table) {
                if (! Schema::hasColumn('portfolio', 'published')) {
                    $table->boolean('published')->default(true)->after('link');
                }

                if (! Schema::hasColumn('portfolio', 'sortOrder')) {
                    $table->integer('sortOrder')->default(0)->after('published');
                }
            });
        }

        if (! Schema::hasTable('testimonial')) {
            Schema::create('testimonial', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('role')->nullable();
                $table->text('quote');
                $table->string('imageUrl')->nullable();
                $table->boolean('published')->default(true);
                $table->integer('sortOrder')->default(0);
                $table->timestamp('createdAt')->useCurrent();
                $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
                $table->index(['published', 'sortOrder', 'createdAt']);
            });
        }

        if (Schema::hasTable('testimonial') && ! DB::table('testimonial')->exists()) {
            DB::table('testimonial')->insert([
                [
                    'name' => 'Anggi Putri',
                    'role' => 'Admin Sekolah',
                    'quote' => 'Kami pesan lanyard custom untuk kegiatan sekolah, hasilnya memuaskan. Cetakan jelas, tidak mudah luntur, dan pengerjaannya tepat waktu.',
                    'imageUrl' => '/uploads/testimoni-anggi-putri.webp',
                    'published' => true,
                    'sortOrder' => 10,
                    'createdAt' => now(),
                    'updatedAt' => now(),
                ],
                [
                    'name' => 'Andi Pratama',
                    'role' => 'Event Organizer',
                    'quote' => 'Pelayanan cepat dan hasil lanyardnya rapi. Warna sesuai desain, bahan nyaman dipakai, dan timnya responsif sampai barang diterima.',
                    'imageUrl' => '/uploads/testimoni-andi-pratama.webp',
                    'published' => true,
                    'sortOrder' => 20,
                    'createdAt' => now(),
                    'updatedAt' => now(),
                ],
                [
                    'name' => 'Dimas Nugroho',
                    'role' => 'Owner UMKM',
                    'quote' => 'Kualitas selalu konsisten, harga masuk akal, dan desain bisa disesuaikan dengan brand. Cocok untuk kebutuhan kantor maupun promosi.',
                    'imageUrl' => '/uploads/testimoni-dimas-nugroho.webp',
                    'published' => true,
                    'sortOrder' => 30,
                    'createdAt' => now(),
                    'updatedAt' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonial');

        if (Schema::hasTable('portfolio')) {
            Schema::table('portfolio', function (Blueprint $table) {
                if (Schema::hasColumn('portfolio', 'sortOrder')) {
                    $table->dropColumn('sortOrder');
                }

                if (Schema::hasColumn('portfolio', 'published')) {
                    $table->dropColumn('published');
                }
            });
        }
    }
};
