<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{OpkLaporan, OpkCategory, Kecamatan};
use App\Services\OpkStatsService;
use Illuminate\Support\Facades\DB;

class LaporanAdminController extends Controller
{
    public function index()
    {
        $stats = app(OpkStatsService::class)->laporanAdmin();

        // Per kategori
        $perKategori = OpkCategory::withCount([
            'laporans as total'   => fn($q) => $q->where('status_verifikasi', 'disetujui'),
            'laporans as kritis'  => fn($q) => $q->where('status_verifikasi', 'disetujui')->where('kondisi', 'kritis'),
            'laporans as waspada' => fn($q) => $q->where('status_verifikasi', 'disetujui')->where('kondisi', 'waspada'),
        ])->orderByDesc('total')->get();

        // Per kecamatan
        $perKecamatan = Kecamatan::withCount([
            'laporans as total'   => fn($q) => $q->where('status_verifikasi', 'disetujui'),
            'laporans as kritis'  => fn($q) => $q->where('status_verifikasi', 'disetujui')->where('kondisi', 'kritis'),
        ])->orderByDesc('total')->get();

        // Tren laporan per bulan (6 bulan terakhir)
        $tren = OpkLaporan::select(
                DB::raw('MONTH(created_at) as bulan'),
                DB::raw('YEAR(created_at) as tahun'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')->orderBy('bulan')
            ->get()
            ->map(fn($r) => [
                'label' => \Carbon\Carbon::createFromDate($r->tahun, $r->bulan, 1)->isoFormat('MMM Y'),
                'total' => $r->total,
            ]);

        // Top 10 OPK urgensi tertinggi
        $topUrgensi = OpkLaporan::with(['kategori', 'kecamatan'])
            ->where('status_verifikasi', 'disetujui')
            ->whereNotNull('ai_urgency_score')
            ->orderByDesc('ai_urgency_score')
            ->limit(10)
            ->get();

        return view('admin.laporan.index', compact(
            'stats', 'perKategori', 'perKecamatan', 'tren', 'topUrgensi'
        ));
    }

    // Export CSV — streaming dengan cursor
    public function exportCsv()
    {
        $filename = 'opk-badung-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');
            // BOM untuk Excel
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, [
                'Kode', 'Nama OPK', 'Jenis OPK', 'Kondisi',
                'Kecamatan', 'Desa Dinas', 'Desa Adat',
                'Latitude', 'Longitude',
                'Status Pelindungan', 'AI Score',
                'Tanggal Lapor',
            ], ';');

            OpkLaporan::with(['kategori', 'kecamatan', 'desaDinas'])
                ->where('status_verifikasi', 'disetujui')
                ->cursor()
                ->each(function ($o) use ($out) {
                    fputcsv($out, [
                        $o->kode_laporan,
                        $o->nama_opk,
                        $o->kategori?->nama,
                        $o->kondisi,
                        $o->kecamatan?->nama,
                        $o->desaDinas?->nama,
                        $o->nama_desa_adat,
                        $o->latitude,
                        $o->longitude,
                        $o->status_pelindungan,
                        $o->ai_urgency_score,
                        $o->created_at->format('Y-m-d'),
                    ], ';');
                });

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
