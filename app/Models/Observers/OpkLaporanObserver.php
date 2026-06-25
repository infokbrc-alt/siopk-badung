<?php

namespace App\Models\Observers;

use App\Models\OpkLaporan;
use Illuminate\Support\Facades\Storage;

class OpkLaporanObserver
{
    public function forceDeleted(OpkLaporan $laporan): void
    {
        foreach ($laporan->fotos as $foto) {
            Storage::disk('public')->delete($foto->path);
        }
        foreach ($laporan->dokumens as $dok) {
            Storage::disk('public')->delete($dok->path);
        }
    }
}
