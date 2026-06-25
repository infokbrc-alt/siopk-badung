<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opk_laporans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_laporan', 30)->unique(); // SIOPK-2025-00001

            // === IDENTITAS OPK ===
            $table->string('nama_opk', 200);
            $table->foreignId('kategori_id')->constrained('opk_categories');
            $table->year('tahun_diketahui')->nullable();
            $table->string('tahun_keterangan', 100)->nullable(); // "abad ke-17", "pra-kolonial"
            $table->enum('status_pelindungan', [
                'belum_terdaftar',
                'sudah_didata_dinas',
                'sk_kabupaten',
                'sk_provinsi',
                'wbtb_nasional'
            ])->default('belum_terdaftar');
            $table->enum('kondisi', ['baik', 'waspada', 'kritis'])->default('baik');

            // === LOKASI & WILAYAH ===
            $table->foreignId('kecamatan_id')->constrained('kecamatans');
            $table->foreignId('desa_dinas_id')->constrained('desa_dinas');
            $table->string('nama_desa_adat', 150);
            $table->string('banjar_adat', 150)->nullable();
            $table->string('lokasi_spesifik', 255)->nullable(); // nama pura, balai banjar, dll
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // === DESKRIPSI DETAIL ===
            $table->text('deskripsi_umum');
            $table->text('sejarah_asal_usul')->nullable();
            $table->text('nilai_makna_budaya')->nullable();
            $table->string('bahasa_digunakan', 100)->nullable();
            $table->string('aksara_digunakan', 100)->nullable();
            $table->enum('frekuensi_pelaksanaan', [
                'rutin_harian',
                'rutin_mingguan',
                'rutin_bulanan',
                'rutin_6bulanan',
                'rutin_tahunan',
                'langka',
                'sangat_langka',
                'tidak_ada'
            ])->nullable();
            $table->enum('status_kepemilikan', [
                'desa_adat',
                'pura_keagamaan',
                'pribadi_keluarga',
                'negara_pemerintah',
                'tidak_jelas'
            ])->nullable();

            // === PRAKTISI (OPSIONAL) ===
            $table->string('praktisi_nama', 150)->nullable();
            $table->unsignedTinyInteger('praktisi_usia')->nullable();
            $table->string('praktisi_kontak', 50)->nullable(); // disimpan terenkripsi

            // === DATA PELAPOR ===
            $table->enum('tipe_pelapor', ['masyarakat', 'tokoh_adat', 'petugas_dinas'])
                  ->default('masyarakat');
            $table->string('pelapor_nama', 150);
            $table->string('pelapor_nik', 20);
            $table->string('pelapor_whatsapp', 20);
            $table->string('pelapor_email', 150)->nullable();

            // === STATUS VERIFIKASI ===
            $table->enum('status_verifikasi', [
                'menunggu',     // baru masuk
                'ai_review',    // sedang diproses AI
                'review_dinas', // menunggu verifikator
                'disetujui',    // masuk database resmi
                'ditolak',      // ditolak
                'duplikat'      // duplikat
            ])->default('menunggu');

            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users');
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->text('catatan_verifikasi')->nullable();

            // === AI ANALISIS ===
            $table->decimal('ai_urgency_score', 4, 2)->nullable(); // 0.00 - 10.00
            $table->decimal('ai_duplikat_score', 5, 2)->nullable(); // 0.00 - 100.00 (%)
            $table->text('ai_rekomendasi')->nullable();
            $table->foreignId('ai_duplikat_of')->nullable()->constrained('opk_laporans');

            // === VIDEO / LINK ===
            $table->string('link_video', 500)->nullable();
            $table->string('link_dokumen_eksternal', 500)->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opk_laporans');
    }
};
