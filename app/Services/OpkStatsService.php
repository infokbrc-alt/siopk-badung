<?php

namespace App\Services;

use App\Models\OpkLaporan;
use Illuminate\Support\Facades\DB;

class OpkStatsService
{
    public function dashboardAdmin(): array
    {
        return [
            'total_opk'     => $this->countDisetujui(),
            'kritis'        => $this->countDisetujui('kritis'),
            'menunggu'      => $this->countByStatus(['menunggu', 'review_dinas']),
            'terlindungi'   => $this->countDisetujui('baik'),
            'bulan_ini'     => OpkLaporan::whereMonth('created_at', now()->month)
                                         ->whereYear('created_at', now()->year)->count(),
        ];
    }

    public function dashboardPublik(): array
    {
        $row = OpkLaporan::selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN kondisi = 'kritis'  THEN 1 ELSE 0 END) as kritis,
                SUM(CASE WHEN kondisi = 'waspada' THEN 1 ELSE 0 END) as waspada,
                SUM(CASE WHEN kondisi = 'baik'    THEN 1 ELSE 0 END) as baik
            ")
            ->where('status_verifikasi', 'disetujui')
            ->first();

        return [
            'total'   => (int) ($row->total ?? 0),
            'kritis'  => (int) ($row->kritis ?? 0),
            'waspada' => (int) ($row->waspada ?? 0),
            'baik'    => (int) ($row->baik ?? 0),
        ];
    }

    public function laporanAdmin(): array
    {
        return [
            'total'     => $this->countDisetujui(),
            'kritis'    => $this->countDisetujui('kritis'),
            'waspada'   => $this->countDisetujui('waspada'),
            'baik'      => $this->countDisetujui('baik'),
            'disetujui' => $this->countDisetujui(),
            'ditolak'   => OpkLaporan::where('status_verifikasi', 'ditolak')->count(),
            'menunggu'  => $this->countByStatus(['menunggu', 'review_dinas']),
            'bulan_ini' => OpkLaporan::whereMonth('created_at', now()->month)
                                     ->whereYear('created_at', now()->year)->count(),
        ];
    }

    public function ringkasanEksekutif(): array
    {
        $kritis = OpkLaporan::where('status_verifikasi', 'disetujui')
            ->where('kondisi', 'kritis')
            ->with('kecamatan')
            ->orderByDesc('ai_urgency_score')
            ->limit(5)
            ->get()
            ->map(fn($o) => "- {$o->nama_opk} (Kec. {$o->kecamatan?->nama}, score: " . number_format($o->ai_urgency_score ?? 0, 1) . ")")
            ->implode("\n");

        return [
            'total_opk'       => $this->countDisetujui(),
            'laporan_baru'    => OpkLaporan::whereDate('created_at', '>=', now()->subDays(7))->count(),
            'kritis'          => $this->countDisetujui('kritis'),
            'waspada'         => $this->countDisetujui('waspada'),
            'disetujui'       => OpkLaporan::where('status_verifikasi', 'disetujui')->whereDate('updated_at', '>=', now()->subDays(7))->count(),
            'ditolak'         => OpkLaporan::where('status_verifikasi', 'ditolak')->whereDate('updated_at', '>=', now()->subDays(7))->count(),
            'menunggu'        => $this->countByStatus(['menunggu', 'review_dinas']),
            'prioritas_tinggi'=> OpkLaporan::where('status_verifikasi', 'disetujui')->where('ai_urgency_score', '>=', 7)->count(),
            'opk_kritis_list' => $kritis ?: '(Tidak ada OPK kritis)',
        ];
    }

    public function countDisetujui(?string $kondisi = null): int
    {
        $query = OpkLaporan::where('status_verifikasi', 'disetujui');

        if ($kondisi) {
            $query->where('kondisi', $kondisi);
        }

        return $query->count();
    }

    public function countByStatus(array $statuses): int
    {
        return OpkLaporan::whereIn('status_verifikasi', $statuses)->count();
    }
}
