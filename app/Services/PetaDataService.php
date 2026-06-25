<?php

namespace App\Services;

use App\Helpers\CacheKeys;
use App\Models\OpkLaporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PetaDataService
{
    public function getPetaData(Request $request, bool $isAdmin = false): array
    {
        $version = (int) Cache::get('peta_data_version', 1);

        $cacheKey = $isAdmin
            ? 'peta_admin_v' . $version . '_' . md5(serialize($request->only(['kondisi', 'kategori_id', 'kecamatan_id'])))
            : 'peta_publik_v' . $version . '_' . md5($request->get('kondisi', '') . '|' . $request->get('kategori_id', '') . '|' . $request->get('kecamatan_id', ''));

        return Cache::remember($cacheKey, 1800, function () use ($request, $isAdmin) {
            return $this->buildPetaData($request, $isAdmin);
        });
    }

    public static function invalidateCache(): void
    {
        Cache::increment('peta_data_version');
        Cache::forget('publik_dashboard');
    }

    private function buildPetaData(Request $request, bool $isAdmin): array
    {
        $query = OpkLaporan::select($this->selectColumns($isAdmin))
            ->with([
                'kategori:id,nama,ikon',
                'kecamatan:id,nama',
                'fotoUtama:laporan_id,path',
            ])
            ->where('status_verifikasi', 'disetujui')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', 0)
            ->where('longitude', '!=', 0);

        $this->applyFilters($query, $request);

        return $query->get()
            ->map(fn($opk) => $this->formatMarker($opk, $isAdmin))
            ->all();
    }

    private function selectColumns(bool $isAdmin): array
    {
        $columns = [
            'id', 'kode_laporan', 'nama_opk', 'kondisi',
            'latitude', 'longitude', 'kategori_id',
            'kecamatan_id', 'nama_desa_adat',
        ];

        if ($isAdmin) {
            $columns[] = 'ai_urgency_score';
            $columns[] = 'status_pelindungan';
        }

        return $columns;
    }

    private function applyFilters($query, Request $request): void
    {
        foreach (['kondisi', 'kategori_id', 'kecamatan_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->$filter);
            }
        }
    }

    private function formatMarker($opk, bool $isAdmin): array
    {
        if ($isAdmin) {
            return [
                'id'            => $opk->id,
                'kode'          => $opk->kode_laporan,
                'nama'          => $opk->nama_opk,
                'kondisi'       => $opk->kondisi,
                'lat'           => (float) $opk->latitude,
                'lng'           => (float) $opk->longitude,
                'kategori'      => $opk->kategori?->nama,
                'ikon_kategori' => $opk->kategori?->ikon,
                'kecamatan'     => $opk->kecamatan?->nama,
                'desa_adat'     => $opk->nama_desa_adat,
                'urgency_score' => $opk->ai_urgency_score,
                'foto_url'      => $opk->fotoUtama ? asset('storage/' . $opk->fotoUtama->path) : null,
                'detail_url'    => route('admin.opk.show', $opk->id),
            ];
        }

        return [
            'id'       => $opk->id,
            'nama'     => $opk->nama_opk,
            'kondisi'  => $opk->kondisi,
            'lat'      => (float) $opk->latitude,
            'lng'      => (float) $opk->longitude,
            'kategori' => $opk->kategori?->nama,
            'ikon'     => $opk->kategori?->ikon,
            'kec'      => $opk->kecamatan?->nama,
            'desa'     => $opk->nama_desa_adat,
            'foto'     => $opk->fotoUtama ? asset('storage/' . $opk->fotoUtama->path) : null,
            'url'      => route('publik.opk.show', $opk->id),
        ];
    }
}
