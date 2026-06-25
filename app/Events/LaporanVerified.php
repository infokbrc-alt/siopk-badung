<?php

namespace App\Events;

use App\Models\OpkLaporan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LaporanVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly OpkLaporan $laporan,
        public readonly string $status
    ) {}
}
