<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminAiTest extends TestCase
{
    use RefreshDatabase;

    private User $verifikator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->verifikator = User::factory()->create(['role' => 'verifikator']);
    }

    public function test_ringkasan_halaman_page_loads(): void
    {
        $response = $this->actingAs($this->verifikator)->get('/admin/ai/ringkasan-halaman');
        $response->assertSuccessful();
    }

    public function test_ringkasan_returns_response(): void
    {
        Cache::flush();
        $response = $this->actingAs($this->verifikator)->get('/admin/ai/ringkasan');
        $response->assertSuccessful();
    }

    public function test_clear_cache_requires_admin(): void
    {
        $response = $this->actingAs($this->verifikator)->post('/admin/ai/clear-cache');
        $response->assertForbidden();
    }

    public function test_clear_cache_allowed_for_superadmin(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($superadmin)->post('/admin/ai/clear-cache');
        $response->assertRedirect();
    }

    public function test_petugas_cannot_access_ai(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($petugas)->get('/admin/ai/ringkasan-halaman');
        $response->assertForbidden();
    }
}
