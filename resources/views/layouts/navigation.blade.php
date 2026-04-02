@php
    use App\Models\Group;
    use App\Models\User;
@endphp

{{-- Kein backdrop-blur: verhindert unsichtbare Dropdown-Texte in WebKit/Chromium. --}}
<nav x-data="{ open: false }" class="bg-white border-b border-opta-teal-light/40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <x-application-logo class="h-9 w-auto" />
                    </a>
                </div>

                <div class="hidden space-x-6 sm:flex sm:items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @can('create', Group::class)
                        <x-nav-link :href="route('groups.create')" :active="request()->routeIs('groups.create')">
                            {{ __('Gruppe anlegen') }}
                        </x-nav-link>
                    @endcan
                    @can('viewAny', User::class)
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Benutzerverwaltung') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-opta-grey bg-white hover:text-opta-teal-dark focus:outline-none transition ease-in-out duration-150">
                            <img
                                src="{{ Auth::user()->avatarUrl(40) }}"
                                alt=""
                                width="32"
                                height="32"
                                class="me-2 h-8 w-8 rounded-full object-cover border border-opta-teal-light/40"
                            />
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4 text-opta-teal-dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button type="button" @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-opta-teal-dark hover:bg-opta-teal-light/20 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-opta-teal-light/30">
        <div class="pt-2 pb-3 space-y-1 px-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @can('create', Group::class)
                <x-responsive-nav-link :href="route('groups.create')" :active="request()->routeIs('groups.create')">
                    {{ __('Gruppe anlegen') }}
                </x-responsive-nav-link>
            @endcan
            @can('viewAny', User::class)
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Benutzerverwaltung') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <div class="pt-4 pb-1 border-t border-opta-teal-light/30">
            <div class="px-4 flex items-center gap-3">
                <img
                    src="{{ Auth::user()->avatarUrl(48) }}"
                    alt=""
                    width="48"
                    height="48"
                    class="h-12 w-12 shrink-0 rounded-full object-cover border border-opta-teal-light/40"
                />
                <div>
                    <div class="font-medium text-base text-opta-grey">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-opta-grey/80">{{ Auth::user()->email }}</div>
                </div>
            </div>

            <div class="mt-3 space-y-1 px-2">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
