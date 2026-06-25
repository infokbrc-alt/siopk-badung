<?php

namespace App\Http\View\Composers;

use App\Helpers\CacheKeys;
use App\Models\OpkLaporan;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $counts = Cache::remember(CacheKeys::SIDEBAR_COUNTS, now()->addMinutes(5), function () {
            return [
                'antrian' => OpkLaporan::whereIn('status_verifikasi', ['menunggu', 'ai_review', 'review_dinas'])->count(),
                'aiKritis' => OpkLaporan::where('status_verifikasi', 'disetujui')
                    ->where('ai_urgency_score', '>=', 8)
                    ->count(),
            ];
        });

        $view->with('sidebarAntrian', $counts['antrian']);
        $view->with('sidebarAiKritis', $counts['aiKritis']);
    }
}
