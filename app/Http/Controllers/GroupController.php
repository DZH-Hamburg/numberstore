<?php

namespace App\Http\Controllers;

use App\Enums\GroupMembershipRole;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Group::class);

        $user = $request->user();
        $q = trim($request->string('q')->toString());
        $roleFilter = $request->string('role')->toString();

        $groups = Group::query()
            ->with(['creator:id,name'])
            ->withCount('users')
            ->when(! $user->isPlatformAdmin(), function ($query) use ($user): void {
                $query->whereHas('users', fn ($sub) => $sub->whereKey($user->getKey()));
            })
            ->when($q !== '', function ($query) use ($q): void {
                $like = '%'.addcslashes(mb_strtolower($q), '%_\\').'%';
                $query->where(function ($sub) use ($like): void {
                    $sub->whereRaw('LOWER(name) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(slug) LIKE ?', [$like]);
                });
            })
            ->when(
                in_array($roleFilter, [
                    GroupMembershipRole::GroupCreator->value,
                    GroupMembershipRole::Consumer->value,
                ], true),
                function ($query) use ($user, $roleFilter): void {
                    $query->whereHas('users', function ($sub) use ($user, $roleFilter): void {
                        $sub->where('users.id', $user->id)
                            ->where('group_user.role', $roleFilter);
                    });
                }
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('groups.index', [
            'groups' => $groups,
            'filters' => [
                'q' => $q,
                'role' => $roleFilter,
            ],
        ]);
    }

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

    public function edit(Group $group): View
    {
        $this->authorize('update', $group);

        return view('groups.edit', ['group' => $group]);
    }

    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        $group->update(['name' => $request->validated('name')]);

        return redirect()->route('groups.show', $group)->with('status', __('Gruppe gespeichert.'));
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

        return redirect()->route('groups.index')->with('status', __('Gruppe gelöscht.'));
    }
}
