<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\OpkLaporan;
use App\Models\OpkCategory;
use App\Models\Kecamatan;
use App\Models\DesaDinas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVerifikasiTest extends TestCase
{
    use RefreshDatabase;

    private User $verifikator;
    private OpkLaporan $laporan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->verifikator = User::factory()->create(['role' => 'verifikator']);

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
        DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $kecamatan->id]);

        $this->laporan = OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00001',
            'nama_opk' => 'Tari Kecak',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Test',
            'deskripsi_umum' => 'Test description',
            'status_verifikasi' => 'review_dinas',
            'pelapor_nama' => 'Test User',
            'pelapor_nik' => '1234567890123456',
            'pelapor_whatsapp' => '08123456789',
        ]);
    }

    public function test_verifikasi_index_page_loads(): void
    {
        $response = $this->actingAs($this->verifikator)->get('/admin/verifikasi');
        $response->assertSuccessful();
    }

    public function test_verifikasi_show_page_loads(): void
    {
        $response = $this->actingAs($this->verifikator)->get('/admin/verifikasi/' . $this->laporan->id);
        $response->assertSuccessful();
    }

    public function test_setujui_laporan_changes_status(): void
    {
        $response = $this->actingAs($this->verifikator)
            ->post('/admin/verifikasi/' . $this->laporan->id . '/setujui');

        $response->assertRedirect();
        $this->assertDatabaseHas('opk_laporans', [
            'id' => $this->laporan->id,
            'status_verifikasi' => 'disetujui',
        ]);
    }

    public function test_tolak_laporan_changes_status(): void
    {
        $response = $this->actingAs($this->verifikator)
            ->post('/admin/verifikasi/' . $this->laporan->id . '/tolak', [
                'alasan' => 'tidak_valid',
                'catatan' => 'Perbaiki deskripsi',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('opk_laporans', [
            'id' => $this->laporan->id,
            'status_verifikasi' => 'ditolak',
        ]);
    }

    public function test_petugas_cannot_access_verifikasi(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $response = $this->actingAs($petugas)->get('/admin/verifikasi');
        $response->assertForbidden();
    }
}
