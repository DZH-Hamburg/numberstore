<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGroupInvitationRequest;
use App\Mail\GroupInvitationMail;
use App\Models\Group;
use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class GroupInvitationController extends Controller
{
    public function store(StoreGroupInvitationRequest $request, Group $group): RedirectResponse
    {
        $validated = $request->validated();
        $email = Str::lower($validated['email']);
        $rawToken = Str::random(48);

        $invitation = Invitation::query()->create([
            'email' => $email,
            'token_hash' => hash('sha256', $rawToken),
            'group_id' => $group->id,
            'role' => $validated['role'],
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addDays(14),
        ]);

        $acceptUrl = route('invitations.show', ['token' => $rawToken], absolute: true);
        Mail::to($email)->send(new GroupInvitationMail($group, $acceptUrl, $invitation->role->value));

        return redirect()->route('groups.show', $group)->with('status', __('Einladung versendet.'));
    }

    public function destroy(Group $group, Invitation $invitation): RedirectResponse
    {
        $this->authorize('delete', $invitation);
        abort_unless($invitation->group_id === $group->id, 404);
        $invitation->delete();

        return redirect()->route('groups.show', $group)->with('status', __('Einladung zurückgezogen.'));
    }
}
