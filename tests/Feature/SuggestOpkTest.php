<?php

namespace Tests\Feature;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuggestOpkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
        DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $kecamatan->id]);

        OpkLaporan::create([
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

        OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00002',
            'nama_opk' => 'Tari Barong',
            'kategori_id' => 1,
            'kondisi' => 'waspada',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Test',
            'deskripsi_umum' => 'Test description',
            'status_verifikasi' => 'disetujui',
            'pelapor_nama' => 'Test User',
            'pelapor_nik' => '1234567890123457',
            'pelapor_whatsapp' => '08123456780',
        ]);
    }

    public function test_suggest_returns_matching_opk(): void
    {
        $response = $this->get('/api/suggest-opk?q=Tari');
        $response->assertSuccessful();

        $data = $response->json();
        $this->assertCount(2, $data);
        $this->assertEquals('Tari Barong', $data[0]['nama']);
    }

    public function test_suggest_returns_empty_for_short_query(): void
    {
        $response = $this->get('/api/suggest-opk?q=T');
        $response->assertSuccessful();

        $this->assertCount(0, $response->json());
    }

    public function test_suggest_returns_empty_for_no_match(): void
    {
        $response = $this->get('/api/suggest-opk?q=XYZ');
        $response->assertSuccessful();

        $this->assertCount(0, $response->json());
    }

    public function test_suggest_excludes_non_disetujui(): void
    {
        OpkLaporan::first()->update(['status_verifikasi' => 'menunggu']);

        $response = $this->get('/api/suggest-opk?q=Tari');
        $response->assertSuccessful();

        $this->assertCount(1, $response->json());
    }
}
