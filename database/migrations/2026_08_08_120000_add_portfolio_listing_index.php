<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const INDEX_NAME = 'portfolio_published_sortorder_createdat_index';

    public function up(): void
    {
        if (! Schema::hasTable('portfolio')) {
            return;
        }

        $indexNames = array_column(Schema::getIndexes('portfolio'), 'name');

        if (! in_array(self::INDEX_NAME, $indexNames, true)) {
            Schema::table('portfolio', function (Blueprint $table): void {
                $table->index(
                    ['published', 'sortOrder', 'createdAt'],
                    self::INDEX_NAME,
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('portfolio')) {
            return;
        }

        $indexNames = array_column(Schema::getIndexes('portfolio'), 'name');

        if (in_array(self::INDEX_NAME, $indexNames, true)) {
            Schema::table('portfolio', function (Blueprint $table): void {
                $table->dropIndex(self::INDEX_NAME);
            });
        }
    }
};
