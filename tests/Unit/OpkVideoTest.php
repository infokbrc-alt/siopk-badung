<?php

namespace Tests\Unit;

use App\Models\OpkVideo;
use App\Models\OpkLaporan;
use App\Models\OpkCategory;
use App\Models\Kecamatan;
use App\Models\DesaDinas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpkVideoTest extends TestCase
{
    use RefreshDatabase;

    private OpkLaporan $laporan;

    protected function setUp(): void
    {
        parent::setUp();

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
        DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $kecamatan->id]);

        $this->laporan = OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00001',
            'nama_opk' => 'Tari Kecak',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Test',
            'deskripsi_umum' => 'Test description',
            'status_verifikasi' => 'disetujui',
            'pelapor_nama' => 'Test User',
            'pelapor_nik' => '1234567890123456',
            'pelapor_whatsapp' => '08123456789',
        ]);
    }

    public function test_is_local_returns_true_when_path_is_set(): void
    {
        $video = OpkVideo::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'video.mp4',
            'path' => 'video_opk/1/video.mp4',
        ]);

        $this->assertTrue($video->isLocal());
        $this->assertFalse($video->isExternal());
    }

    public function test_is_external_returns_true_when_link_is_set(): void
    {
        $video = OpkVideo::create([
            'laporan_id' => $this->laporan->id,
            'link_eksternal' => 'https://youtube.com/watch?v=abc123',
        ]);

        $this->assertTrue($video->isExternal());
        $this->assertFalse($video->isLocal());
    }

    public function test_is_local_returns_false_with_empty_path(): void
    {
        $video = OpkVideo::create([
            'laporan_id' => $this->laporan->id,
        ]);

        $this->assertFalse($video->isLocal());
        $this->assertFalse($video->isExternal());
    }

    public function test_laporan_relation(): void
    {
        $video = OpkVideo::create([
            'laporan_id' => $this->laporan->id,
            'link_eksternal' => 'https://youtube.com/watch?v=abc123',
        ]);

        $this->assertEquals($this->laporan->id, $video->laporan->id);
    }
}
