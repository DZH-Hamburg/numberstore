@php
    $raw = session('status');
    $message = null;

    if (is_string($raw) && $raw !== '') {
        $message = match ($raw) {
            'profile-updated', 'password-updated' => __('Saved.'),
            'verification-link-sent' => request()->routeIs('verification.notice')
                ? __('A new verification link has been sent to the email address you provided during registration.')
                : __('A new verification link has been sent to your email address.'),
            default => $raw,
        };
    }
@endphp

@if ($message)
    <div
        x-data="{ show: true }"
        x-show="show"
        x-cloak
        x-init="setTimeout(() => { show = false }, 5000)"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-400"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="pointer-events-none fixed bottom-4 right-4 z-[70] max-w-sm rounded-lg border border-opta-green/40 bg-opta-green/10 px-4 py-3 text-left text-opta-grey shadow-lg shadow-opta-teal-dark/10"
        role="status"
        aria-live="polite"
    >
        <p class="text-sm leading-snug">{{ $message }}</p>
    </div>
@endif
