<?php

namespace App\Http\Controllers;

use App\Enums\GroupMembershipRole;
use App\Http\Requests\StoreGroupRequest;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function create(): View
    {
        $this->authorize('create', Group::class);

        return view('groups.create');
    }

    public function store(StoreGroupRequest $request): RedirectResponse
    {
        $user = $request->user();
        $group = Group::query()->create([
            'name' => $request->validated('name'),
            'created_by' => $user->id,
        ]);
        $group->users()->attach($user->id, ['role' => GroupMembershipRole::GroupCreator]);

        return redirect()->route('groups.show', $group)->with('status', __('Gruppe angelegt.'));
    }

    public function show(Group $group): View
    {
        $this->authorize('view', $group);
        $group->load(['users', 'invitations' => fn ($q) => $q->whereNull('accepted_at')->orderByDesc('created_at'), 'elements']);

        return view('groups.show', ['group' => $group]);
    }

    public function destroy(Group $group): RedirectResponse
    {
        $this->authorize('delete', $group);
        $group->delete();

        return redirect()->route('dashboard')->with('status', __('Gruppe gelöscht.'));
    }
}
