<?php

namespace Tests\Unit;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use App\Models\User;
use App\Services\VerifikasiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifikasiServiceTest extends TestCase
{
    use RefreshDatabase;

    private VerifikasiService $service;

    private User $verifikator;

    private OpkLaporan $laporan;

    protected function setUp(): void
    {
        parent::setUp();

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
        DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $kecamatan->id]);

        $this->verifikator = User::factory()->create(['role' => 'verifikator']);

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

        $this->service = new VerifikasiService;
    }

    public function test_setujui_laporan_changes_status_to_disetujui(): void
    {
        $this->service->setujuiLaporan($this->laporan, $this->verifikator);

        $this->laporan->refresh();
        $this->assertEquals('disetujui', $this->laporan->status_verifikasi);
        $this->assertEquals($this->verifikator->id, $this->laporan->diverifikasi_oleh);
        $this->assertNotNull($this->laporan->tanggal_verifikasi);
    }

    public function test_tolak_laporan_changes_status_to_ditolak(): void
    {
        $this->service->tolakLaporan(
            $this->laporan,
            $this->verifikator,
            'Deskripsi tidak memadai',
            'Data kurang lengkap'
        );

        $this->laporan->refresh();
        $this->assertEquals('ditolak', $this->laporan->status_verifikasi);
        $this->assertStringContainsString('Deskripsi tidak memadai', $this->laporan->catatan_verifikasi);
    }

    public function test_verification_creates_riwayat_record(): void
    {
        $this->service->setujuiLaporan($this->laporan, $this->verifikator);

        $this->assertDatabaseHas('opk_riwayat_status', [
            'laporan_id' => $this->laporan->id,
            'status_baru' => 'disetujui',
            'user_id' => $this->verifikator->id,
        ]);
    }
}
