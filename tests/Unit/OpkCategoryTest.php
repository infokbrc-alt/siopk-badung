<?php

namespace Tests\Unit;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\OpkCategory;
use App\Models\OpkLaporan;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpkCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        OpkCategory::create(['nomor' => 1, 'nama' => 'Seni', 'ikon' => '🎨']);
        $kecamatan = Kecamatan::create(['nama' => 'Kuta', 'kode' => 'KT']);
        DesaDinas::create(['nama' => 'Kuta', 'kecamatan_id' => $kecamatan->id]);
    }

    public function test_laporans_relation(): void
    {
        $category = OpkCategory::first();

        OpkLaporan::create([
            'kode_laporan' => 'SIOPK-2025-00001',
            'nama_opk' => 'Tari Legong',
            'kategori_id' => $category->id,
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

        $this->assertCount(1, $category->laporans);
        $this->assertEquals('Tari Legong', $category->laporans->first()->nama_opk);
    }

    public function test_nomor_is_unique(): void
    {
        $this->expectException(QueryException::class);
        OpkCategory::create(['nomor' => 1, 'nama' => 'Duplikat', 'ikon' => '📦']);
    }
}
