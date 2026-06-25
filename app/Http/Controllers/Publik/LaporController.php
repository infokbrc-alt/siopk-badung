<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLaporanRequest;
use App\Jobs\AnalisisOpkJob;
use App\Events\LaporanCreated;
use App\Models\{OpkCategory, Kecamatan, DesaDinas, DesaAdat, OpkLaporan, OpkFoto, OpkDokumen, OpkVideo};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LaporController extends Controller
{
    // Halaman form laporan publik
    public function index()
    {
        $kategori   = Cache::remember('kategori_list', 86400, fn() => OpkCategory::orderBy('nomor')->get());
        $kecamatans = Cache::remember('kecamatan_list', 86400, fn() => Kecamatan::orderBy('nama')->get());
        return view('publik.lapor', compact('kategori', 'kecamatans'));
    }

    // API: ambil desa dinas berdasarkan kecamatan (AJAX)
    public function getDesaDinas(Request $request)
    {
        $request->validate(['kecamatan_id' => 'required|exists:kecamatans,id']);
        $desa = DesaDinas::where('kecamatan_id', $request->kecamatan_id)
                         ->orderBy('nama')
                         ->get(['id', 'nama']);
        return response()->json($desa);
    }

    // API: ambil desa adat berdasarkan kecamatan (AJAX)
    public function getDesaAdat(Request $request)
    {
        $request->validate(['kecamatan_id' => 'required|exists:kecamatans,id']);
        $desa = DesaAdat::where('kecamatan_id', $request->kecamatan_id)
                        ->orderBy('nama')
                        ->get(['id', 'nama']);
        return response()->json($desa);
    }

    // Simpan laporan baru
    public function store(StoreLaporanRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            // Buat laporan
            $laporan = OpkLaporan::create([
                'kode_laporan'        => OpkLaporan::generateKode(),
                'nama_opk'            => $validated['nama_opk'],
                'kategori_id'         => $validated['kategori_id'],
                'tahun_diketahui'     => $validated['tahun_diketahui'] ?? null,
                'tahun_keterangan'    => $validated['tahun_keterangan'] ?? null,
                'status_pelindungan'  => $validated['status_pelindungan'],
                'kondisi'             => $validated['kondisi'],
                'kecamatan_id'        => $validated['kecamatan_id'],
                'desa_dinas_id'       => $validated['desa_dinas_id'],
                'nama_desa_adat'      => $validated['nama_desa_adat'],
                'banjar_adat'         => $validated['banjar_adat'] ?? null,
                'lokasi_spesifik'     => $validated['lokasi_spesifik'] ?? null,
                'latitude'            => $validated['latitude'] ?? null,
                'longitude'           => $validated['longitude'] ?? null,
                'deskripsi_umum'      => $validated['deskripsi_umum'],
                'sejarah_asal_usul'   => $validated['sejarah_asal_usul'] ?? null,
                'nilai_makna_budaya'  => $validated['nilai_makna_budaya'] ?? null,
                'bahasa_digunakan'    => $validated['bahasa_digunakan'] ?? null,
                'aksara_digunakan'    => $validated['aksara_digunakan'] ?? null,
                'frekuensi_pelaksanaan' => $validated['frekuensi_pelaksanaan'] ?? null,
                'status_kepemilikan'  => $validated['status_kepemilikan'] ?? null,
                'praktisi_nama'       => $validated['praktisi_nama'] ?? null,
                'praktisi_usia'       => $validated['praktisi_usia'] ?? null,
                'praktisi_kontak'     => $validated['praktisi_kontak'] ?? null,
                'tipe_pelapor'        => $validated['tipe_pelapor'],
                'pelapor_nama'        => $validated['pelapor_nama'],
                'pelapor_nik'         => $validated['pelapor_nik'],
                'pelapor_whatsapp'    => $validated['pelapor_whatsapp'],
                'pelapor_email'       => $validated['pelapor_email'] ?? null,
                'link_video'          => $validated['link_video'] ?? null,
                'status_verifikasi'   => 'menunggu',
            ]);

            // Upload multi foto
            if ($request->hasFile('fotos')) {
                foreach ($request->file('fotos') as $index => $foto) {
                    $namaFile = Str::uuid() . '.' . $foto->getClientOriginalExtension();
                    $path     = $foto->storeAs('foto_opk/' . $laporan->id, $namaFile, 'public');

                    OpkFoto::create([
                        'laporan_id'   => $laporan->id,
                        'nama_file'    => $foto->getClientOriginalName(),
                        'path'         => $path,
                        'keterangan'   => $index === 0 ? ($validated['keterangan_foto_utama'] ?? null) : null,
                        'is_utama'     => $index === 0,
                        'urutan'       => $index,
                        'ukuran_bytes' => $foto->getSize(),
                        'mime_type'    => $foto->getMimeType(),
                    ]);
                }
            }

            // Upload dokumen
            if ($request->hasFile('dokumen')) {
                $dok      = $request->file('dokumen');
                $namaDok  = Str::uuid() . '.' . $dok->getClientOriginalExtension();
                $pathDok  = $dok->storeAs('dokumen_opk/' . $laporan->id, $namaDok, 'public');

                OpkDokumen::create([
                    'laporan_id'   => $laporan->id,
                    'nama_file'    => $dok->getClientOriginalName(),
                    'path'         => $pathDok,
                    'jenis'        => 'dokumen_pendukung',
                    'ukuran_bytes' => $dok->getSize(),
                ]);
            }

            // Simpan link video jika ada
            if (!empty($validated['link_video'])) {
                OpkVideo::create([
                    'laporan_id'     => $laporan->id,
                    'link_eksternal' => $validated['link_video'],
                ]);
            }

            DB::commit();

            // Dispatch job AI untuk analisis background
            AnalisisOpkJob::dispatch($laporan->id);

            LaporanCreated::dispatch($laporan);

            return redirect()->route('publik.lapor.sukses', ['kode' => $laporan->kode_laporan])
                             ->with('success', 'Laporan berhasil dikirim!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal menyimpan laporan OPK', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat menyimpan laporan. Silakan coba lagi.')->withInput();
        }
    }

    // Halaman sukses setelah kirim laporan
    public function sukses(Request $request)
    {
        $kode    = $request->kode;
        $laporan = OpkLaporan::with(['kategori', 'kecamatan'])->where('kode_laporan', $kode)->firstOrFail();
        return view('publik.lapor-sukses', compact('laporan'));
    }

    // Cek status laporan oleh pelapor
    public function cekStatus(Request $request)
    {
        $kode    = $request->kode_laporan;
        $laporan = null;
        if ($kode) {
            $laporan = OpkLaporan::with(['kategori', 'kecamatan', 'desaDinas', 'riwayat.user'])
                ->where('kode_laporan', $kode)->first();
        }
        return view('publik.cek-status', compact('laporan', 'kode'));
    }
}
