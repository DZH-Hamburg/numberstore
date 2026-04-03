<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4 text-left">
            <h2 class="text-xl font-semibold text-opta-teal-dark leading-tight">
                {{ __('Benutzerverwaltung') }}
            </h2>
            @can('create', App\Models\User::class)
                <a href="{{ route('admin.users.create') }}" class="text-sm font-medium text-opta-teal-dark hover:text-opta-teal-light">
                    {{ __('Neuer Benutzer') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white border border-opta-teal-light/30 shadow-sm sm:rounded-xl overflow-x-auto text-left">
                <table class="min-w-full divide-y divide-opta-teal-light/30 text-sm">
                    <thead class="bg-opta-teal-light/10">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-opta-teal-dark">{{ __('Name') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-opta-teal-dark">{{ __('E-Mail') }}</th>
                            <th class="px-4 py-3 text-left font-semibold text-opta-teal-dark">{{ __('Rollen') }}</th>
                            <th class="px-4 py-3 text-right font-semibold text-opta-teal-dark">{{ __('Aktionen') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-opta-teal-light/20 text-opta-grey">
                        @foreach ($users as $u)
                            <tr>
                                <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                                <td class="px-4 py-3">{{ $u->email }}</td>
                                <td class="px-4 py-3">
                                    @if ($u->is_platform_admin)
                                        <span class="inline-block rounded px-2 py-0.5 text-xs bg-opta-teal-dark text-white">{{ __('Admin') }}</span>
                                    @endif
                                    @if ($u->can_create_groups)
                                        <span class="inline-block rounded px-2 py-0.5 text-xs bg-opta-sky/40 text-opta-grey ms-1">{{ __('Creator global') }}</span>
                                    @endif
                                    @if (! $u->is_platform_admin && ! $u->can_create_groups)
                                        <span class="text-opta-grey/70">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                                    @can('update', $u)
                                        <a href="{{ route('admin.users.edit', $u) }}" class="text-opta-teal-dark hover:text-opta-teal-light">{{ __('Bearbeiten') }}</a>
                                    @endcan
                                    @can('delete', $u)
                                        <form class="inline" method="post" action="{{ route('admin.users.destroy', $u) }}" onsubmit="return confirm(@json(__('Benutzer wirklich löschen?')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-opta-berry hover:underline">{{ __('Löschen') }}</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-2">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
