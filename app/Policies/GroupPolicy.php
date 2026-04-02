<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    /**
     * Authentifizierte Nutzer dürfen die Gruppenliste öffnen (kann leer sein, z. B. nach Registrierung).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Group $group): bool
    {
        return $user->isPlatformAdmin() || $user->isMemberOf($group);
    }

    public function create(User $user): bool
    {
        return $user->canCreatePlatformGroups();
    }

    public function update(User $user, Group $group): bool
    {
        return $user->isPlatformAdmin() || $user->isGroupCreatorIn($group);
    }

    public function delete(User $user, Group $group): bool
    {
        return $user->isPlatformAdmin();
    }

    public function inviteMembers(User $user, Group $group): bool
    {
        return $user->isPlatformAdmin() || $user->isGroupCreatorIn($group);
    }

    public function detachMembers(User $user, Group $group): bool
    {
        return $user->isPlatformAdmin() || $user->isGroupCreatorIn($group);
    }
}
