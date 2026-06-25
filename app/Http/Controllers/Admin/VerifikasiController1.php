<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{OpkLaporan, OpkRiwayatStatus};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifikasiController extends Controller
{
    // Daftar antrian verifikasi
    public function index(Request $request)
    {
        $query = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
            ->whereIn('status_verifikasi', ['menunggu', 'ai_review', 'review_dinas']);

        // Filter
        if ($request->filled('kondisi')) {
            $query->where('kondisi', $request->kondisi);
        }
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        $laporans = $query->orderByDesc('ai_urgency_score')
                          ->orderBy('created_at')
                          ->paginate(15);

        return view('admin.verifikasi.index', compact('laporans'));
    }

    // Detail satu laporan
    public function show(OpkLaporan $laporan)
    {
        $laporan->load([
            'kategori', 'kecamatan', 'desaDinas',
            'fotos', 'dokumens', 'videos', 'riwayat.user'
        ]);
        return view('admin.verifikasi.show', compact('laporan'));
    }

    // Setujui laporan → masuk peta resmi
    public function setujui(Request $request, OpkLaporan $laporan)
    {
        $request->validate([
            'catatan' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $statusLama = $laporan->status_verifikasi;

            $laporan->update([
                'status_verifikasi'  => 'disetujui',
                'diverifikasi_oleh'  => auth()->id(),
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $request->catatan,
            ]);

            OpkRiwayatStatus::create([
                'laporan_id'  => $laporan->id,
                'status_lama' => $statusLama,
                'status_baru' => 'disetujui',
                'user_id'     => auth()->id(),
                'catatan'     => $request->catatan ?? 'Disetujui oleh verifikator.',
            ]);

            DB::commit();
            return redirect()->route('admin.verifikasi.index')
                             ->with('success', "Laporan {$laporan->kode_laporan} berhasil disetujui dan masuk peta OPK.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyetujui: ' . $e->getMessage());
        }
    }

    // Tolak laporan
    public function tolak(Request $request, OpkLaporan $laporan)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
            'alasan'  => 'required|in:tidak_valid,duplikat,kurang_data,diluar_wilayah,lainnya',
        ]);

        DB::beginTransaction();
        try {
            $statusLama = $laporan->status_verifikasi;
            $statusBaru = $request->alasan === 'duplikat' ? 'duplikat' : 'ditolak';

            $laporan->update([
                'status_verifikasi'  => $statusBaru,
                'diverifikasi_oleh'  => auth()->id(),
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $request->catatan,
            ]);

            OpkRiwayatStatus::create([
                'laporan_id'  => $laporan->id,
                'status_lama' => $statusLama,
                'status_baru' => $statusBaru,
                'user_id'     => auth()->id(),
                'catatan'     => "[{$request->alasan}] " . $request->catatan,
            ]);

            DB::commit();
            return redirect()->route('admin.verifikasi.index')
                             ->with('success', "Laporan {$laporan->kode_laporan} ditolak.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menolak: ' . $e->getMessage());
        }
    }

    // Update AI score manual (superadmin/admin)
    public function updateAiScore(Request $request, OpkLaporan $laporan)
    {
        $request->validate([
            'ai_urgency_score'  => 'required|numeric|min:0|max:10',
            'ai_rekomendasi'    => 'nullable|string',
        ]);

        $laporan->update([
            'ai_urgency_score' => $request->ai_urgency_score,
            'ai_rekomendasi'   => $request->ai_rekomendasi,
            'status_verifikasi'=> 'review_dinas',
        ]);

        return back()->with('success', 'AI score diperbarui.');
    }
}
