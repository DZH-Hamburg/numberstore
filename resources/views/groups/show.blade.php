@php
    use App\Models\Element;
    use App\Enums\GroupMembershipRole;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4 text-left">
            <h2 class="text-xl font-semibold text-opta-teal-dark leading-tight">
                {{ $group->name }}
            </h2>
            @can('delete', $group)
                <form method="post" action="{{ route('groups.destroy', $group) }}" onsubmit="return confirm(@json(__('Gruppe wirklich löschen?')));">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-opta-berry hover:underline">{{ __('Gruppe löschen') }}</button>
                </form>
            @endcan
        </div>
    </x-slot>

    <div class="py-10 space-y-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-opta-green/40 bg-opta-green/10 px-4 py-3 text-opta-grey text-left" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid gap-6 lg:grid-cols-2 text-left">
                <section class="bg-white border border-opta-teal-light/30 rounded-xl p-5 shadow-sm">
                    <h3 class="text-lg font-semibold text-opta-teal-dark mb-3">{{ __('Mitglieder') }}</h3>
                    <ul class="space-y-2">
                        @foreach ($group->users as $member)
                            <li class="flex flex-wrap items-center justify-between gap-2 text-opta-grey">
                                <span>{{ $member->name }} <span class="text-opta-grey/70">({{ $member->pivot->role->value }})</span></span>
                                @can('detachMembers', $group)
                                    @if ($member->id !== auth()->id())
                                        <form method="post" action="{{ route('groups.members.destroy', [$group, $member]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-opta-berry hover:underline">{{ __('Entfernen') }}</button>
                                        </form>
                                    @endif
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                </section>

                @can('inviteMembers', $group)
                    <section class="bg-opta-sky/10 border border-opta-sky/30 rounded-xl p-5 shadow-sm">
                        <h3 class="text-lg font-semibold text-opta-teal-dark mb-3">{{ __('Einladung') }}</h3>
                        <form method="post" action="{{ route('groups.invitations.store', $group) }}" class="space-y-3">
                            @csrf
                            <div>
                                <x-input-label for="email" :value="__('E-Mail')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                                <x-input-error :messages="$errors->get('email')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="role" :value="__('Rolle')" />
                                <select id="role" name="role" class="mt-1 block w-full rounded-md border-opta-teal-light/50 shadow-sm focus:border-opta-teal-dark focus:ring-opta-teal-dark" required>
                                    @foreach (GroupMembershipRole::cases() as $roleCase)
                                        <option value="{{ $roleCase->value }}" @selected(old('role') === $roleCase->value)>{{ $roleCase->value }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('role')" class="mt-2" />
                            </div>
                            <x-primary-button>{{ __('Einladung senden') }}</x-primary-button>
                        </form>

                        @if ($group->invitations->isNotEmpty())
                            <ul class="mt-4 space-y-2 border-t border-opta-teal-light/30 pt-4">
                                @foreach ($group->invitations as $inv)
                                    <li class="flex flex-wrap justify-between gap-2 text-sm text-opta-grey">
                                        <span>{{ $inv->email }} — {{ $inv->role->value }}</span>
                                        <form method="post" action="{{ route('groups.invitations.destroy', [$group, $inv]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-opta-berry hover:underline">{{ __('Zurückziehen') }}</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endcan
            </div>

            <section class="bg-white border border-opta-teal-light/30 rounded-xl p-5 shadow-sm text-left">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-opta-teal-dark">{{ __('Elemente') }}</h3>
                    @can('create', [Element::class, $group])
                        <a href="{{ route('groups.elements.create', $group) }}" class="text-sm font-medium text-opta-teal-dark hover:text-opta-teal-light">{{ __('Element anlegen') }}</a>
                    @endcan
                </div>
                @if ($group->elements->isEmpty())
                    <p class="text-opta-grey">{{ __('Noch keine Elemente.') }}</p>
                @else
                    <ul class="divide-y divide-opta-teal-light/30">
                        @foreach ($group->elements as $el)
                            <li class="py-3 flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-opta-teal-dark">{{ $el->name }}</p>
                                    <p class="text-sm text-opta-grey">{{ $el->type->value }} — API Consumer: {{ $el->pivot->consumer_can_read_via_api ? __('ja') : __('nein') }}</p>
                                </div>
                                @can('update', $el)
                                    <div class="flex gap-3 text-sm">
                                        <a href="{{ route('groups.elements.edit', [$group, $el]) }}" class="text-opta-teal-dark hover:text-opta-teal-light">{{ __('Bearbeiten') }}</a>
                                        <form method="post" action="{{ route('groups.elements.destroy', [$group, $el]) }}" onsubmit="return confirm(@json(__('Element von dieser Gruppe entfernen?')));">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-opta-berry hover:underline">{{ __('Entfernen') }}</button>
                                        </form>
                                    </div>
                                @endcan
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
