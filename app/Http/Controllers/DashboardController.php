<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', Group::class);

        $user = $request->user();
        $groups = Group::query()
            ->when(! $user->isPlatformAdmin(), function ($q) use ($user): void {
                $q->whereIn('id', $user->groups()->pluck('groups.id'));
            })
            ->orderBy('name')
            ->get();

        return view('dashboard', ['groups' => $groups]);
    }
}
