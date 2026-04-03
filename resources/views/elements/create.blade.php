@php
    use App\Enums\ElementType;
    $selectedElementType = old('type', ElementType::Scrape->value);
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
                <form method="post" action="{{ route('groups.elements.store', $group) }}" class="space-y-4" x-data="{ type: @js($selectedElementType) }">
                    @csrf
                    @if ($errors->any())
                        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                            <p class="font-medium">{{ __('Bitte Eingaben prüfen.') }}</p>
                            <ul class="mt-2 list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div>
                        <x-input-label for="type" :value="__('Typ')" />
                        <select id="type" name="type" x-model="type" class="mt-1 block w-full rounded-md border-opta-teal-light/50 shadow-sm focus:border-opta-teal-dark focus:ring-opta-teal-dark" required>
                            @foreach (ElementType::cases() as $t)
                                <option value="{{ $t->value }}" @selected($selectedElementType === $t->value)>{{ $t->value }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('type')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <fieldset class="rounded-lg border border-opta-teal-light/30 p-4 space-y-3 transition-opacity" :class="type !== '{{ ElementType::Screenshot->value }}' ? 'opacity-50' : ''">
                        <legend class="px-2 text-sm font-medium text-opta-teal-dark">{{ __('Screenshot') }}</legend>
                        <div>
                            <x-input-label for="screenshot_url" :value="__('URL')" />
                            <x-text-input id="screenshot_url" name="config[url]" type="url" class="mt-1 block w-full" :value="old('config.url')" placeholder="https://example.com" />
                            <x-input-error :messages="$errors->get('config.url')" class="mt-2" />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <x-input-label for="screenshot_username" class="inline-flex items-center gap-1">
                                    <span>{{ __('Benutzer (optional)') }}</span>
                                    <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('Log-in-Benutzername oder E-Mail, falls die Seite eine Anmeldung braucht. Wird verschlüsselt gespeichert.') }}">ⓘ</span>
                                </x-input-label>
                                <x-text-input id="screenshot_username" name="secrets[username]" type="text" class="mt-1 block w-full" :value="old('secrets.username')" autocomplete="off" />
                                <x-input-error :messages="$errors->get('secrets.username')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="screenshot_password" class="inline-flex items-center gap-1">
                                    <span>{{ __('Kennwort (optional)') }}</span>
                                    <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('Passwort für die Anmeldung. Wird verschlüsselt gespeichert und nie in der API ausgegeben.') }}">ⓘ</span>
                                </x-input-label>
                                <x-text-input id="screenshot_password" name="secrets[password]" type="password" class="mt-1 block w-full" :value="old('secrets.password')" autocomplete="off" />
                                <x-input-error :messages="$errors->get('secrets.password')" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="screenshot_totp" class="inline-flex items-center gap-1">
                                <span>{{ __('TOTP-Secret (Base32, optional)') }}</span>
                                <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('Geheimer Schlüssel im Base32-Format (z. B. aus dem Authenticator). Wird verschlüsselt gespeichert; der 6-stellige Code wird auf dem Server erzeugt und nur an Playwright übergeben.') }}">ⓘ</span>
                            </x-input-label>
                            <x-text-input id="screenshot_totp" name="secrets[totp_secret]" type="password" class="mt-1 block w-full" :value="old('secrets.totp_secret')" autocomplete="off" />
                            <x-input-error :messages="$errors->get('secrets.totp_secret')" class="mt-2" />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <x-input-label for="sel_username" class="inline-flex items-center gap-1">
                                    <span>{{ __('Selector: Username (optional)') }}</span>
                                    <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('CSS-Selektor des Benutzerfelds, z. B. #email oder input[name=user]. Wenn ein Benutzer gespeichert ist und das Feld leer bleibt, werden gängige Login-Felder automatisch versucht.') }}">ⓘ</span>
                                </x-input-label>
                                <x-text-input id="sel_username" name="config[selectors][username]" type="text" class="mt-1 block w-full font-mono text-xs" :value="old('config.selectors.username')" placeholder="#email" />
                            </div>
                            <div>
                                <x-input-label for="sel_password" class="inline-flex items-center gap-1">
                                    <span>{{ __('Selector: Password (optional)') }}</span>
                                    <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('CSS-Selektor des Passwortfelds. Wenn leer und Kennwort gespeichert: input[type=password]. Erster Login-Schritt, danach ggf. TOTP.') }}">ⓘ</span>
                                </x-input-label>
                                <x-text-input id="sel_password" name="config[selectors][password]" type="text" class="mt-1 block w-full font-mono text-xs" :value="old('config.selectors.password')" placeholder="#password" />
                            </div>
                            <div>
                                <x-input-label for="sel_totp" class="inline-flex items-center gap-1">
                                    <span>{{ __('Selector: TOTP (optional)') }}</span>
                                    <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('CSS-Selektor des Einmalcode-Felds nach dem ersten Anmeldeschritt. Leer lassen: gängige OTP-Felder werden versucht.') }}">ⓘ</span>
                                </x-input-label>
                                <x-text-input id="sel_totp" name="config[selectors][totp]" type="text" class="mt-1 block w-full font-mono text-xs" :value="old('config.selectors.totp')" placeholder="#totp" />
                            </div>
                            <div>
                                <x-input-label for="sel_submit" class="inline-flex items-center gap-1">
                                    <span>{{ __('Selector: Submit (optional)') }}</span>
                                    <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('Klick auf Anmelden / Weiter, z. B. button[type=submit] oder #login-button.') }}">ⓘ</span>
                                </x-input-label>
                                <x-text-input id="sel_submit" name="config[selectors][submit]" type="text" class="mt-1 block w-full font-mono text-xs" :value="old('config.selectors.submit')" placeholder="button[type=submit]" />
                            </div>
                            <div>
                                <x-input-label for="sel_totp_submit" class="inline-flex items-center gap-1">
                                    <span>{{ __('Selector: TOTP bestätigen (optional)') }}</span>
                                    <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('Separater Klick nach dem Einmalcode (z. B. Weiter), falls nicht derselbe Button wie bei der Passwort-Anmeldung. Leer: gleicher Submit wie oben oder erster Submit-Button.') }}">ⓘ</span>
                                </x-input-label>
                                <x-text-input id="sel_totp_submit" name="config[selectors][totp_submit]" type="text" class="mt-1 block w-full font-mono text-xs" :value="old('config.selectors.totp_submit')" placeholder="#verify-otp" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <x-input-label for="wait_for" class="inline-flex items-center gap-1">
                                    <span>{{ __('Wait-for Selector (optional)') }}</span>
                                    <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('Playwright wartet bis dieses Element im DOM sichtbar ist, bevor der Full-Page-Screenshot erstellt wird (z. B. main oder .dashboard).') }}">ⓘ</span>
                                </x-input-label>
                                <x-text-input id="wait_for" name="config[wait_for]" type="text" class="mt-1 block w-full font-mono text-xs" :value="old('config.wait_for')" placeholder="main" />
                            </div>
                            <div>
                                <x-input-label for="timeout_ms" class="inline-flex items-center gap-1">
                                    <span>{{ __('Timeout (ms, optional)') }}</span>
                                    <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('Maximale Wartezeit pro Schritt (Navigation, Selektoren) in Millisekunden. Standard in Playwright: 60000.') }}">ⓘ</span>
                                </x-input-label>
                                <x-text-input id="timeout_ms" name="config[timeout_ms]" type="number" class="mt-1 block w-full" :value="old('config.timeout_ms')" placeholder="60000" min="1" max="300000" />
                            </div>
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <input type="hidden" name="config[full_page]" value="0" />
                            <input id="screenshot_full_page" name="config[full_page]" type="checkbox" value="1" class="rounded border-opta-teal-light text-opta-teal-dark focus:ring-opta-teal-dark" @checked(old('config.full_page', true)) />
                            <x-input-label for="screenshot_full_page" class="!mb-0 inline-flex items-center gap-1">
                                <span>{{ __('Gesamte Seite per Playwright erfassen (inkl. Scrollen für Lazy-Loading)') }}</span>
                                <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('Wenn deaktiviert: nur der aktuell sichtbare Viewport. Wenn aktiv: volle Seitenhöhe; die Seite wird vorher schrittweise gescrollt, damit nachladende Inhalte mit erfasst werden.') }}">ⓘ</span>
                            </x-input-label>
                        </div>
                    </fieldset>
                    <div>
                        <x-input-label for="config" :value="__('Konfiguration (JSON, optional)')" />
                        <textarea id="config" name="config_json" rows="4" class="mt-1 block w-full rounded-md border-opta-teal-light/50 shadow-sm focus:border-opta-teal-dark focus:ring-opta-teal-dark font-mono text-xs" placeholder="{}">{{ old('config_json') }}</textarea>
                        <x-input-error :messages="$errors->get('config')" class="mt-2" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input id="consumer_can_read_via_api" name="consumer_can_read_via_api" type="checkbox" value="1" class="rounded border-opta-teal-light text-opta-teal-dark focus:ring-opta-teal-dark" @checked(old('consumer_can_read_via_api')) />
                        <x-input-label for="consumer_can_read_via_api" class="!mb-0 inline-flex items-center gap-1">
                            <span>{{ __('Consumer darf per API lesen') }}</span>
                            <span class="cursor-help select-none text-opta-teal-dark/60 text-xs font-normal" title="{{ __('Bei Screenshot-Elementen erlaubt Lesen den Download des letzten Screenshots per GET; ein neuer Screenshot wird per POST ausgelöst (Schreiben nur für Gruppen-Ersteller).') }}">ⓘ</span>
                        </x-input-label>
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
