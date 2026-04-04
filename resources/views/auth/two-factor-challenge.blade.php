@php
    use App\Enums\TwoFactorMethod;
@endphp

<x-guest-layout>
    <x-slot name="footer">
        <p class="text-center text-sm text-opta-grey/80">
            <a
                href="https://curenect.de/impressum/"
                class="text-opta-teal-dark hover:text-opta-teal-light underline underline-offset-2 focus:outline-none focus:ring-2 focus:ring-opta-teal-dark focus:ring-offset-2 rounded"
                target="_blank"
                rel="noopener noreferrer"
            >{{ __('Impressum') }}</a>
        </p>
    </x-slot>

    <div class="mb-6 text-center">
        <h1 class="text-lg font-semibold text-opta-teal-dark">{{ __('Zweiter Faktor') }}</h1>
        @if ($user->two_factor_method === TwoFactorMethod::Email)
            <p class="mt-2 text-sm text-opta-grey">
                {{ __('Wir haben einen 6-stelligen Code an :email gesendet.', ['email' => $user->email]) }}
            </p>
        @else
            <p class="mt-2 text-sm text-opta-grey">
                {{ __('Gib den 6-stelligen Code aus deiner Authenticator-App ein.') }}
            </p>
        @endif
    </div>

    @if (session('status'))
        <p class="mb-4 text-sm text-opta-teal-dark text-center" role="status">{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-4">
        @csrf
        <div>
            <x-input-label for="code" :value="__('Bestätigungscode')" />
            <x-text-input
                id="code"
                class="block mt-1 w-full tracking-widest text-center text-lg font-mono"
                type="text"
                name="code"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="6"
                autocomplete="one-time-code"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>
        <div class="flex justify-end">
            <x-primary-button>{{ __('Bestätigen') }}</x-primary-button>
        </div>
    </form>

    @if ($user->two_factor_method === TwoFactorMethod::Email)
        <form method="POST" action="{{ route('two-factor.login.resend') }}" class="mt-4 text-center">
            @csrf
            <button
                type="submit"
                class="text-sm text-opta-teal-dark hover:text-opta-teal-light underline underline-offset-2 rounded-md focus:outline-none focus:ring-2 focus:ring-opta-teal-dark"
            >{{ __('Code erneut senden') }}</button>
        </form>
    @endif

    <p class="mt-6 text-center text-sm">
        <a class="text-opta-grey hover:text-opta-teal-dark underline underline-offset-2" href="{{ route('login') }}">{{ __('Zurück zum Login') }}</a>
    </p>
</x-guest-layout>
