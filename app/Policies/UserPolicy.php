<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $target): bool
    {
        if ($target->isSuperAdmin() && $user->id !== $target->id) {
            return false;
        }

        return $user->isAdmin();
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->id === $target->id || $target->isSuperAdmin()) {
            return false;
        }

        return $user->isAdmin();
    }

    public function toggleActive(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        return $user->isAdmin();
    }
}
