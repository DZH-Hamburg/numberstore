<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-opta-teal-dark leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm border border-opta-teal-light/30 sm:rounded-xl">
                <div class="p-6 space-y-4 text-left">
                    <h3 class="text-lg font-semibold text-opta-teal-dark">{{ __('Deine Gruppen') }}</h3>
                    @if ($groups->isEmpty())
                        <p class="text-opta-grey">{{ __('Noch keine Gruppen. Lege eine an oder nimm eine Einladung an.') }}</p>
                        <a href="{{ route('groups.index') }}" class="inline-flex items-center gap-2 text-opta-teal-dark font-medium hover:text-opta-teal-light">
                            {{ __('Zu den Gruppen') }}
                        </a>
                    @else
                        <ul class="divide-y divide-opta-teal-light/30">
                            @foreach ($groups as $group)
                                <li class="py-3 flex flex-wrap items-center justify-between gap-2">
                                    <a href="{{ route('groups.show', $group) }}" class="font-medium text-opta-teal-dark hover:text-opta-teal-light">
                                        {{ $group->name }}
                                    </a>
                                    <span class="text-sm text-opta-grey/80">{{ $group->slug }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <div class="bg-opta-periwinkle/15 border border-opta-periwinkle/30 rounded-xl p-5 text-left">
                <p class="text-opta-grey text-sm mb-2">{{ __('API-Dokumentation') }}</p>
                <a
                    href="{{ route('l5-swagger.default.api') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center text-sm font-medium text-opta-teal-dark hover:text-opta-teal-light"
                >
                    OpenAPI / Swagger
                    <svg class="ms-1 h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
