<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class GroupMemberController extends Controller
{
    public function destroy(Group $group, User $user): RedirectResponse
    {
        $this->authorize('detachMembers', $group);
        abort_if($user->id === auth()->id(), 403);
        $group->users()->detach($user->id);

        return redirect()->route('groups.show', $group)->with('status', __('Mitglied entfernt.'));
    }
}
