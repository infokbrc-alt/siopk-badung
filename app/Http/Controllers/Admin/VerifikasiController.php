<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\LaporanVerified;
use App\Models\{OpkLaporan, OpkRiwayatStatus};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerifikasiController extends Controller
{
    public function index(Request $request)
    {
        $query = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
            ->whereIn('status_verifikasi', ['menunggu', 'ai_review', 'review_dinas']);

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

    public function show(OpkLaporan $laporan)
    {
        $laporan->load([
            'kategori', 'kecamatan', 'desaDinas',
            'fotos', 'dokumens', 'videos', 'riwayat.user'
        ]);
        return view('admin.verifikasi.show', compact('laporan'));
    }

    public function setujui(Request $request, OpkLaporan $laporan)
    {
        $request->validate(['catatan' => 'nullable|string|max:500']);
        return $this->updateVerificationStatus($laporan, 'disetujui', $request->catatan);
    }

    public function tolak(Request $request, OpkLaporan $laporan)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
            'alasan'  => 'required|in:tidak_valid,duplikat,kurang_data,diluar_wilayah,lainnya',
        ]);

        $statusBaru = $request->alasan === 'duplikat' ? 'duplikat' : 'ditolak';
        $catatan    = $request->alasan === 'duplikat'
            ? "[duplikat] " . $request->catatan
            : "[{$request->alasan}] " . $request->catatan;

        return $this->updateVerificationStatus($laporan, $statusBaru, $catatan);
    }

    private function updateVerificationStatus(OpkLaporan $laporan, string $statusBaru, ?string $catatan = null)
    {
        DB::beginTransaction();
        try {
            $statusLama = $laporan->status_verifikasi;

            $laporan->update([
                'status_verifikasi'  => $statusBaru,
                'diverifikasi_oleh'  => auth()->id(),
                'tanggal_verifikasi' => now(),
                'catatan_verifikasi' => $catatan,
            ]);

            OpkRiwayatStatus::create([
                'laporan_id'  => $laporan->id,
                'status_lama' => $statusLama,
                'status_baru' => $statusBaru,
                'user_id'     => auth()->id(),
                'catatan'     => $catatan ?? ($statusBaru === 'disetujui' ? 'Disetujui oleh verifikator.' : 'Ditolak oleh verifikator.'),
            ]);

            DB::commit();

            LaporanVerified::dispatch($laporan, $statusBaru);

            return redirect()->route('admin.verifikasi.index')
                ->with('success', "Laporan {$laporan->kode_laporan} berhasil {$statusBaru}.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal memverifikasi laporan', ['laporan_id' => $laporan->id, 'error' => $e->getMessage()]);
            return back()->with('error', 'Gagal memverifikasi laporan. Silakan coba lagi.');
        }
    }

    // Update AI score manual (superadmin/admin)
    public function updateAiScore(Request $request, OpkLaporan $laporan)
    {
        $request->validate([
            'ai_urgency_score'  => 'required|numeric|min:0|max:10',
            'ai_rekomendasi'    => 'nullable|string',
        ]);

        // FIX: jangan ubah status_verifikasi — update AI score saja
        $laporan->update([
            'ai_urgency_score' => $request->ai_urgency_score,
            'ai_rekomendasi'   => $request->ai_rekomendasi,
        ]);

        return back()->with('success', 'AI score diperbarui.');
    }
}
