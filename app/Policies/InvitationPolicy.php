<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\Invitation;
use App\Models\User;

class InvitationPolicy
{
    public function create(User $user, Group $group): bool
    {
        return $user->isPlatformAdmin() || $user->isGroupCreatorIn($group);
    }

    public function delete(User $user, Invitation $invitation): bool
    {
        return $user->isPlatformAdmin() || $user->isGroupCreatorIn($invitation->group);
    }
}
