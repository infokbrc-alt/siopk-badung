<?php

namespace Tests\Unit;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesaDinasTest extends TestCase
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
        $desa = DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $this->kecamatan->id]);

        $this->assertEquals($this->kecamatan->id, $desa->kecamatan->id);
    }

    public function test_laporans_relation(): void
    {
        $desa = DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $this->kecamatan->id]);

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Relations\HasMany::class,
            $desa->laporans()
        );
    }

    public function test_kode_is_nullable(): void
    {
        $desa = DesaDinas::create(['nama' => 'Seminyak', 'kecamatan_id' => $this->kecamatan->id]);

        $this->assertNull($desa->kode);
    }
}
