@php
    use App\Enums\GroupMembershipRole;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4 text-left">
            <h2 class="text-xl font-semibold text-opta-teal-dark leading-tight">
                {{ __('Gruppen') }}
            </h2>
            @can('create', \App\Models\Group::class)
                <a
                    href="{{ route('groups.create') }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-opta-teal-dark px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-opta-teal-light focus:bg-opta-teal-light focus:outline-none focus:ring-2 focus:ring-opta-teal-light focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    {{ __('Gruppe anlegen') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-opta-green/40 bg-opta-green/10 px-4 py-3 text-opta-grey text-left" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm border border-opta-teal-light/30 sm:rounded-xl">
                <div class="p-6 space-y-6 text-left">
                    <form method="get" action="{{ route('groups.index') }}" class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-end">
                        <div class="grow min-w-[12rem]">
                            <x-input-label for="filter_q" :value="__('Suche (Name oder Slug)')" />
                            <x-text-input
                                id="filter_q"
                                name="q"
                                type="search"
                                class="mt-1 block w-full"
                                :value="$filters['q']"
                                autocomplete="off"
                            />
                        </div>
                        <div class="min-w-[10rem]">
                            <x-input-label for="filter_role" :value="__('Meine Rolle')" />
                            <select
                                id="filter_role"
                                name="role"
                                class="mt-1 block w-full rounded-md border-opta-teal-light/50 shadow-sm focus:border-opta-teal-dark focus:ring-opta-teal-dark text-sm text-opta-grey"
                            >
                                <option value="">{{ __('Alle') }}</option>
                                <option value="{{ GroupMembershipRole::GroupCreator->value }}" @selected($filters['role'] === GroupMembershipRole::GroupCreator->value)>{{ __('Gruppen-Ersteller') }}</option>
                                <option value="{{ GroupMembershipRole::Consumer->value }}" @selected($filters['role'] === GroupMembershipRole::Consumer->value)>{{ __('Consumer') }}</option>
                            </select>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <x-primary-button type="submit">{{ __('Filtern') }}</x-primary-button>
                            <a href="{{ route('groups.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-opta-teal-light/50 rounded-md text-sm text-opta-grey hover:bg-opta-teal-light/10">
                                {{ __('Zurücksetzen') }}
                            </a>
                        </div>
                    </form>

                    @if ($groups->isEmpty())
                        <p class="text-opta-grey">{{ __('Keine Gruppen gefunden.') }}</p>
                    @else
                        <div class="overflow-x-auto rounded-lg border border-opta-teal-light/30">
                            <table class="min-w-full divide-y divide-opta-teal-light/30 text-sm">
                                <thead class="bg-opta-teal-light/10 text-left text-xs font-semibold uppercase tracking-wide text-opta-grey">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">{{ __('Name') }}</th>
                                        <th scope="col" class="px-4 py-3">{{ __('Slug') }}</th>
                                        <th scope="col" class="px-4 py-3">{{ __('Mitglieder') }}</th>
                                        <th scope="col" class="px-4 py-3">{{ __('Erstellt von') }}</th>
                                        <th scope="col" class="px-4 py-3 text-end">{{ __('Aktionen') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-opta-teal-light/20 bg-white text-opta-grey">
                                    @foreach ($groups as $group)
                                        <tr>
                                            <td class="px-4 py-3 font-medium text-opta-teal-dark">
                                                <a href="{{ route('groups.show', $group) }}" class="hover:text-opta-teal-light">
                                                    {{ $group->name }}
                                                </a>
                                            </td>
                                            <td class="px-4 py-3 text-opta-grey/80">{{ $group->slug }}</td>
                                            <td class="px-4 py-3">{{ $group->users_count }}</td>
                                            <td class="px-4 py-3">{{ $group->creator?->name ?? '—' }}</td>
                                            <td class="px-4 py-3 text-end whitespace-nowrap space-x-3">
                                                @can('update', $group)
                                                    <a href="{{ route('groups.edit', $group) }}" class="text-opta-teal-dark hover:text-opta-teal-light font-medium">{{ __('Bearbeiten') }}</a>
                                                @endcan
                                                <a href="{{ route('groups.show', $group) }}" class="text-opta-teal-dark hover:text-opta-teal-light font-medium">{{ __('Details') }}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div>
                            {{ $groups->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
