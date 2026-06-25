<?php

namespace App\Policies;

use App\Models\OpkLaporan;
use App\Models\User;

class OpkLaporanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, OpkLaporan $laporan): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, OpkLaporan $laporan): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, OpkLaporan $laporan): bool
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function forceDelete(User $user, OpkLaporan $laporan): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, OpkLaporan $laporan): bool
    {
        return $user->isAdmin();
    }

    public function verify(User $user, OpkLaporan $laporan): bool
    {
        return $user->canVerify();
    }
}
