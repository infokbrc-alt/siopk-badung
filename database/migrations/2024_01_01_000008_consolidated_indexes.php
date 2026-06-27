<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->index(['status_verifikasi', 'kondisi'], 'idx_status_kondisi');
            $table->index(['status_verifikasi', 'ai_urgency_score'], 'idx_status_urgensi');
            $table->index(['latitude', 'longitude'], 'idx_lat_lng');
            $table->index('created_at', 'idx_created_at');
        });

        Schema::table('opk_categories', function (Blueprint $table) {
            $table->unique('nomor', 'idx_categories_nomor_unique');
        });
    }

    public function down(): void
    {
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->dropIndex('idx_status_kondisi');
            $table->dropIndex('idx_status_urgensi');
            $table->dropIndex('idx_lat_lng');
            $table->dropIndex('idx_created_at');
        });

        Schema::table('opk_categories', function (Blueprint $table) {
            $table->dropUnique('idx_categories_nomor_unique');
        });
    }
};
