<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\OpkCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminKategoriTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
    }

    public function test_kategori_index_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/kategori');
        $response->assertSuccessful();
    }

    public function test_kategori_store_creates_new(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/kategori', [
            'nomor' => 2,
            'nama' => 'Adat Istiadat',
            'ikon' => '🏛️',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('opk_categories', ['nama' => 'Adat Istiadat']);
    }

    public function test_kategori_update_changes_data(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/kategori/1', [
            'nomor' => 1,
            'nama' => 'Seni Pertunjukan',
            'ikon' => '🎭',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('opk_categories', ['nama' => 'Seni Pertunjukan']);
    }

    public function test_kategori_destroy_deletes(): void
    {
        $response = $this->actingAs($this->admin)->delete('/admin/kategori/1');
        $response->assertRedirect();

        $this->assertNull(OpkCategory::find(1));
    }

    public function test_petugas_cannot_access_kategori(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($petugas)->get('/admin/kategori');
        $response->assertForbidden();
    }
}
