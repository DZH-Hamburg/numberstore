<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        $devAdminEmail = null;
        $devAdminPassword = null;

        if (app()->environment('local')) {
            $devAdminEmail = $this->devAdminEnvString('dev_admin_user');
            $devAdminPassword = $this->devAdminEnvString('dev_admin_password');
        }

        return view('auth.login', [
            'devAdminEmail' => $devAdminEmail,
            'devAdminPassword' => $devAdminPassword,
        ]);
    }

    private function devAdminEnvString(string $key): ?string
    {
        $value = env($key);

        return is_string($value) && $value !== '' ? $value : null;
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
