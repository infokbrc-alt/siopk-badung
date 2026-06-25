<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AnalisisOpkJob;
use App\Models\OpkLaporan;
use App\Services\AiOpkAnalyzer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AiController extends Controller
{
    public function __construct(
        private readonly AiOpkAnalyzer $ai
    ) {}

    // ─────────────────────────────────────────────
    //  1. Chat asisten per-laporan (AJAX)
    // ─────────────────────────────────────────────
    public function chat(Request $request, OpkLaporan $laporan)
    {
        $request->validate([
            'pertanyaan' => 'required|string|max:500',
        ]);

        $jawaban = $this->ai->chatAsisten(
            $request->pertanyaan,
            $laporan->load(['kategori', 'kecamatan'])
        );

        return response()->json([
            'success' => true,
            'jawaban' => $jawaban,
        ]);
    }

    // ─────────────────────────────────────────────
    //  2. Re-analisis manual (admin trigger ulang AI)
    // ─────────────────────────────────────────────
    public function reAnalisis(OpkLaporan $laporan)
    {
        // Reset status dulu
        $laporan->update([
            'status_verifikasi' => 'ai_review',
            'ai_urgency_score'  => null,
            'ai_rekomendasi'    => null,
            'ai_duplikat_score' => null,
        ]);

        // Dispatch ulang job
        AnalisisOpkJob::dispatch($laporan->id);

        return back()->with('success', "AI sedang menganalisis ulang laporan {$laporan->kode_laporan}. Refresh halaman dalam beberapa detik.");
    }

    // ─────────────────────────────────────────────
    //  3. Ringkasan eksekutif mingguan (AJAX + Cache)
    // ─────────────────────────────────────────────
    public function ringkasanEksekutif()
    {
        // Cache 6 jam agar tidak spam API
        $ringkasan = Cache::remember('siopk_ringkasan_eksekutif', 21600, function () {

            $kritis = OpkLaporan::where('status_verifikasi', 'disetujui')
                ->where('kondisi', 'kritis')
                ->with('kecamatan')
                ->orderByDesc('ai_urgency_score')
                ->limit(5)
                ->get()
                ->map(fn($o) => "- {$o->nama_opk} (Kec. {$o->kecamatan?->nama}, score: " . number_format($o->ai_urgency_score ?? 0, 1) . ")")
                ->implode("\n");

            $stats = [
                'total_opk'       => OpkLaporan::where('status_verifikasi', 'disetujui')->count(),
                'laporan_baru'    => OpkLaporan::whereDate('created_at', '>=', now()->subDays(7))->count(),
                'kritis'          => OpkLaporan::where('status_verifikasi', 'disetujui')->where('kondisi', 'kritis')->count(),
                'waspada'         => OpkLaporan::where('status_verifikasi', 'disetujui')->where('kondisi', 'waspada')->count(),
                'disetujui'       => OpkLaporan::where('status_verifikasi', 'disetujui')->whereDate('updated_at', '>=', now()->subDays(7))->count(),
                'ditolak'         => OpkLaporan::where('status_verifikasi', 'ditolak')->whereDate('updated_at', '>=', now()->subDays(7))->count(),
                'menunggu'        => OpkLaporan::whereIn('status_verifikasi', ['menunggu', 'review_dinas'])->count(),
                'prioritas_tinggi'=> OpkLaporan::where('status_verifikasi', 'disetujui')->where('ai_urgency_score', '>=', 7)->count(),
                'opk_kritis_list' => $kritis ?: '(Tidak ada OPK kritis)',
            ];

            return $this->ai->ringkasanEksekutif($stats);
        });

        return response()->json([
            'success'   => true,
            'ringkasan' => $ringkasan,
            'cached_at' => now()->isoFormat('D MMM Y, HH:mm'),
        ]);
    }

    // ─────────────────────────────────────────────
    //  4. Hapus cache ringkasan (admin force refresh)
    // ─────────────────────────────────────────────
    public function clearRingkasanCache()
    {
        Cache::forget('siopk_ringkasan_eksekutif');
        return back()->with('success', 'Cache ringkasan eksekutif dihapus. AI akan generate ulang saat diminta.');
    }

    // ─────────────────────────────────────────────
    //  5. Auto-klasifikasi OPK dari deskripsi (AJAX)
    // ─────────────────────────────────────────────
    public function klasifikasi(Request $request)
    {
        $request->validate([
            'nama_opk'   => 'required|string|max:200',
            'deskripsi'  => 'required|string|min:20',
        ]);

        $nomorKategori = $this->ai->klasifikasiOtomatis(
            $request->nama_opk,
            $request->deskripsi
        );

        return response()->json([
            'success'         => true,
            'nomor_kategori'  => $nomorKategori,
        ]);
    }
}
