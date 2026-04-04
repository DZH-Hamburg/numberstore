@php
    use App\Enums\TwoFactorMethod;
    use App\Support\TotpProvisioningQr;
    $enrollmentUri = session('totp_enrollment_uri');
    $enrollmentSecret = session('totp_enrollment_secret');
@endphp

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Zweiter Faktor (2FA)') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Standard ist ein Code per E-Mail bei jeder Anmeldung. Du kannst stattdessen eine Authenticator-App (TOTP) verwenden.') }}
        </p>
    </header>

    <div class="mt-6 space-y-4 rounded-lg border border-gray-200 bg-gray-50/80 p-4">
        <p class="text-sm text-gray-700">
            <span class="font-medium text-gray-900">{{ __('Aktive Methode:') }}</span>
            @if ($user->two_factor_method === TwoFactorMethod::Email)
                {{ __('E-Mail-Code') }}
            @else
                {{ __('Authenticator-App (TOTP)') }}
            @endif
        </p>

        @if ($user->two_factor_method === TwoFactorMethod::Email)
            <form method="post" action="{{ route('profile.two-factor.totp.start') }}" class="space-y-3">
                @csrf
                <div>
                    <x-input-label for="totp_start_password" :value="__('Aktuelles Passwort')" />
                    <x-text-input id="totp_start_password" name="password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
                    <x-input-error class="mt-2" :messages="$errors->get('password')" />
                </div>
                <x-secondary-button type="submit">{{ __('Authenticator-App einrichten') }}</x-secondary-button>
            </form>
        @else
            <form method="post" action="{{ route('profile.two-factor.email') }}" class="space-y-3" onsubmit="return confirm(@json(__('Zurück zu E-Mail-Codes? Die Authenticator-Einrichtung wird entfernt.')));">
                @csrf
                <div>
                    <x-input-label for="email_2fa_password" :value="__('Aktuelles Passwort')" />
                    <x-text-input id="email_2fa_password" name="password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
                    <x-input-error class="mt-2" :messages="$errors->get('password')" />
                </div>
                <x-secondary-button type="submit">{{ __('Wieder E-Mail-Code verwenden') }}</x-secondary-button>
            </form>
        @endif
    </div>

    @if (is_string($enrollmentUri) && $enrollmentUri !== '' && is_string($enrollmentSecret) && $enrollmentSecret !== '')
        <div class="mt-6 space-y-4 rounded-lg border border-opta-teal-light/40 bg-white p-4 shadow-sm">
            <h3 class="text-sm font-semibold text-opta-teal-dark">{{ __('Authenticator einrichten') }}</h3>
            <p class="text-sm text-gray-600">{{ __('Scanne den QR-Code mit deiner App (z. B. Google Authenticator, 1Password, Bitwarden) oder gib den Schlüssel manuell ein.') }}</p>
            <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                <div
                    class="shrink-0 rounded-lg border border-gray-200 bg-white p-1 [&_svg]:block [&_svg]:max-h-[200px] [&_svg]:max-w-[200px]"
                    role="img"
                    aria-label="{{ __('QR-Code für Authenticator-App') }}"
                >
                    {!! TotpProvisioningQr::svg($enrollmentUri) !!}
                </div>
                <div class="min-w-0 flex-1 text-sm">
                    <p class="font-medium text-gray-900">{{ __('Geheimer Schlüssel (Base32)') }}</p>
                    <p class="mt-1 break-all font-mono text-xs text-gray-700">{{ $enrollmentSecret }}</p>
                </div>
            </div>

            <form method="post" action="{{ route('profile.two-factor.totp.confirm') }}" class="space-y-3">
                @csrf
                <div>
                    <x-input-label for="totp_confirm_code" :value="__('6-stelliger Code aus der App')" />
                    <x-text-input id="totp_confirm_code" name="code" type="text" class="mt-1 block w-full max-w-xs font-mono tracking-widest" inputmode="numeric" pattern="[0-9]*" maxlength="6" required autocomplete="one-time-code" />
                    <x-input-error class="mt-2" :messages="$errors->get('code')" />
                </div>
                <div class="flex flex-wrap gap-3">
                    <x-primary-button type="submit">{{ __('Authenticator aktivieren') }}</x-primary-button>
                </div>
            </form>

            <form method="post" action="{{ route('profile.two-factor.totp.cancel') }}">
                @csrf
                <button type="submit" class="text-sm text-gray-600 underline hover:text-gray-900">{{ __('Einrichtung abbrechen') }}</button>
            </form>
        </div>
    @endif
</section>
