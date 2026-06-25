<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah deleted_at jika belum ada (untuk SoftDeletes)
        if (!Schema::hasColumn('opk_laporans', 'deleted_at')) {
            Schema::table('opk_laporans', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::table('opk_laporans', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
