<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(DB::select('SHOW INDEXES FROM opk_laporans'))->pluck('Key_name');

        if (!$indexes->contains('idx_kec_status')) {
            DB::statement('CREATE INDEX idx_kec_status ON opk_laporans (kecamatan_id, status_verifikasi)');
        }
        if (!$indexes->contains('idx_kat_status')) {
            DB::statement('CREATE INDEX idx_kat_status ON opk_laporans (kategori_id, status_verifikasi)');
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_kec_status ON opk_laporans');
        DB::statement('DROP INDEX IF EXISTS idx_kat_status ON opk_laporans');
    }
};
