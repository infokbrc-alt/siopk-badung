<?php

namespace Tests\Unit;

use App\Models\OpkDokumen;
use App\Models\OpkLaporan;
use App\Models\OpkCategory;
use App\Models\Kecamatan;
use App\Models\DesaDinas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpkDokumenTest extends TestCase
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
        $dokumen = OpkDokumen::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'dokumen.pdf',
            'path' => 'dokumen_opk/1/dokumen.pdf',
            'jenis' => 'dokumen_pendukung',
        ]);

        $this->assertStringContainsString('dokumen_opk/1/dokumen.pdf', $dokumen->url);
    }

    public function test_laporan_relation(): void
    {
        $dokumen = OpkDokumen::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'dokumen.pdf',
            'path' => 'dokumen_opk/1/dokumen.pdf',
        ]);

        $this->assertEquals($this->laporan->id, $dokumen->laporan->id);
    }

    public function test_optional_fields_are_nullable(): void
    {
        $dokumen = OpkDokumen::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'dokumen.pdf',
            'path' => 'dokumen_opk/1/dokumen.pdf',
        ]);

        $this->assertNull($dokumen->judul);
        $this->assertNull($dokumen->jenis);
        $this->assertNull($dokumen->ukuran_bytes);
    }
}
