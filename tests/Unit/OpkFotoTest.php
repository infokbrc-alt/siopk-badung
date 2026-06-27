<?php

namespace Tests\Unit;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\OpkCategory;
use App\Models\OpkFoto;
use App\Models\OpkLaporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpkFotoTest extends TestCase
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

    public function test_url_accessor_returns_asset_uri(): void
    {
        $foto = OpkFoto::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'foto.jpg',
            'path' => 'foto_opk/1/foto.jpg',
            'is_utama' => true,
            'urutan' => 0,
        ]);

        $this->assertStringContainsString('foto_opk/1/foto.jpg', $foto->url);
    }

    public function test_is_utama_casts_to_boolean(): void
    {
        $foto = OpkFoto::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'foto.jpg',
            'path' => 'foto_opk/1/foto.jpg',
            'is_utama' => 1,
            'urutan' => 0,
        ]);

        $this->assertTrue($foto->is_utama);
    }

    public function test_laporan_relation(): void
    {
        $foto = OpkFoto::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'foto.jpg',
            'path' => 'foto_opk/1/foto.jpg',
            'is_utama' => true,
            'urutan' => 0,
        ]);

        $this->assertEquals($this->laporan->id, $foto->laporan->id);
    }

    public function test_is_utama_defaults_to_false(): void
    {
        $foto = OpkFoto::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'foto.jpg',
            'path' => 'foto_opk/1/foto.jpg',
            'is_utama' => false,
            'urutan' => 1,
        ]);

        $this->assertFalse($foto->is_utama);
    }
}
