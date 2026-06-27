<?php

namespace Tests\Unit;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use App\Models\OpkRiwayatStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpkRiwayatStatusTest extends TestCase
{
    use RefreshDatabase;

    private OpkLaporan $laporan;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
        DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $kecamatan->id]);

        $this->user = User::factory()->create(['role' => 'verifikator']);

        $this->laporan = OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00001',
            'nama_opk' => 'Tari Kecak',
            'kategori_id' => 1,
            'kondisi' => 'baik',
            'kecamatan_id' => 1,
            'desa_dinas_id' => 1,
            'nama_desa_adat' => 'Desa Adat Test',
            'deskripsi_umum' => 'Test description',
            'status_verifikasi' => 'disetujui',
            'pelapor_nama' => 'Test User',
            'pelapor_nik' => '1234567890123456',
            'pelapor_whatsapp' => '08123456789',
        ]);
    }

    public function test_laporan_relation(): void
    {
        $riwayat = OpkRiwayatStatus::create([
            'laporan_id' => $this->laporan->id,
            'status_lama' => 'menunggu',
            'status_baru' => 'review_dinas',
            'user_id' => $this->user->id,
            'catatan' => 'AI selesai',
        ]);

        $this->assertEquals($this->laporan->id, $riwayat->laporan->id);
    }

    public function test_user_relation(): void
    {
        $riwayat = OpkRiwayatStatus::create([
            'laporan_id' => $this->laporan->id,
            'status_lama' => 'review_dinas',
            'status_baru' => 'disetujui',
            'user_id' => $this->user->id,
            'catatan' => 'Disetujui',
        ]);

        $this->assertEquals($this->user->id, $riwayat->user->id);
    }

    public function test_catatan_is_nullable(): void
    {
        $riwayat = OpkRiwayatStatus::create([
            'laporan_id' => $this->laporan->id,
            'status_lama' => 'menunggu',
            'status_baru' => 'ai_review',
            'user_id' => null,
        ]);

        $this->assertNull($riwayat->catatan);
    }
}
