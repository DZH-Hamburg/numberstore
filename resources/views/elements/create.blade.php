@php
    use App\Enums\ElementType;
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-opta-teal-dark leading-tight text-left">
            {{ __('Element anlegen') }} — {{ $group->name }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-opta-teal-light/30 shadow-sm sm:rounded-xl p-6 text-left">
                <form method="post" action="{{ route('groups.elements.store', $group) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="type" :value="__('Typ')" />
                        <select id="type" name="type" class="mt-1 block w-full rounded-md border-opta-teal-light/50 shadow-sm focus:border-opta-teal-dark focus:ring-opta-teal-dark" required>
                            @foreach (ElementType::cases() as $t)
                                <option value="{{ $t->value }}" @selected(old('type') === $t->value)>{{ $t->value }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="config" :value="__('Konfiguration (JSON, optional)')" />
                        <textarea id="config" name="config" rows="4" class="mt-1 block w-full rounded-md border-opta-teal-light/50 shadow-sm focus:border-opta-teal-dark focus:ring-opta-teal-dark font-mono text-xs" placeholder="{}">{{ old('config') }}</textarea>
                        <x-input-error :messages="$errors->get('config')" class="mt-2" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="consumer_can_read_via_api" name="consumer_can_read_via_api" type="checkbox" value="1" class="rounded border-opta-teal-light text-opta-teal-dark focus:ring-opta-teal-dark" @checked(old('consumer_can_read_via_api')) />
                        <x-input-label for="consumer_can_read_via_api" :value="__('Consumer darf per API lesen')" class="!mb-0" />
                    </div>
                    <div class="flex items-center gap-3">
                        <x-primary-button>{{ __('Speichern') }}</x-primary-button>
                        <a href="{{ route('groups.show', $group) }}" class="text-sm text-opta-teal-dark hover:text-opta-teal-light">{{ __('Abbrechen') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
