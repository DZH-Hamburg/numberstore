<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()->orderBy('name')->paginate(15);

        return view('admin.users.index', ['users' => $users]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(64)),
            'is_platform_admin' => $data['is_platform_admin'] ?? false,
            'can_create_groups' => $data['can_create_groups'] ?? false,
            'email_verified_at' => now(),
        ]);

        $status = Password::sendResetLink(['email' => $user->email]);

        $message = __('Benutzer angelegt.');
        if ($status === Password::RESET_LINK_SENT) {
            $message .= ' '.__('Der Nutzer hat eine E-Mail mit einem Link erhalten, um sein Passwort zu setzen.');
        } else {
            $message .= ' '.__('Die Einladungs-E-Mail konnte nicht gesendet werden (:reason). Bitte Mail-Konfiguration prüfen oder „Passwort vergessen“ für diesen Nutzer nutzen.', [
                'reason' => __($status),
            ]);
        }

        return redirect()->route('admin.users.index')->with('status', $message);
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if ($data['password'] ?? null) {
            $user->password = $data['password'];
        }
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'is_platform_admin' => $data['is_platform_admin'] ?? false,
            'can_create_groups' => $data['can_create_groups'] ?? false,
        ]);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('status', __('Benutzer gespeichert.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', __('Benutzer gelöscht.'));
    }
}
