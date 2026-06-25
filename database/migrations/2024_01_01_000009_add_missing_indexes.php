<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opk_categories', function (Blueprint $table) {
            $table->unique('nomor', 'idx_categories_nomor_unique');
        });

        Schema::table('opk_fotos', function (Blueprint $table) {
            $table->index('is_utama', 'idx_fotos_is_utama');
        });
    }

    public function down(): void
    {
        Schema::table('opk_categories', function (Blueprint $table) {
            $table->dropUnique('idx_categories_nomor_unique');
        });

        Schema::table('opk_fotos', function (Blueprint $table) {
            $table->dropIndex('idx_fotos_is_utama');
        });
    }
};
