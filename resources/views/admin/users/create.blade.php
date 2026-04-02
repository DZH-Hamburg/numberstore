<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-opta-teal-dark leading-tight text-left">
            {{ __('Neuer Benutzer') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-opta-teal-light/30 shadow-sm sm:rounded-xl p-6">
                <p class="text-sm text-opta-grey mb-4 text-left">{{ __('Es wird kein Passwort vergeben. Der Nutzer erhält eine E-Mail mit Link zum Festlegen des Passworts (wie „Passwort vergessen“).') }}</p>
                <form method="post" action="{{ route('admin.users.store') }}" class="space-y-6">
                    @csrf
                    @include('admin.users._form-fields', ['showPasswordFields' => false])
                    <div class="flex items-center gap-3">
                        <x-primary-button>{{ __('Speichern') }}</x-primary-button>
                        <a href="{{ route('admin.users.index') }}" class="text-sm text-opta-teal-dark hover:text-opta-teal-light">{{ __('Abbrechen') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
