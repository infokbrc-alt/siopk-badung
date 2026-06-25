<?php

namespace App\Helpers;

class CacheKeys
{
    const RINGKASAN_EKSEKUTIF = 'siopk_ringkasan_eksekutif';
    const PUBLIK_DASHBOARD     = 'publik_dashboard';
    const SIDEBAR_COUNTS       = 'sidebar_opk_counts';
    const KATEGORI_LIST        = 'kategori_list';
    const KECAMATAN_LIST       = 'kecamatan_list';
    const PETA_PUBLIK          = 'peta_publik_data';

    public static function adminDashboard(int $userId): string
    {
        return 'admin_dashboard_' . $userId;
    }

    public static function petaPublik(string $kondisi = '', string $kategoriId = '', string $kecamatanId = ''): string
    {
        return 'peta_publik_' . md5($kondisi . '|' . $kategoriId . '|' . $kecamatanId);
    }
}
