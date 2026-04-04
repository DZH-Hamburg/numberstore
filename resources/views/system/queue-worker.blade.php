@php
    /** @var array $queueReport */
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-opta-teal-dark leading-tight">
            Queue-Worker
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm border border-opta-teal-light/30 sm:rounded-xl">
                <div class="p-6 space-y-6 text-left">
                    <p class="text-sm text-opta-grey">
                        Standard-Verbindung: <span class="font-medium text-opta-teal-dark">{{ $queueReport['default_connection'] }}</span>
                        · Erfolgszähler gelten für die letzten 60 Minuten (ohne <code class="text-xs bg-opta-teal-light/15 px-1 rounded">sync</code>-Connection).
                    </p>

                    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div class="rounded-lg border border-opta-teal-light/30 bg-opta-teal-light/5 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-opta-grey">Prozess</dt>
                            <dd class="mt-1 text-lg font-semibold text-opta-teal-dark">
                                @if ($queueReport['queue_worker_running'] === true)
                                    <span class="text-emerald-700">Läuft</span>
                                @elseif ($queueReport['queue_worker_running'] === false)
                                    <span class="text-amber-700">Kein Worker</span>
                                @else
                                    <span class="text-opta-grey">Unbekannt</span>
                                @endif
                            </dd>
                            <dd class="mt-1 text-xs text-opta-grey">Erkannt via <code class="bg-white/80 px-1 rounded">queue:work</code> / <code class="bg-white/80 px-1 rounded">horizon</code></dd>
                        </div>

                        <div class="rounded-lg border border-opta-teal-light/30 bg-opta-teal-light/5 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-opta-grey">Jobs wartend (gesamt)</dt>
                            <dd class="mt-1 text-lg font-semibold text-opta-teal-dark tabular-nums">
                                @if ($queueReport['jobs_pending'] !== null)
                                    {{ $queueReport['jobs_pending'] }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>

                        <div class="rounded-lg border border-opta-teal-light/30 bg-opta-teal-light/5 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-opta-grey">Erfolgreich (60 min)</dt>
                            <dd class="mt-1 text-lg font-semibold text-opta-teal-dark tabular-nums">{{ $queueReport['jobs_succeeded_last_hour'] }}</dd>
                        </div>

                        <div class="rounded-lg border border-opta-teal-light/30 bg-opta-teal-light/5 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-opta-grey">Fehlgeschlagen (gesamt)</dt>
                            <dd class="mt-1 text-lg font-semibold text-opta-teal-dark tabular-nums">
                                @if ($queueReport['jobs_failed_total'] !== null)
                                    {{ $queueReport['jobs_failed_total'] }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>

                        <div class="rounded-lg border border-opta-teal-light/30 bg-opta-teal-light/5 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-opta-grey">Fehlgeschlagen (letzte Stunde)</dt>
                            <dd class="mt-1 text-lg font-semibold text-opta-teal-dark tabular-nums">
                                @if ($queueReport['jobs_failed_last_hour'] !== null)
                                    {{ $queueReport['jobs_failed_last_hour'] }}
                                @else
                                    —
                                @endif
                            </dd>
                        </div>

                        <div class="rounded-lg border border-opta-teal-light/30 bg-opta-teal-light/5 p-4">
                            <dt class="text-xs font-medium uppercase tracking-wide text-opta-grey">Offene Job-Batches</dt>
                            <dd class="mt-1 text-lg font-semibold text-opta-teal-dark tabular-nums">
                                @if ($queueReport['batches_open'] !== null)
                                    {{ $queueReport['batches_open'] }}
                                @else
                                    —
                                @endif
                            </dd>
                            <dd class="mt-1 text-xs text-opta-grey">Mit ausstehenden Jobs, nicht abgebrochen, nicht fertig</dd>
                        </div>
                    </dl>

                    <div>
                        <h3 class="text-base font-semibold text-opta-teal-dark mb-3">Warteschlangen (wartende Jobs)</h3>
                        @if (count($queueReport['jobs_by_queue']) === 0)
                            <p class="text-sm text-opta-grey">Keine wartenden Jobs oder Tabelle nicht verfügbar.</p>
                        @else
                            <div class="overflow-x-auto rounded-lg border border-opta-teal-light/30">
                                <table class="min-w-full text-sm text-left">
                                    <thead class="bg-opta-teal-light/10 text-xs uppercase text-opta-grey">
                                        <tr>
                                            <th class="px-4 py-2 font-medium">Queue</th>
                                            <th class="px-4 py-2 font-medium text-right">Anzahl</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-opta-teal-light/25">
                                        @foreach ($queueReport['jobs_by_queue'] as $row)
                                            <tr>
                                                <td class="px-4 py-2 font-mono text-opta-teal-dark">{{ $row['queue'] }}</td>
                                                <td class="px-4 py-2 text-right tabular-nums font-medium">{{ $row['count'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-base font-semibold text-opta-teal-dark mb-3">Letzte fehlgeschlagene Jobs</h3>
                        @if (count($queueReport['recent_failed_jobs']) === 0)
                            <p class="text-sm text-opta-grey">Keine Einträge in <code class="text-xs bg-opta-teal-light/15 px-1 rounded">failed_jobs</code>.</p>
                        @else
                            <ul class="space-y-4">
                                @foreach ($queueReport['recent_failed_jobs'] as $job)
                                    <li class="rounded-lg border border-opta-teal-light/30 p-4 space-y-2">
                                        <div class="flex flex-wrap items-baseline justify-between gap-2 text-sm">
                                            <span class="font-mono text-xs text-opta-grey">#{{ $job['id'] }} · {{ $job['uuid'] }}</span>
                                            <time class="text-xs text-opta-grey" datetime="{{ $job['failed_at'] }}">{{ $job['failed_at'] }}</time>
                                        </div>
                                        <div class="text-xs text-opta-grey">
                                            <span class="font-medium text-opta-teal-dark">{{ $job['connection'] }}</span>
                                            · Queue <span class="font-mono">{{ $job['queue'] }}</span>
                                        </div>
                                        <pre class="text-xs bg-opta-teal-light/10 text-opta-grey rounded-md p-3 overflow-x-auto whitespace-pre-wrap break-words max-h-48">{{ $job['exception_preview'] }}</pre>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
