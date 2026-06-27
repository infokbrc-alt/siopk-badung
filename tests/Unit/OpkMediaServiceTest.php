<?php

namespace Tests\Unit;

use App\Models\DesaDinas;
use App\Models\Kecamatan;
use App\Models\OpkCategory;
use App\Models\OpkFoto;
use App\Models\OpkLaporan;
use App\Services\OpkMediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OpkMediaServiceTest extends TestCase
{
    use RefreshDatabase;

    private OpkMediaService $service;

    private OpkLaporan $laporan;

    protected function setUp(): void
    {
        parent::setUp();

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
            'status_verifikasi' => 'disetujui',
            'pelapor_nama' => 'Test User',
            'pelapor_nik' => '1234567890123456',
            'pelapor_whatsapp' => '08123456789',
        ]);

        $this->service = new OpkMediaService;
    }

    public function test_delete_fotos_removes_specified_photos(): void
    {
        $foto1 = OpkFoto::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'foto1.jpg',
            'path' => 'foto_opk/1/foto1.jpg',
            'is_utama' => true,
            'urutan' => 0,
        ]);
        $foto2 = OpkFoto::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'foto2.jpg',
            'path' => 'foto_opk/1/foto2.jpg',
            'urutan' => 1,
        ]);

        $this->service->deleteFotos($this->laporan->id, [$foto1->id]);

        $this->assertCount(1, $this->laporan->fresh()->fotos);
        $this->assertEquals($foto2->id, $this->laporan->fresh()->fotos->first()->id);
    }

    public function test_upload_fotos_adds_new_photos(): void
    {
        Storage::fake('public');

        $files = [
            UploadedFile::fake()->image('new_foto.jpg', 800, 600),
        ];

        $this->service->uploadFotos($this->laporan, $files, 0);

        $this->assertCount(1, $this->laporan->fresh()->fotos);
    }

    public function test_set_foto_utama_changes_primary_photo(): void
    {
        $foto1 = OpkFoto::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'foto1.jpg',
            'path' => 'foto_opk/1/foto1.jpg',
            'is_utama' => true,
            'urutan' => 0,
        ]);
        $foto2 = OpkFoto::create([
            'laporan_id' => $this->laporan->id,
            'nama_file' => 'foto2.jpg',
            'path' => 'foto_opk/1/foto2.jpg',
            'is_utama' => false,
            'urutan' => 1,
        ]);

        $this->service->setFotoUtama($this->laporan->id, $foto2->id);

        $this->assertFalse(OpkFoto::find($foto1->id)->is_utama);
        $this->assertTrue(OpkFoto::find($foto2->id)->is_utama);
    }
}
