<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Jobs\AnalisisOpkJob;
use App\Models\{OpkCategory, Kecamatan, DesaDinas, DesaAdat, OpkLaporan, OpkFoto, OpkDokumen, OpkVideo};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LaporController extends Controller
{
    // Halaman form laporan publik
    public function index()
    {
        $kategori   = OpkCategory::orderBy('nomor')->get();
        $kecamatans = Kecamatan::orderBy('nama')->get();
        return view('publik.lapor', compact('kategori', 'kecamatans'));
    }

    // API: ambil desa dinas berdasarkan kecamatan (AJAX)
    public function getDesaDinas(Request $request)
    {
        $kecamatanId = $request->kecamatan_id;
        $desa = DesaDinas::where('kecamatan_id', $kecamatanId)
                         ->orderBy('nama')
                         ->get(['id', 'nama']);
        return response()->json($desa);
    }

    // API: ambil desa adat berdasarkan kecamatan (AJAX)
    public function getDesaAdat(Request $request)
    {
        $kecamatanId = $request->kecamatan_id;
        $desa = DesaAdat::where('kecamatan_id', $kecamatanId)
                        ->orderBy('nama')
                        ->get(['id', 'nama']);
        return response()->json($desa);
    }

    // Simpan laporan baru
    public function store(Request $request)
    {
        // Validasi semua field
        $validated = $request->validate([
            // Step 1
            'nama_opk'            => 'required|string|max:200',
            'kategori_id'         => 'required|exists:opk_categories,id',
            'tahun_diketahui'     => 'nullable|integer|min:1|max:' . date('Y'),
            'tahun_keterangan'    => 'nullable|string|max:100',
            'status_pelindungan'  => 'required|in:belum_terdaftar,sudah_didata_dinas,sk_kabupaten,sk_provinsi,wbtb_nasional',
            'kondisi'             => 'required|in:baik,waspada,kritis',
            // Step 2
            'kecamatan_id'        => 'required|exists:kecamatans,id',
            'desa_dinas_id'       => 'required|exists:desa_dinas,id',
            'nama_desa_adat'      => 'required|string|max:150',
            'banjar_adat'         => 'nullable|string|max:150',
            'lokasi_spesifik'     => 'nullable|string|max:255',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            // Step 3
            'deskripsi_umum'      => 'required|string|min:50',
            'sejarah_asal_usul'   => 'nullable|string',
            'nilai_makna_budaya'  => 'nullable|string',
            'bahasa_digunakan'    => 'nullable|string|max:100',
            'aksara_digunakan'    => 'nullable|string|max:100',
            'frekuensi_pelaksanaan' => 'nullable|string',
            'status_kepemilikan'  => 'nullable|string',
            'praktisi_nama'       => 'nullable|string|max:150',
            'praktisi_usia'       => 'nullable|integer|min:1|max:120',
            'praktisi_kontak'     => 'nullable|string|max:50',
            // Step 4
            'fotos'               => 'nullable|array|max:10',
            'fotos.*'             => 'image|mimes:jpg,jpeg,png,heic|max:10240',
            'keterangan_foto_utama' => 'nullable|string|max:255',
            'link_video'          => 'nullable|url|max:500',
            'dokumen'             => 'nullable|file|mimes:pdf,doc,docx|max:20480',
            // Step 5
            'tipe_pelapor'        => 'required|in:masyarakat,tokoh_adat,petugas_dinas',
            'pelapor_nama'        => 'required|string|max:150',
            'pelapor_nik'         => 'required|string|size:16',
            'pelapor_whatsapp'    => 'required|string|max:20',
            'pelapor_email'       => 'nullable|email|max:150',
            'setuju_1'            => 'accepted',
            'setuju_2'            => 'accepted',
        ], $this->messages());

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

            return redirect()->route('publik.lapor.sukses', ['kode' => $laporan->kode_laporan])
                             ->with('success', 'Laporan berhasil dikirim!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    // Halaman sukses setelah kirim laporan
    public function sukses(Request $request)
    {
        $kode    = $request->kode;
        $laporan = OpkLaporan::where('kode_laporan', $kode)->firstOrFail();
        return view('publik.lapor-sukses', compact('laporan'));
    }

    // Cek status laporan oleh pelapor
    public function cekStatus(Request $request)
    {
        $kode    = $request->kode_laporan;
        $laporan = null;
        if ($kode) {
            $laporan = OpkLaporan::where('kode_laporan', $kode)->first();
        }
        return view('publik.cek-status', compact('laporan', 'kode'));
    }

    private function messages(): array
    {
        return [
            'nama_opk.required'       => 'Nama objek budaya wajib diisi.',
            'kategori_id.required'    => 'Jenis OPK wajib dipilih.',
            'kondisi.required'        => 'Kondisi OPK wajib dipilih.',
            'kecamatan_id.required'   => 'Kecamatan wajib dipilih.',
            'desa_dinas_id.required'  => 'Desa dinas wajib dipilih.',
            'nama_desa_adat.required' => 'Nama desa adat wajib diisi.',
            'deskripsi_umum.required' => 'Deskripsi umum wajib diisi.',
            'deskripsi_umum.min'      => 'Deskripsi minimal 50 karakter.',
            'fotos.*.image'           => 'File foto harus berupa gambar.',
            'fotos.*.max'             => 'Ukuran foto maksimal 10MB.',
            'fotos.max'               => 'Maksimal 10 foto yang dapat diupload.',
            'pelapor_nama.required'   => 'Nama pelapor wajib diisi.',
            'pelapor_nik.required'    => 'NIK wajib diisi.',
            'pelapor_nik.size'        => 'NIK harus 16 digit.',
            'pelapor_whatsapp.required' => 'Nomor WhatsApp wajib diisi.',
            'setuju_1.accepted'       => 'Anda harus menyetujui pernyataan pertama.',
            'setuju_2.accepted'       => 'Anda harus menyetujui pernyataan kedua.',
        ];
    }
}
