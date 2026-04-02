<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-opta-teal-dark leading-tight text-left">
            {{ __('Benutzer bearbeiten') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-opta-teal-light/30 shadow-sm sm:rounded-xl p-6">
                <form method="post" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')
                    @include('admin.users._form-fields', ['user' => $user, 'requirePassword' => false])
                    <div class="flex items-center gap-3">
                        <x-primary-button>{{ __('Speichern') }}</x-primary-button>
                        <a href="{{ route('admin.users.index') }}" class="text-sm text-opta-teal-dark hover:text-opta-teal-light">{{ __('Abbrechen') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
