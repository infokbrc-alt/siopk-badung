<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\{OpkLaporan, OpkCategory, Kecamatan};
use App\Helpers\CacheKeys;
use App\Services\{OpkStatsService, PetaDataService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardPublikController extends Controller
{
    public function index()
    {
        $data = Cache::remember(CacheKeys::PUBLIK_DASHBOARD, 120, function () {
            $stats = app(OpkStatsService::class)->dashboardPublik();

            $kategori   = OpkCategory::withCount([
                'laporans as total' => fn($q) => $q->where('status_verifikasi', 'disetujui')
            ])->orderByDesc('total')->get();

            $kecamatans = Kecamatan::withCount([
                'laporans as total' => fn($q) => $q->where('status_verifikasi', 'disetujui')
            ])->orderByDesc('total')->get();

            $terbaru = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
                ->where('status_verifikasi', 'disetujui')
                ->latest()
                ->limit(6)
                ->get();

            return compact('stats', 'kategori', 'kecamatans', 'terbaru');
        });

        return view('publik.dashboard', $data);
    }

    public function petaJson(Request $request)
    {
        return response()->json(
            app(PetaDataService::class)->getPetaData($request)
        );
    }

    // Detail OPK publik
    public function showOpk(OpkLaporan $opk)
    {
        if ($opk->status_verifikasi !== 'disetujui') {
            abort(404);
        }
        $opk->load(['kategori', 'kecamatan', 'desaDinas', 'fotoUtama', 'fotos', 'videos']);
        return view('publik.opk-detail', compact('opk'));
    }
}
