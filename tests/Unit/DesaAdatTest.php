<?php

namespace Tests\Unit;

use App\Models\DesaAdat;
use App\Models\Kecamatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesaAdatTest extends TestCase
{
    use RefreshDatabase;

    private Kecamatan $kecamatan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
    }

    public function test_kecamatan_relation(): void
    {
        $desaAdat = DesaAdat::create(['nama' => 'Desa Adat Kuta', 'kecamatan_id' => $this->kecamatan->id]);

        $this->assertEquals($this->kecamatan->id, $desaAdat->kecamatan->id);
    }

    public function test_banjar_adat_is_nullable(): void
    {
        $desaAdat = DesaAdat::create(['nama' => 'Desa Adat Kuta', 'kecamatan_id' => $this->kecamatan->id]);

        $this->assertNull($desaAdat->banjar_adat);
    }
}
