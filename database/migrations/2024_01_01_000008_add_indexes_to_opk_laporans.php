<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->index('status_verifikasi', 'idx_status_verifikasi');
            $table->index('kondisi', 'idx_kondisi');
            $table->index('kecamatan_id', 'idx_kecamatan_id');
            $table->index('kategori_id', 'idx_kategori_id');
            $table->index('created_at', 'idx_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->dropIndex('idx_status_verifikasi');
            $table->dropIndex('idx_kondisi');
            $table->dropIndex('idx_kecamatan_id');
            $table->dropIndex('idx_kategori_id');
            $table->dropIndex('idx_created_at');
        });
    }
};
