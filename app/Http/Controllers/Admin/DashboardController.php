<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\CacheKeys;
use App\Models\{OpkLaporan, OpkCategory, Kecamatan};
use App\Services\OpkStatsService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $cacheKey = CacheKeys::adminDashboard(auth()->id());

        $data = Cache::remember($cacheKey, 60, function () {
            $stats = app(OpkStatsService::class)->dashboardAdmin();

            $perKategori = OpkCategory::withCount([
                'laporans as total' => fn($q) => $q->where('status_verifikasi', 'disetujui')
            ])->orderByDesc('total')->get();

            $perKecamatan = Kecamatan::withCount([
                'laporans as total' => fn($q) => $q->where('status_verifikasi', 'disetujui')
            ])->orderByDesc('total')->get();

            $prioritas = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
                ->where('status_verifikasi', 'disetujui')
                ->whereIn('kondisi', ['kritis', 'waspada'])
                ->orderByDesc('ai_urgency_score')
                ->orderBy('kondisi')
                ->limit(10)
                ->get();

            $antrian = OpkLaporan::with(['kategori', 'kecamatan'])
                ->whereIn('status_verifikasi', ['menunggu', 'review_dinas'])
                ->latest()
                ->limit(7)
                ->get();

            $petaData = OpkLaporan::select([
                    'id', 'kode_laporan', 'nama_opk', 'kondisi',
                    'latitude', 'longitude', 'kategori_id', 'kecamatan_id'
                ])
                ->with(['kategori:id,nama,ikon', 'kecamatan:id,nama'])
                ->where('status_verifikasi', 'disetujui')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();

            return compact(
                'stats', 'perKategori', 'perKecamatan',
                'prioritas', 'antrian', 'petaData'
            );
        });

        return view('admin.dashboard.index', $data);
    }
}
