<?php

namespace App\Listeners;

use App\Events\AiAnalysisCompleted;
use App\Events\LaporanCreated;
use App\Events\LaporanVerified;
use App\Helpers\CacheKeys;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SideEffectHandler
{
    private function clearSidebarCache(): void
    {
        Cache::forget(CacheKeys::SIDEBAR_COUNTS);
        Cache::forget(CacheKeys::RINGKASAN_EKSEKUTIF);
    }

    public function handleLaporanCreated(LaporanCreated $event): void
    {
        $this->clearSidebarCache();
        Log::info("Laporan dibuat: {$event->laporan->kode_laporan}");
    }

    public function handleLaporanVerified(LaporanVerified $event): void
    {
        $this->clearSidebarCache();
        Log::info("Laporan {$event->laporan->kode_laporan} telah {$event->status}");
    }

    public function handleAiAnalysisCompleted(AiAnalysisCompleted $event): void
    {
        $this->clearSidebarCache();
        Log::info("AI selesai analisis: {$event->laporan->kode_laporan}");
    }
}
