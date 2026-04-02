<?php

namespace App\Policies;

use App\Models\Element;
use App\Models\Group;
use App\Models\User;

class ElementPolicy
{
    public function view(User $user, Element $element): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        return $element->groups()->whereIn('groups.id', $user->groups()->pluck('groups.id'))->exists();
    }

    public function viewViaApi(User $user, Element $element, Group $group): bool
    {
        if (! $user->isMemberOf($group)) {
            return false;
        }

        if ($user->isPlatformAdmin()) {
            return true;
        }

        if ($user->isGroupCreatorIn($group) && $element->groups()->whereKey($group->getKey())->exists()) {
            return true;
        }

        $pivot = $element->groups()->whereKey($group->getKey())->first()?->pivot;

        return (bool) ($pivot?->consumer_can_read_via_api ?? false);
    }

    public function create(User $user, ?Group $group = null): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        if ($group === null) {
            return false;
        }

        return $user->isGroupCreatorIn($group);
    }

    public function update(User $user, Element $element): bool
    {
        if ($user->isPlatformAdmin()) {
            return true;
        }

        $groupIds = $element->groups()->pluck('groups.id');

        return $user->groups()
            ->whereIn('groups.id', $groupIds)
            ->wherePivot('role', 'group_creator')
            ->exists();
    }

    public function delete(User $user, Element $element): bool
    {
        return $this->update($user, $element);
    }

    public function attachToGroup(User $user, Element $element, Group $group): bool
    {
        return $this->update($user, $element) && ($user->isPlatformAdmin() || $user->isGroupCreatorIn($group));
    }

    public function setConsumerApiFlag(User $user, Element $element, Group $group): bool
    {
        return $user->isPlatformAdmin() || $user->isGroupCreatorIn($group);
    }
}
