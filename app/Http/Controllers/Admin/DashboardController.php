<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{OpkLaporan, OpkCategory, Kecamatan, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // KPI Cards
        $stats = [
            'total_opk'     => OpkLaporan::where('status_verifikasi', 'disetujui')->count(),
            'kritis'         => OpkLaporan::where('status_verifikasi', 'disetujui')
                                           ->where('kondisi', 'kritis')->count(),
            'menunggu'       => OpkLaporan::whereIn('status_verifikasi', ['menunggu', 'review_dinas'])->count(),
            'terlindungi'    => OpkLaporan::where('status_verifikasi', 'disetujui')
                                           ->where('kondisi', 'baik')->count(),
            'bulan_ini'      => OpkLaporan::whereMonth('created_at', now()->month)
                                           ->whereYear('created_at', now()->year)->count(),
        ];

        // OPK per kategori (untuk chart)
        $perKategori = OpkCategory::withCount([
            'laporans as total' => fn($q) => $q->where('status_verifikasi', 'disetujui')
        ])->orderByDesc('total')->get();

        // OPK per kecamatan
        $perKecamatan = Kecamatan::withCount([
            'laporans as total' => fn($q) => $q->where('status_verifikasi', 'disetujui')
        ])->orderByDesc('total')->get();

        // Prioritas pemeliharaan (disetujui + kritis/waspada, urutkan ai_urgency_score)
        $prioritas = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
            ->where('status_verifikasi', 'disetujui')
            ->whereIn('kondisi', ['kritis', 'waspada'])
            ->orderByDesc('ai_urgency_score')
            ->orderBy('kondisi') // kritis dulu
            ->limit(10)
            ->get();

        // Laporan terbaru (menunggu verifikasi)
        $antrian = OpkLaporan::with(['kategori', 'kecamatan'])
            ->whereIn('status_verifikasi', ['menunggu', 'review_dinas'])
            ->latest()
            ->limit(7)
            ->get();

        // Data peta (semua yang disetujui, ada koordinat)
        $petaData = OpkLaporan::select([
                'id', 'kode_laporan', 'nama_opk', 'kondisi',
                'latitude', 'longitude', 'kategori_id', 'kecamatan_id'
            ])
            ->with(['kategori:id,nama,ikon', 'kecamatan:id,nama'])
            ->where('status_verifikasi', 'disetujui')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return view('admin.dashboard.index', compact(
            'stats', 'perKategori', 'perKecamatan',
            'prioritas', 'antrian', 'petaData'
        ));
    }
}
