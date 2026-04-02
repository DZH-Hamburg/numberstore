<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\GroupMembershipRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Group::class);
        $user = $request->user();
        $groups = Group::query()
            ->when(! $user->isPlatformAdmin(), function ($q) use ($user): void {
                $q->whereIn('id', $user->groups()->pluck('groups.id'));
            })
            ->orderBy('name')
            ->get();

        return response()->json($groups);
    }

    public function show(Request $request, Group $group): JsonResponse
    {
        $this->authorize('view', $group);

        return response()->json($group->loadCount('users'));
    }

    public function store(StoreGroupRequest $request): JsonResponse
    {
        $user = $request->user();
        $group = Group::query()->create([
            'name' => $request->validated('name'),
            'created_by' => $user->id,
        ]);
        $group->users()->attach($user->id, ['role' => GroupMembershipRole::GroupCreator]);

        return response()->json($group, 201);
    }

    public function destroy(Request $request, Group $group): JsonResponse
    {
        $this->authorize('delete', $group);
        $group->delete();

        return response()->json(null, 204);
    }
}
