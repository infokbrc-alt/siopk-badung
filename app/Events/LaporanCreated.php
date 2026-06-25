<?php

namespace App\Events;

use App\Models\OpkLaporan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LaporanCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly OpkLaporan $laporan
    ) {}
}
