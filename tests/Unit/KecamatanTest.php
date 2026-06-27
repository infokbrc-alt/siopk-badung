<?php

namespace Tests\Unit;

use App\Models\DesaAdat;
use App\Models\DesaDinas;
use App\Models\Kecamatan;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KecamatanTest extends TestCase
{
    use RefreshDatabase;

    private Kecamatan $kecamatan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
    }

    public function test_desa_dinas_relation(): void
    {
        DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $this->kecamatan->id]);
        DesaDinas::create(['nama' => 'Legian', 'kecamatan_id' => $this->kecamatan->id]);

        $this->assertCount(2, $this->kecamatan->desaDinas);
    }

    public function test_desa_adats_relation(): void
    {
        DesaAdat::create(['nama' => 'Desa Adat Kuta', 'kecamatan_id' => $this->kecamatan->id]);

        $this->assertCount(1, $this->kecamatan->desaAdats);
    }

    public function test_laporans_relation(): void
    {
        $this->assertInstanceOf(
            HasMany::class,
            $this->kecamatan->laporans()
        );
    }

    public function test_kode_is_nullable(): void
    {
        $kecamatan = Kecamatan::create(['nama' => 'Kuta Utara']);

        $this->assertNull($kecamatan->kode);
    }
}
