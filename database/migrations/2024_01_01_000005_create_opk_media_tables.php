<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel foto OPK (multi foto per laporan)
        Schema::create('opk_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('opk_laporans')->onDelete('cascade');
            $table->string('nama_file', 255);
            $table->string('path', 500);
            $table->string('keterangan', 255)->nullable();
            $table->boolean('is_utama')->default(false); // foto cover
            $table->unsignedInteger('urutan')->default(0);
            $table->unsignedBigInteger('ukuran_bytes')->nullable();
            $table->string('mime_type', 50)->nullable();
            $table->timestamps();
        });

        // Tabel dokumen pendukung
        Schema::create('opk_dokumens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('opk_laporans')->onDelete('cascade');
            $table->string('nama_file', 255);
            $table->string('path', 500);
            $table->string('judul', 200)->nullable();
            $table->string('jenis', 50)->nullable(); // SK, sertifikat, artikel, lainnya
            $table->unsignedBigInteger('ukuran_bytes')->nullable();
            $table->timestamps();
        });

        // Tabel video lokal
        Schema::create('opk_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('opk_laporans')->onDelete('cascade');
            $table->string('nama_file', 255)->nullable();
            $table->string('path', 500)->nullable();
            $table->string('link_eksternal', 500)->nullable(); // YouTube/GDrive
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });

        // Riwayat status verifikasi
        Schema::create('opk_riwayat_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('opk_laporans')->onDelete('cascade');
            $table->string('status_lama', 50);
            $table->string('status_baru', 50);
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opk_riwayat_status');
        Schema::dropIfExists('opk_videos');
        Schema::dropIfExists('opk_dokumens');
        Schema::dropIfExists('opk_fotos');
    }
};
