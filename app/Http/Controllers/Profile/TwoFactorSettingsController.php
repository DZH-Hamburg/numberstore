<?php

namespace App\Http\Controllers\Profile;

use App\Enums\TwoFactorMethod;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OTPHP\TOTP;

class TwoFactorSettingsController extends Controller
{
    public function startTotpEnrollment(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $totp = TOTP::generate();
        $totp->setIssuer((string) config('app.name'));
        $totp->setLabel($user->email);

        $request->session()->put([
            'totp_enrollment_secret' => $totp->getSecret(),
            'totp_enrollment_uri' => $totp->getProvisioningUri(),
        ]);

        return redirect()->route('profile.edit')
            ->with('status', __('Authenticator: Scanne den QR-Code oder gib den Schlüssel manuell ein, und bestätige mit einem 6-stelligen Code.'));
    }

    public function confirmTotpEnrollment(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $secret = $request->session()->get('totp_enrollment_secret');
        if (! is_string($secret) || $secret === '') {
            return redirect()->route('profile.edit')
                ->withErrors(['code' => __('Bitte starte die Authenticator-Einrichtung erneut.')]);
        }

        $totp = TOTP::createFromSecret($secret);
        if (! $totp->verify($request->string('code')->toString(), null, 1)) {
            throw ValidationException::withMessages([
                'code' => [__('Ungültiger Code. Bitte erneut versuchen.')],
            ]);
        }

        /** @var User $user */
        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_method' => TwoFactorMethod::Totp,
            'two_factor_confirmed_at' => now(),
        ])->save();

        $request->session()->forget(['totp_enrollment_secret', 'totp_enrollment_uri']);

        return redirect()->route('profile.edit')
            ->with('status', __('Authenticator-App ist jetzt aktiv. Bei der Anmeldung wird der Code aus der App abgefragt.'));
    }

    public function cancelTotpEnrollment(Request $request): RedirectResponse
    {
        $request->session()->forget(['totp_enrollment_secret', 'totp_enrollment_uri']);

        return redirect()->route('profile.edit');
    }

    public function useEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->forceFill([
            'two_factor_method' => TwoFactorMethod::Email,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $request->session()->forget(['totp_enrollment_secret', 'totp_enrollment_uri']);

        return redirect()->route('profile.edit')
            ->with('status', __('Zweiter Faktor erfolgt wieder per E-Mail-Code bei der Anmeldung.'));
    }
}
