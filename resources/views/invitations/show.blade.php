<x-guest-layout>
    <div class="mb-4 text-left space-y-2">
        <h1 class="text-lg font-semibold text-opta-teal-dark">{{ __('Einladung') }}</h1>
        <p class="text-opta-grey text-sm">
            {{ __('Gruppe:') }} <strong class="text-opta-teal-dark">{{ $invitation->group->name }}</strong>
        </p>
        <p class="text-opta-grey text-sm">
            {{ __('Rolle:') }} {{ $invitation->role->value }}
        </p>
    </div>

    @auth
        @if (strtolower(auth()->user()->email) !== strtolower($invitation->email))
            <p class="text-sm text-opta-berry text-left mb-4">{{ __('Bitte mit der eingeladenen E-Mail-Adresse anmelden.') }}</p>
        @else
            <form method="post" action="{{ route('invitations.accept', $token) }}" class="text-left">
                @csrf
                <x-primary-button>{{ __('Beitritt bestätigen') }}</x-primary-button>
            </form>
        @endif
    @else
        <p class="text-sm text-opta-grey text-left mb-4">{{ __('Melde dich an oder registriere dich mit der eingeladenen E-Mail-Adresse.') }}</p>
        <div class="flex flex-col gap-2 text-left">
            <a href="{{ route('login', ['email' => $invitation->email]) }}" class="text-opta-teal-dark font-medium hover:text-opta-teal-light">{{ __('Anmelden') }}</a>
            <a href="{{ route('register', ['email' => $invitation->email]) }}" class="text-opta-teal-dark font-medium hover:text-opta-teal-light">{{ __('Registrieren') }}</a>
        </div>
    @endauth
</x-guest-layout>
