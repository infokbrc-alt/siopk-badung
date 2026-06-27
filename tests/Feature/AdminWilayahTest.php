<?php

namespace Tests\Feature;

use App\Models\Kecamatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWilayahTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Kecamatan $kecamatan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
    }

    public function test_wilayah_index_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/wilayah');
        $response->assertSuccessful();
    }

    public function test_kecamatan_store_creates_new(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/wilayah/kecamatan', [
            'nama' => 'Mengwi',
            'kode' => 'MW',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kecamatans', ['nama' => 'Mengwi']);
    }

    public function test_kecamatan_update_changes_data(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/wilayah/kecamatan/'.$this->kecamatan->id, [
            'nama' => 'Kuta Updated',
            'kode' => 'KU',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kecamatans', ['nama' => 'Kuta Updated']);
    }

    public function test_kecamatan_destroy_deletes(): void
    {
        $response = $this->actingAs($this->admin)->delete('/admin/wilayah/kecamatan/'.$this->kecamatan->id);
        $response->assertRedirect();

        $this->assertNull(Kecamatan::find($this->kecamatan->id));
    }

    public function test_desa_dinas_store_creates_new(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/wilayah/desa-dinas', [
            'nama' => 'Seminyak',
            'kecamatan_id' => $this->kecamatan->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('desa_dinas', ['nama' => 'Seminyak']);
    }

    public function test_desa_adat_store_creates_new(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/wilayah/desa-adat', [
            'nama' => 'Desa Adat Kuta',
            'kecamatan_id' => $this->kecamatan->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('desa_adats', ['nama' => 'Desa Adat Kuta']);
    }

    public function test_petugas_cannot_access_wilayah(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($petugas)->get('/admin/wilayah');
        $response->assertForbidden();
    }
}
