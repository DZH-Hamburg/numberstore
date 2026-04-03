@php
    use App\Models\Element;
    use App\Enums\ElementType;
    use App\Enums\GroupMembershipRole;

    $openInviteModal = $errors->has('email') || $errors->has('role');
    $elementsFilterPayload = $group->elements->map(fn ($el) => [
        'name' => $el->name,
        'type' => $el->type->value,
    ])->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4 text-left" x-data="{}">
            <div class="flex flex-wrap items-center gap-x-6 gap-y-2">
                <h2 class="text-xl font-semibold text-opta-teal-dark leading-tight">
                    {{ $group->name }}
                </h2>
                <nav class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm font-medium" aria-label="{{ __('Gruppenaktionen') }}">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-md p-1 text-opta-teal-dark hover:bg-opta-teal-light/15 hover:text-opta-teal-light"
                        aria-label="{{ __('Mitglieder') }}"
                        title="{{ __('Mitglieder') }}"
                        @click="$dispatch('open-group-members-modal')"
                    >
                        <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 1 1 -6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1 -5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </button>
                    @can('create', [Element::class, $group])
                        <a
                            href="{{ route('groups.elements.create', $group) }}"
                            class="text-opta-teal-dark hover:text-opta-teal-light text-base font-semibold min-w-[1.25rem] inline-flex items-center justify-center"
                            aria-label="{{ __('Element anlegen') }}"
                            title="{{ __('Element anlegen') }}"
                        >+</a>
                    @endcan
                </nav>
            </div>
            @can('delete', $group)
                <form method="post" action="{{ route('groups.destroy', $group) }}" onsubmit="return confirm(@json(__('Gruppe wirklich löschen?')));">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-sm font-medium text-opta-berry hover:underline">{{ __('Gruppe löschen') }}</button>
                </form>
            @endcan
        </div>
    </x-slot>

    <div
        class="py-10"
        x-data="{
            membersModalOpen: false,
            inviteModalOpen: {{ $openInviteModal ? 'true' : 'false' }},
            elementFilter: '',
            elementTypeFilter: 'all',
            elementsForFilter: @js($elementsFilterPayload),
            elementMatches(name, type) {
                const q = this.elementFilter.trim().toLowerCase();
                if (this.elementTypeFilter !== 'all' && type !== this.elementTypeFilter) {
                    return false;
                }
                if (! q) {
                    return true;
                }
                return String(name).toLowerCase().includes(q);
            },
            noElementMatches() {
                if (! this.elementsForFilter.length) {
                    return false;
                }
                return ! this.elementsForFilter.some((item) => this.elementMatches(item.name, item.type));
            },
        }"
        @open-group-members-modal.window="membersModalOpen = true"
        @open-group-invite-modal.window="inviteModalOpen = true"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-opta-green/40 bg-opta-green/10 px-4 py-3 text-opta-grey text-left" role="status">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Mitglieder-Modal --}}
            <div
                x-show="membersModalOpen"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
                @keydown.escape.window="membersModalOpen = false"
                role="dialog"
                aria-modal="true"
                aria-labelledby="group-members-title"
            >
                <div class="relative max-w-lg w-full max-h-[90vh] bg-white rounded-xl shadow-lg overflow-hidden flex flex-col" @click.outside="membersModalOpen = false">
                    <div class="flex items-center justify-between gap-2 px-5 py-4 border-b border-opta-teal-light/30 shrink-0">
                        <h3 id="group-members-title" class="text-lg font-semibold text-opta-teal-dark inline-flex items-center gap-2">
                            <svg class="w-6 h-6 shrink-0 text-opta-teal-dark/90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 1 1 -6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1 -5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                            {{ __('Mitglieder') }}
                        </h3>
                        <button type="button" class="text-sm text-opta-grey hover:text-opta-teal-dark" @click="membersModalOpen = false">{{ __('Schließen') }}</button>
                    </div>
                    <div class="overflow-y-auto p-5 space-y-5">
                        <ul class="space-y-3">
                            @foreach ($group->users as $member)
                                <li class="flex flex-wrap items-center justify-between gap-2 text-opta-grey text-sm">
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
                        @can('inviteMembers', $group)
                            <div class="pt-4 border-t border-opta-teal-light/30">
                                <button
                                    type="button"
                                    class="text-sm font-medium text-opta-teal-dark hover:text-opta-teal-light"
                                    @click="membersModalOpen = false; $dispatch('open-group-invite-modal')"
                                >
                                    {{ __('Einladung') }}
                                </button>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>

            @can('inviteMembers', $group)
                {{-- Einladungs-Modal (gleiche Karten-Optik wie Mitglieder, damit Formularfelder nicht auf Transparenz liegen) --}}
                <div
                    x-show="inviteModalOpen"
                    x-cloak
                    class="fixed inset-0 z-50 overflow-y-auto"
                    @keydown.escape.window="inviteModalOpen = false"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="group-invite-title"
                >
                    <div
                        class="fixed inset-0 bg-black/50"
                        aria-hidden="true"
                        @click="inviteModalOpen = false"
                    ></div>
                    <div class="flex min-h-full items-end justify-center p-4 sm:items-center sm:p-6 pointer-events-none">
                        <div
                            class="pointer-events-auto relative my-8 w-full max-w-lg max-h-[min(90vh,calc(100%-2rem))] overflow-y-auto rounded-xl border border-opta-teal-light/30 bg-white shadow-lg outline-none flex flex-col"
                            @click.outside="inviteModalOpen = false"
                        >
                            <div class="flex shrink-0 items-center justify-between gap-2 border-b border-opta-teal-light/30 px-5 py-4">
                                <h3 id="group-invite-title" class="inline-flex items-center gap-2 text-lg font-semibold text-opta-teal-dark">
                                    <svg class="h-6 w-6 shrink-0 text-opta-teal-dark/90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                    {{ __('Einladung') }}
                                </h3>
                                <button type="button" class="text-sm text-opta-grey hover:text-opta-teal-dark" @click="inviteModalOpen = false">{{ __('Schließen') }}</button>
                            </div>
                            <div class="space-y-4 p-5">
                                <form method="post" action="{{ route('groups.invitations.store', $group) }}" class="space-y-3 text-left">
                                    @csrf
                                    <div>
                                        <x-input-label for="invite_email" :value="__('E-Mail')" />
                                        <x-text-input id="invite_email" name="email" type="email" class="mt-1 block w-full border-opta-teal-light/50 focus:border-opta-teal-dark focus:ring-opta-teal-dark" :value="old('email')" required autocomplete="email" />
                                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label for="invite_role" :value="__('Rolle')" />
                                        <select id="invite_role" name="role" class="mt-1 block w-full rounded-md border-opta-teal-light/50 shadow-sm focus:border-opta-teal-dark focus:ring-opta-teal-dark" required>
                                            @foreach (GroupMembershipRole::cases() as $roleCase)
                                                <option value="{{ $roleCase->value }}" @selected(old('role') === $roleCase->value)>{{ $roleCase->value }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                                    </div>
                                    <x-primary-button>{{ __('Einladung senden') }}</x-primary-button>
                                </form>

                                @if ($group->invitations->isNotEmpty())
                                    <div class="border-t border-opta-teal-light/30 pt-4">
                                        <p class="text-sm font-medium text-opta-teal-dark mb-2">{{ __('Ausstehende Einladungen') }}</p>
                                        <ul class="space-y-2">
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
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endcan

            <section class="text-left">
                @if ($group->elements->isEmpty())
                    <p class="text-opta-grey rounded-xl border border-opta-teal-light/30 bg-white p-6 shadow-sm">{{ __('Noch keine Elemente.') }}</p>
                @else
                    <div class="space-y-6">
                        <div class="flex flex-wrap items-end gap-3 rounded-xl border border-opta-teal-light/30 bg-white p-4 shadow-sm">
                            <div class="flex-1 min-w-[12rem]">
                                <x-input-label for="element_filter" class="text-xs text-opta-grey uppercase tracking-wide" :value="__('Suche')" />
                                <x-text-input
                                    id="element_filter"
                                    type="search"
                                    class="mt-1 block w-full"
                                    x-model="elementFilter"
                                    placeholder="{{ __('Name …') }}"
                                    autocomplete="off"
                                />
                            </div>
                            <div class="w-full sm:w-48">
                                <x-input-label for="element_type_filter" class="text-xs text-opta-grey uppercase tracking-wide" :value="__('Typ')" />
                                <select
                                    id="element_type_filter"
                                    x-model="elementTypeFilter"
                                    class="mt-1 block w-full rounded-md border-opta-teal-light/50 shadow-sm focus:border-opta-teal-dark focus:ring-opta-teal-dark text-sm"
                                >
                                    <option value="all">{{ __('Alle Typen') }}</option>
                                    @foreach (ElementType::cases() as $typeCase)
                                        <option value="{{ $typeCase->value }}">{{ $typeCase->value }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <p x-show="noElementMatches()" x-cloak class="text-opta-grey text-center py-10 rounded-xl border border-dashed border-opta-teal-light/40 bg-white/80">
                            {{ __('Keine Elemente passen zum Filter.') }}
                        </p>

                        <div class="grid gap-6 sm:grid-cols-2">
                            @foreach ($group->elements as $el)
                                <article
                                    x-show="elementMatches(@js($el->name), @js($el->type->value))"
                                    class="bg-white border border-opta-teal-light/30 rounded-xl p-5 shadow-sm flex flex-col gap-4 min-h-[8rem]"
                                >
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <h4 class="font-medium text-opta-teal-dark text-base">{{ $el->name }}</h4>
                                            <p class="text-sm text-opta-grey mt-1">
                                                {{ $el->type->value }} — API Consumer: {{ $el->pivot->consumer_can_read_via_api ? __('ja') : __('nein') }}
                                            </p>
                                        </div>
                                        @can('update', $el)
                                            <div class="flex flex-wrap gap-3 text-sm shrink-0">
                                                <a href="{{ route('groups.elements.edit', [$group, $el]) }}" class="text-opta-teal-dark hover:text-opta-teal-light">{{ __('Bearbeiten') }}</a>
                                                <form method="post" action="{{ route('groups.elements.destroy', [$group, $el]) }}" onsubmit="return confirm(@json(__('Element von dieser Gruppe entfernen?')));">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-opta-berry hover:underline">{{ __('Entfernen') }}</button>
                                                </form>
                                            </div>
                                        @endcan
                                    </div>

                                    @if ($el->type === ElementType::Screenshot)
                                        @php
                                            $thumbSrc = $el->hasStoredScreenshot()
                                                ? route('groups.elements.screenshot.show', [$group, $el]).'?t='.$el->last_screenshot_at?->getTimestamp()
                                                : '';
                                            $screenshotClientConfig = [
                                                'previousAt' => $el->last_screenshot_at?->toIso8601String(),
                                                'postUrl' => route('groups.elements.screenshot.store', [$group, $el]),
                                                'metaUrl' => route('groups.elements.screenshot.meta', [$group, $el]),
                                                'showUrl' => route('groups.elements.screenshot.show', [$group, $el]),
                                                'imgSrc' => $thumbSrc,
                                                'strings' => [
                                                    'running' => __('Screenshot wird erstellt'),
                                                    'done' => __('Screenshot fertig.'),
                                                    'error' => __('Screenshot fehlgeschlagen.'),
                                                    'timeout' => __('Zeitüberschreitung beim Screenshot.'),
                                                ],
                                            ];
                                        @endphp
                                        <div
                                            class="flex flex-wrap items-center gap-3 pt-1 border-t border-opta-teal-light/20"
                                            x-data="screenshotCapture(@js($screenshotClientConfig))"
                                        >
                                            <div class="h-24 min-w-[180px] max-w-[220px] shrink-0 flex items-center justify-center">
                                                <div
                                                    x-show="busy"
                                                    x-cloak
                                                    class="h-24 w-full flex items-center justify-center rounded-lg ring-1 ring-opta-teal-light/40 bg-opta-teal-light/10"
                                                    role="status"
                                                    :aria-label="strings.running"
                                                >
                                                    <svg class="h-8 w-8 animate-spin text-opta-teal-dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </div>
                                                <button
                                                    type="button"
                                                    x-show="imgSrc && !busy"
                                                    x-cloak
                                                    class="rounded-lg ring-1 ring-opta-teal-light/40 overflow-hidden hover:ring-opta-teal-dark focus:outline-none focus:ring-2 focus:ring-opta-teal-dark"
                                                    @click="modalOpen = true"
                                                    title="{{ __('Letzten Screenshot anzeigen') }}"
                                                >
                                                    <img :src="imgSrc" alt="" class="h-24 w-auto max-w-[220px] object-cover object-top bg-opta-teal-light/10 block" height="96" />
                                                </button>
                                                <span x-show="!imgSrc && !busy" class="text-xs text-opta-grey whitespace-normal text-center px-1">{{ __('Noch kein Screenshot') }}</span>
                                            </div>
                                            @can('update', $el)
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center justify-center rounded-lg p-2 text-opta-teal-dark hover:bg-opta-teal-light/20 disabled:opacity-40 disabled:cursor-not-allowed"
                                                    :disabled="busy"
                                                    @click="capture()"
                                                    title="{{ __('Neuen Screenshot erstellen') }}"
                                                >
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.864 47.864 0 00-1.068-.63 2.458 2.458 0 01-1.03-2.753 2.318 2.318 0 00-.643-2.008 2.316 2.316 0 00-2.864-.166 47.876 47.876 0 00-1.15-.732 2.25 2.25 0 00-2.262 0 47.809 47.809 0 00-1.15.732 2.318 2.318 0 00-.643 2.007 2.458 2.458 0 01-1.03 2.753c-.307.182-.612.38-.904.593a2.25 2.25 0 00-1.263 2.652A48.55 48.55 0 019.375 9.116a2.25 2.25 0 012.099-.914h.006z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                </button>
                                            @endcan
                                            <span
                                                class="text-xs text-opta-grey max-w-full flex-1 min-w-[8rem] inline-flex items-baseline gap-0"
                                                x-show="status || busy"
                                                aria-live="polite"
                                            >
                                                <span x-show="busy" class="inline-flex items-baseline">
                                                    <span x-text="strings.running"></span>
                                                    <span class="screenshot-running-dots" aria-hidden="true"><span>.</span><span>.</span><span>.</span></span>
                                                </span>
                                                <span x-show="!busy && status" x-text="status"></span>
                                            </span>
                                            <div
                                                x-show="modalOpen"
                                                x-cloak
                                                class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50"
                                                @keydown.escape.window="modalOpen = false"
                                                role="dialog"
                                                aria-modal="true"
                                            >
                                                <div class="relative max-w-4xl max-h-[90vh] w-full bg-white rounded-xl shadow-lg overflow-hidden flex flex-col" @click.away="modalOpen = false">
                                                    <div class="flex items-center justify-between gap-2 px-4 py-3 border-b border-opta-teal-light/30">
                                                        <p class="text-sm font-medium text-opta-teal-dark">{{ __('Letzter Screenshot') }} — {{ $el->name }}</p>
                                                        <div class="flex items-center gap-2">
                                                            <a
                                                                :href="downloadUrl()"
                                                                class="text-sm font-medium text-opta-teal-dark hover:text-opta-teal-light"
                                                            >{{ __('Herunterladen') }}</a>
                                                            <button type="button" class="text-sm text-opta-grey hover:text-opta-teal-dark" @click="modalOpen = false">{{ __('Schließen') }}</button>
                                                        </div>
                                                    </div>
                                                    <div class="overflow-auto p-4 bg-opta-teal-light/10 min-h-[120px]">
                                                        <img x-show="imgSrc" x-cloak :src="imgSrc" alt="" class="max-w-full h-auto mx-auto rounded border border-opta-teal-light/30" />
                                                        <p x-show="!imgSrc" class="text-sm text-opta-grey text-center py-8">{{ __('Noch kein Screenshot vorhanden.') }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
