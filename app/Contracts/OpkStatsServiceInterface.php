<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface OpkStatsServiceInterface
{
    public function dashboardAdmin(): array;

    public function dashboardPublik(): array;

    public function laporanAdmin(): array;

    public function ringkasanEksekutif(): array;

    public function kategoriWithOpkCount(): Collection;

    public function kecamatanWithOpkCount(): Collection;
}
