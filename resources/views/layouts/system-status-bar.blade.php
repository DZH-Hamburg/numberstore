@php
    /** @var array $systemStatus */
    $load = $systemStatus['load'] ?? null;
    $ram = $systemStatus['ram'] ?? null;
    $worker = $systemStatus['queue_worker_running'] ?? null;
    $scheduler = $systemStatus['scheduler_active'] ?? false;
@endphp

<div class="bg-opta-teal-dark text-white/95 text-[11px] sm:text-xs leading-tight border-b border-black/10">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-1.5 flex flex-wrap items-center gap-x-4 gap-y-1">
        <span class="font-semibold text-white tracking-wide uppercase shrink-0">System</span>

        <span class="opacity-90" title="Load Average (1 / 5 / 15 min)">
            Load:
            @if ($load !== null)
                <span class="text-white font-medium tabular-nums">{{ $load }}</span>
            @else
                <span class="text-white/60">—</span>
            @endif
        </span>

        <span class="opacity-90" title="Arbeitsspeicher">
            RAM:
            @if ($ram !== null)
                <span class="text-white font-medium">{{ $ram }}</span>
            @else
                <span class="text-white/60">—</span>
            @endif
        </span>

        <a
            href="{{ route('system.queue-worker') }}"
            class="opacity-90 inline-flex items-center gap-1 rounded px-0.5 -mx-0.5 underline decoration-white/35 underline-offset-2 hover:decoration-white hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70"
            title="Queue-Worker-Details"
        >
            <span>Queue-Worker:</span>
            @if ($worker === true)
                <span class="text-emerald-300 font-medium">läuft</span>
            @elseif ($worker === false)
                <span class="text-amber-300 font-medium">keiner</span>
            @else
                <span class="text-white/60">unbekannt</span>
            @endif
        </a>

        <span class="opacity-90">
            Scheduler:
            @if ($scheduler)
                <span class="text-emerald-300 font-medium">aktiv</span>
            @else
                <span class="text-amber-300 font-medium">inaktiv</span>
            @endif
        </span>
    </div>
</div>
