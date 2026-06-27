<?php

namespace Tests\Feature;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPublikTest extends TestCase
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
    }

    public function test_home_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertSuccessful();
    }

    public function test_daftar_opk_page_loads(): void
    {
        $response = $this->get('/daftar-opk');
        $response->assertSuccessful();
    }

    public function test_opk_detail_page_loads_for_disetujui(): void
    {
        $opk = OpkLaporan::first();
        $response = $this->get('/opk/'.$opk->id);
        $response->assertSuccessful();
    }

    public function test_opk_detail_returns_404_for_non_disetujui(): void
    {
        $opk = OpkLaporan::first();
        $opk->update(['status_verifikasi' => 'menunggu']);

        $response = $this->get('/opk/'.$opk->id);
        $response->assertNotFound();
    }

    public function test_peta_data_returns_json(): void
    {
        $response = $this->get('/peta/data');
        $response->assertSuccessful();
        $json = $response->json();
        $this->assertIsArray($json);
    }
}
