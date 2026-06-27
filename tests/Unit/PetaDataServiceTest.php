<?php

namespace Tests\Unit;

use App\Models\OpkLaporan;
use App\Models\OpkCategory;
use App\Models\Kecamatan;
use App\Models\DesaDinas;
use App\Services\PetaDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PetaDataServiceTest extends TestCase
{
    use RefreshDatabase;

    private PetaDataService $service;

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
            'latitude' => -8.7186,
            'longitude' => 115.1686,
        ]);

        $this->service = new PetaDataService();
    }

    public function test_get_peta_data_returns_array(): void
    {
        $request = new Request();
        $data = $this->service->getPetaData($request);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('Tari Kecak', $data[0]['nama']);
    }

    public function test_get_peta_data_admin_returns_array(): void
    {
        $request = new Request();
        $data = $this->service->getPetaData($request, true);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
    }

    public function test_invalidate_cache_increments_version(): void
    {
        Cache::flush();
        $initialVersion = Cache::get('peta_data_version', 0);
        PetaDataService::invalidateCache();
        $newVersion = Cache::get('peta_data_version', 0);

        $this->assertGreaterThan($initialVersion, $newVersion);
    }
}
