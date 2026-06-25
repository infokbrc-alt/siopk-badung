<?php

namespace App\Helpers;

class CacheKeys
{
    const RINGKASAN_EKSEKUTIF = 'siopk_ringkasan_eksekutif';
    const PUBLIK_DASHBOARD     = 'publik_dashboard';
    const SIDEBAR_COUNTS       = 'sidebar_opk_counts';
    const KATEGORI_LIST        = 'kategori_list';
    const KECAMATAN_LIST       = 'kecamatan_list';

    public static function adminDashboard(int $userId): string
    {
        return 'admin_dashboard_' . $userId;
    }
}
