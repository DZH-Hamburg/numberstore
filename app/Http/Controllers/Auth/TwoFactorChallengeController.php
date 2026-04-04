<?php

namespace App\Http\Controllers\Auth;

use App\Enums\TwoFactorMethod;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorEmailCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use OTPHP\TOTP;

class TwoFactorChallengeController extends Controller
{
    public function __construct(
        private TwoFactorEmailCodeService $emailCodeService,
    ) {}

    public function create(Request $request): RedirectResponse|View
    {
        $user = $this->pendingUser($request);
        if (! $user instanceof User) {
            return redirect()->route('login')
                ->withErrors(['email' => __('Deine Sitzung ist abgelaufen. Bitte melde dich erneut an.')]);
        }

        if ($user->two_factor_method === TwoFactorMethod::Email) {
            $this->emailCodeService->ensureLoginCodeSent($user);
        }

        return view('auth.two-factor-challenge', [
            'user' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user instanceof User) {
            return redirect()->route('login')
                ->withErrors(['email' => __('Deine Sitzung ist abgelaufen. Bitte melde dich erneut an.')]);
        }

        if ($user->two_factor_method === TwoFactorMethod::Email) {
            $this->emailCodeService->ensureLoginCodePrepared($user);
        }

        $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $code = $request->string('code')->toString();

        $valid = match ($user->two_factor_method) {
            TwoFactorMethod::Email => $this->emailCodeService->verifyLoginCode($user, $code),
            TwoFactorMethod::Totp => $this->verifyTotp($user, $code),
        };

        if (! $valid) {
            throw ValidationException::withMessages([
                'code' => [__('Der eingegebene Code ist ungültig oder abgelaufen.')],
            ]);
        }

        $remember = (bool) $request->session()->pull('two_factor_remember', false);

        $request->session()->forget([
            'two_factor_pending_user_id',
            'two_factor_remember',
        ]);

        Auth::login($user, $remember);

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $this->pendingUser($request);
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        if ($user->two_factor_method !== TwoFactorMethod::Email) {
            return redirect()->route('two-factor.login');
        }

        if ($this->emailCodeService->resendLoginCode($user)) {
            return redirect()->route('two-factor.login')
                ->with('status', __('Ein neuer Code wurde per E-Mail versendet.'));
        }

        return redirect()->route('two-factor.login')
            ->withErrors(['code' => __('Zu viele Anfragen. Bitte warte, bevor du einen neuen Code anforderst.')]);
    }

    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get('two_factor_pending_user_id');
        if (! is_int($id) && ! (is_string($id) && ctype_digit($id))) {
            return null;
        }

        return User::query()->find((int) $id);
    }

    private function verifyTotp(User $user, string $code): bool
    {
        if ($user->two_factor_confirmed_at === null) {
            return false;
        }

        $secret = $user->two_factor_secret;
        if (! is_string($secret) || $secret === '') {
            return false;
        }

        $totp = TOTP::createFromSecret($secret);

        return $totp->verify($code, null, 1);
    }
}
