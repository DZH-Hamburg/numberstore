<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvitationAcceptController extends Controller
{
    public function show(string $token): View
    {
        $invitation = Invitation::query()
            ->where('token_hash', hash('sha256', $token))
            ->with('group')
            ->firstOrFail();

        abort_if($invitation->isAccepted(), 410);
        abort_if($invitation->isExpired(), 410);

        return view('invitations.show', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::query()
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();

        abort_if($invitation->isAccepted(), 410);
        abort_if($invitation->isExpired(), 410);

        $user = $request->user();
        abort_unless(Str::lower($user->email) === Str::lower($invitation->email), 403);

        if (! $user->isMemberOf($invitation->group)) {
            $user->groups()->attach($invitation->group_id, ['role' => $invitation->role->value]);
        }

        $invitation->forceFill(['accepted_at' => now()])->save();

        return redirect()->route('groups.show', $invitation->group)->with('status', __('Du bist der Gruppe beigetreten.'));
    }
}
