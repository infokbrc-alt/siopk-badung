<?php

namespace App\Http\Controllers\Publik;

use App\Http\Controllers\Controller;
use App\Models\{OpkLaporan, OpkCategory, Kecamatan};
use Illuminate\Http\Request;

class DashboardPublikController extends Controller
{
    public function index()
    {
        $stats = [
            'total'   => OpkLaporan::where('status_verifikasi', 'disetujui')->count(),
            'kritis'  => OpkLaporan::where('status_verifikasi', 'disetujui')->where('kondisi', 'kritis')->count(),
            'waspada' => OpkLaporan::where('status_verifikasi', 'disetujui')->where('kondisi', 'waspada')->count(),
            'baik'    => OpkLaporan::where('status_verifikasi', 'disetujui')->where('kondisi', 'baik')->count(),
        ];

        $kategori   = OpkCategory::withCount([
            'laporans as total' => fn($q) => $q->where('status_verifikasi', 'disetujui')
        ])->orderByDesc('total')->get();

        $kecamatans = Kecamatan::withCount([
            'laporans as total' => fn($q) => $q->where('status_verifikasi', 'disetujui')
        ])->orderByDesc('total')->get();

        // OPK terbaru (6 item untuk highlight)
        $terbaru = OpkLaporan::with(['kategori', 'kecamatan', 'fotoUtama'])
            ->where('status_verifikasi', 'disetujui')
            ->latest()
            ->limit(6)
            ->get();

        return view('publik.dashboard', compact('stats', 'kategori', 'kecamatans', 'terbaru'));
    }

    // API JSON peta publik (tanpa auth)
    public function petaJson(Request $request)
    {
        $query = OpkLaporan::select([
                'id', 'kode_laporan', 'nama_opk', 'kondisi',
                'latitude', 'longitude', 'kategori_id',
                'kecamatan_id', 'nama_desa_adat',
            ])
            ->with(['kategori:id,nama,ikon', 'kecamatan:id,nama', 'fotoUtama:laporan_id,path'])
            ->where('status_verifikasi', 'disetujui')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->filled('kondisi'))     $query->where('kondisi', $request->kondisi);
        if ($request->filled('kategori_id')) $query->where('kategori_id', $request->kategori_id);
        if ($request->filled('kecamatan_id')) $query->where('kecamatan_id', $request->kecamatan_id);

        return response()->json(
            $query->get()->map(fn($o) => [
                'id'       => $o->id,
                'nama'     => $o->nama_opk,
                'kondisi'  => $o->kondisi,
                'lat'      => (float) $o->latitude,
                'lng'      => (float) $o->longitude,
                'kategori' => $o->kategori?->nama,
                'ikon'     => $o->kategori?->ikon,
                'kec'      => $o->kecamatan?->nama,
                'desa'     => $o->nama_desa_adat,
                'foto'     => $o->fotoUtama ? asset('storage/' . $o->fotoUtama->path) : null,
                'url'      => route('publik.opk.show', $o->id),
            ])
        );
    }

    // Detail OPK publik
    public function showOpk(OpkLaporan $opk)
    {
        if ($opk->status_verifikasi !== 'disetujui') {
            abort(404);
        }
        $opk->load(['kategori', 'kecamatan', 'desaDinas', 'fotos', 'videos']);
        return view('publik.opk-detail', compact('opk'));
    }
}
