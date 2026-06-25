<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\OpkLaporan;
use App\Models\OpkCategory;
use App\Models\Kecamatan;
use App\Models\DesaDinas;
use App\Services\AiOpkAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role'      => 'admin',
            'is_active' => true,
        ]);

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
        DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $kecamatan->id]);

        OpkLaporan::create([
            'kode_laporan'       => 'SIOPK-2025-00001',
            'nama_opk'           => 'Tari Kecak',
            'kategori_id'        => 1,
            'kondisi'            => 'baik',
            'kecamatan_id'       => $kecamatan->id,
            'desa_dinas_id'      => 1,
            'nama_desa_adat'     => 'Desa Adat Kuta',
            'deskripsi_umum'     => 'Tarian tradisional Bali yang terkenal',
            'status_verifikasi'  => 'disetujui',
            'pelapor_nama'       => 'Test User',
            'pelapor_nik'        => '1234567890123456',
            'pelapor_whatsapp'   => '08123456789',
            'latitude'           => -8.7186,
            'longitude'          => 115.1686,
        ]);
    }

    public function test_dashboard_accessible_by_admin(): void
    {
        Cache::flush();
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Tari Kecak');
    }

    public function test_dashboard_denied_for_unauthenticated(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_has_kpi_stats(): void
    {
        Cache::flush();
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }
}
