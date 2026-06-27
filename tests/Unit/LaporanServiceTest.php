<?php

namespace Tests\Unit;

use App\Models\OpkLaporan;
use App\Models\OpkCategory;
use App\Models\Kecamatan;
use App\Models\DesaDinas;
use App\Services\LaporanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LaporanServiceTest extends TestCase
{
    use RefreshDatabase;

    private LaporanService $service;

    protected function setUp(): void
    {
        parent::setUp();

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Mengwi', 'kode' => 'MW']);
        DesaDinas::create(['nama' => 'Mengwi', 'kecamatan_id' => $kecamatan->id]);

        $this->service = new LaporanService();
    }

    public function test_create_laporan_returns_opk_laporan_with_menunggu_status(): void
    {
        $data = [
            'nama_opk' => 'Tari Legong',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'status_pelindungan' => 'belum_terdaftar',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Mengwi',
            'deskripsi_umum' => 'Tarian tradisional Bali.',
            'tipe_pelapor' => 'masyarakat',
            'pelapor_nama' => 'Test User',
            'pelapor_nik' => '1234567890123456',
            'pelapor_whatsapp' => '08123456789',
        ];

        $laporan = $this->service->createLaporan($data);

        $this->assertInstanceOf(OpkLaporan::class, $laporan);
        $this->assertEquals('menunggu', $laporan->status_verifikasi);
        $this->assertStringStartsWith('SIOPK-' . date('Y') . '-', $laporan->kode_laporan);
    }

    public function test_upload_fotos_creates_foto_records(): void
    {
        Storage::fake('public');

        $laporan = $this->createLaporan();

        $files = [
            UploadedFile::fake()->image('foto1.jpg', 800, 600),
            UploadedFile::fake()->image('foto2.jpg', 800, 600),
        ];

        $this->service->uploadFotos($laporan, $files, 'Keterangan foto');

        $this->assertCount(2, $laporan->fotos);
        $this->assertTrue($laporan->fotos->first()->is_utama);
        $this->assertEquals('Keterangan foto', $laporan->fotos->first()->keterangan);
    }

    public function test_upload_dokumen_creates_dokumen_record(): void
    {
        Storage::fake('public');

        $laporan = $this->createLaporan();

        $dokumen = UploadedFile::fake()->create('dokumen.pdf', 500, 'application/pdf');
        $this->service->uploadDokumen($laporan, $dokumen);

        $this->assertCount(1, $laporan->dokumens);
    }

    public function test_save_video_link_creates_video_record(): void
    {
        $laporan = $this->createLaporan();

        $this->service->saveVideoLink($laporan, 'https://youtube.com/watch?v=test');

        $this->assertCount(1, $laporan->videos);
        $this->assertEquals('https://youtube.com/watch?v=test', $laporan->videos->first()->link_eksternal);
    }

    public function test_save_video_link_skips_empty_url(): void
    {
        $laporan = $this->createLaporan();

        $this->service->saveVideoLink($laporan, null);

        $this->assertCount(0, $laporan->videos);
    }

    private function createLaporan(): OpkLaporan
    {
        return OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00001',
            'nama_opk' => 'Tari Kecak',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'status_pelindungan' => 'belum_terdaftar',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Test',
            'deskripsi_umum' => 'Test description',
            'status_verifikasi' => 'menunggu',
            'pelapor_nama' => 'Test User',
            'pelapor_nik' => '1234567890123456',
            'pelapor_whatsapp' => '08123456789',
        ]);
    }
}
