@props(['user' => null, 'requirePassword' => false, 'showPasswordFields' => true])

<div class="space-y-4 text-left">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user?->name)" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="email" :value="__('E-Mail')" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user?->email)" required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
    @if ($showPasswordFields)
        <div>
            <x-input-label for="password" :value="$requirePassword ? __('Passwort') : __('Neues Passwort (optional)')" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="$requirePassword" autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="password_confirmation" :value="__('Passwort bestätigen')" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="$requirePassword" autocomplete="new-password" />
        </div>
    @endif

    <fieldset class="rounded-lg border border-opta-teal-light/40 p-4 space-y-3">
        <legend class="px-1 text-sm font-semibold text-opta-teal-dark">{{ __('Plattform-Rechte') }}</legend>
        <p class="text-xs text-opta-grey/90">{{ __('Nur Plattform-Admins sehen diese Verwaltung. Gruppenrollen (Creator/Consumer) vergibst du weiterhin pro Gruppe.') }}</p>
        <div class="flex items-center gap-2">
            <input id="is_platform_admin" name="is_platform_admin" type="checkbox" value="1" class="rounded border-opta-teal-light text-opta-teal-dark focus:ring-opta-teal-dark" @checked(old('is_platform_admin', $user?->is_platform_admin)) />
            <x-input-label for="is_platform_admin" :value="__('Plattform-Admin')" class="!mb-0" />
        </div>
        <x-input-error :messages="$errors->get('is_platform_admin')" class="mt-1" />
        <div class="flex items-center gap-2">
            <input id="can_create_groups" name="can_create_groups" type="checkbox" value="1" class="rounded border-opta-teal-light text-opta-teal-dark focus:ring-opta-teal-dark" @checked(old('can_create_groups', $user?->can_create_groups)) />
            <x-input-label for="can_create_groups" :value="__('Darf neue Gruppen anlegen (Creator global)')" class="!mb-0" />
        </div>
    </fieldset>
</div>
