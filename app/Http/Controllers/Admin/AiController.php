<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\CacheKeys;
use App\Jobs\AnalisisOpkJob;
use App\Models\OpkLaporan;
use App\Services\{AiOpkAnalyzer, OpkStatsService};
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
        $ringkasan = Cache::remember(CacheKeys::RINGKASAN_EKSEKUTIF, 21600, function () {
            $stats = app(OpkStatsService::class)->ringkasanEksekutif();
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
        Cache::forget(CacheKeys::RINGKASAN_EKSEKUTIF);
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
