<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->isPlatformAdmin();
    }

    public function view(User $actor, User $model): bool
    {
        return $actor->isPlatformAdmin();
    }

    public function create(User $actor): bool
    {
        return $actor->isPlatformAdmin();
    }

    public function update(User $actor, User $model): bool
    {
        return $actor->isPlatformAdmin();
    }

    public function delete(User $actor, User $model): bool
    {
        if (! $actor->isPlatformAdmin()) {
            return false;
        }

        if ($actor->id === $model->id) {
            return false;
        }

        if ($model->is_platform_admin && User::query()->where('is_platform_admin', true)->count() <= 1) {
            return false;
        }

        return true;
    }
}
