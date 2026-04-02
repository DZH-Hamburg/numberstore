<x-mail::message>
# {{ __('Einladung') }}

{{ __('Du wurdest zur Gruppe **:name** eingeladen (Rolle: :role).', ['name' => $group->name, 'role' => $roleLabel]) }}

<x-mail::button :url="$acceptUrl">
{{ __('Einladung annehmen') }}
</x-mail::button>

{{ __('Der Link ist 14 Tage gültig.') }}

{{ __('Viele Grüße') }}<br>
{{ config('app.name') }}
</x-mail::message>
