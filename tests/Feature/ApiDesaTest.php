<?php

namespace Tests\Feature;

use App\Models\Kecamatan;
use App\Models\DesaDinas;
use App\Models\DesaAdat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiDesaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
        DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $kecamatan->id]);
        DesaDinas::create(['nama' => 'Legian', 'kecamatan_id' => $kecamatan->id]);
        DesaAdat::create(['nama' => 'Desa Adat Kuta', 'kecamatan_id' => $kecamatan->id]);
    }

    public function test_desa_dinas_api_returns_json(): void
    {
        $response = $this->get('/api/desa-dinas?kecamatan_id=1');
        $response->assertSuccessful();
        $data = $response->json();

        $this->assertCount(2, $data);
        $this->assertEquals('Kuta', $data[0]['nama']);
    }

    public function test_desa_dinas_api_requires_kecamatan_id(): void
    {
        $response = $this->get('/api/desa-dinas');
        $response->assertSessionHasErrors(['kecamatan_id']);
    }

    public function test_desa_dinas_api_requires_valid_kecamatan_id(): void
    {
        $response = $this->get('/api/desa-dinas?kecamatan_id=999');
        $response->assertSessionHasErrors(['kecamatan_id']);
    }

    public function test_desa_adat_api_returns_json(): void
    {
        $response = $this->get('/api/desa-adat?kecamatan_id=1');
        $response->assertSuccessful();
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals('Desa Adat Kuta', $data[0]['nama']);
    }

    public function test_desa_adat_api_requires_kecamatan_id(): void
    {
        $response = $this->get('/api/desa-adat');
        $response->assertSessionHasErrors(['kecamatan_id']);
    }
}
