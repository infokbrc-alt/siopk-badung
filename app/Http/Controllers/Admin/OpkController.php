<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{OpkLaporan, OpkCategory, Kecamatan};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OpkController extends Controller
{
    public function index(Request $request)
    {
        $query = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
            ->where('status_verifikasi', 'disetujui');

        if ($request->filled('search')) {
            $query->where('nama_opk', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }
        if ($request->filled('kecamatan_id')) {
            $query->where('kecamatan_id', $request->kecamatan_id);
        }
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }

        $laporans   = $query->latest()->paginate(20)->withQueryString();
        $kategori   = OpkCategory::orderBy('nomor')->get();
        $kecamatans = Kecamatan::orderBy('nama')->get();

        return view('admin.opk.index', compact('laporans', 'kategori', 'kecamatans'));
    }

    public function show(OpkLaporan $laporan)
    {
        $laporan->load(['kategori', 'kecamatan', 'desaDinas', 'fotos', 'dokumens', 'videos', 'riwayat.user', 'verifikator']);
        return view('admin.opk.show', compact('laporan'));
    }

    public function edit(OpkLaporan $laporan)
    {
        $kategori   = OpkCategory::orderBy('nomor')->get();
        $kecamatans = Kecamatan::with('desaDinas')->orderBy('nama')->get();
        return view('admin.opk.edit', compact('laporan', 'kategori', 'kecamatans'));
    }

    public function update(Request $request, OpkLaporan $laporan)
    {
        $validated = $request->validate([
            'nama_opk'           => 'required|string|max:200',
            'kondisi'            => 'required|in:baik,waspada,kritis',
            'status_pelindungan' => 'required|string',
            'deskripsi_umum'     => 'required|string|min:10',
            'sejarah_asal_usul'  => 'nullable|string',
            'nilai_makna_budaya' => 'nullable|string',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
        ]);

        // FIX: hanya update field yang relevan, jangan sentuh status_verifikasi
        $laporan->nama_opk           = $validated['nama_opk'];
        $laporan->kondisi            = $validated['kondisi'];
        $laporan->status_pelindungan = $validated['status_pelindungan'];
        $laporan->deskripsi_umum     = $validated['deskripsi_umum'];
        $laporan->sejarah_asal_usul  = $validated['sejarah_asal_usul'] ?? $laporan->sejarah_asal_usul;
        $laporan->nilai_makna_budaya = $validated['nilai_makna_budaya'] ?? $laporan->nilai_makna_budaya;
        $laporan->latitude           = $validated['latitude'] ?? $laporan->latitude;
        $laporan->longitude          = $validated['longitude'] ?? $laporan->longitude;
        $laporan->save();

        return redirect()->route('admin.opk.show', $laporan)
                         ->with('success', 'Data OPK berhasil diperbarui.');
    }

    // Arsipkan (soft delete)
    public function destroy(OpkLaporan $laporan)
    {
        $laporan->delete(); // soft delete — data tetap di DB, tidak muncul di tampilan
        return redirect()->route('admin.opk.index')
                         ->with('success', 'OPK berhasil diarsipkan. Data masih tersimpan di database.');
    }

    // Restore dari arsip
    public function restore($id)
    {
        $laporan = OpkLaporan::withTrashed()->findOrFail($id);
        $laporan->restore();
        return redirect()->route('admin.opk.index')
                         ->with('success', 'OPK berhasil dipulihkan dari arsip.');
    }

    // Hapus permanen dari arsip (hanya yang sudah di-soft-delete)
    public function forceDelete($id)
    {
        $laporan = OpkLaporan::onlyTrashed()->findOrFail($id);

        // Hapus file-file terkait dari storage
        foreach ($laporan->fotos as $foto) {
            Storage::disk('public')->delete($foto->path);
        }
        foreach ($laporan->dokumens as $dok) {
            Storage::disk('public')->delete($dok->path);
        }

        $laporan->forceDelete();

        return redirect()->route('admin.opk.arsip')
                         ->with('success', 'OPK berhasil dihapus permanen dari database.');
    }

    // Data JSON untuk peta Leaflet
    public function petaJson(Request $request)
    {
        $query = OpkLaporan::select([
                'id', 'kode_laporan', 'nama_opk', 'kondisi',
                'latitude', 'longitude', 'status_pelindungan',
                'kategori_id', 'kecamatan_id',
                'nama_desa_adat', 'ai_urgency_score'
            ])
            ->with(['kategori:id,nama,ikon', 'kecamatan:id,nama', 'fotoUtama:laporan_id,path'])
            ->where('status_verifikasi', 'disetujui')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->filled('kondisi'))     $query->where('kondisi', $request->kondisi);
        if ($request->filled('kategori_id')) $query->where('kategori_id', $request->kategori_id);
        if ($request->filled('kecamatan_id')) $query->where('kecamatan_id', $request->kecamatan_id);

        $data = $query->get()->map(function ($opk) {
            return [
                'id'            => $opk->id,
                'kode'          => $opk->kode_laporan,
                'nama'          => $opk->nama_opk,
                'kondisi'       => $opk->kondisi,
                'lat'           => (float) $opk->latitude,
                'lng'           => (float) $opk->longitude,
                'kategori'      => $opk->kategori?->nama,
                'ikon_kategori' => $opk->kategori?->ikon,
                'kecamatan'     => $opk->kecamatan?->nama,
                'desa_adat'     => $opk->nama_desa_adat,
                'urgency_score' => $opk->ai_urgency_score,
                'foto_url'      => $opk->fotoUtama ? asset('storage/' . $opk->fotoUtama->path) : null,
                'detail_url'    => route('admin.opk.show', $opk->id),
            ];
        });

        return response()->json($data);
    }

    // Daftar OPK yang diarsipkan
    public function arsip(Request $request)
    {
        $laporans = OpkLaporan::onlyTrashed()
            ->with(['kategori', 'kecamatan'])
            ->latest('deleted_at')
            ->paginate(20);
        return view('admin.opk.arsip', compact('laporans'));
    }
}
