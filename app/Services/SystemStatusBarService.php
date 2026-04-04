<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

class SystemStatusBarService
{
    private const CACHE_HOST = 'system_status:host_metrics';

    private const CACHE_SCHEDULER = 'system_status:scheduler_heartbeat';

    private const CACHE_QUEUE_MINUTE_PREFIX = 'system_status:queue_success_min:';

    /**
     * @return array{
     *     load: string|null,
     *     ram: string|null,
     *     queue_worker_running: bool|null,
     *     scheduler_active: bool,
     * }
     */
    public function statusBarSnapshot(): array
    {
        $host = Cache::remember(self::CACHE_HOST, 5, fn (): array => $this->collectHostMetrics());

        return [
            'load' => $host['load'],
            'ram' => $host['ram'],
            'queue_worker_running' => $this->detectQueueWorkerRunning(),
            'scheduler_active' => $this->schedulerHeartbeatFresh(),
        ];
    }

    /**
     * @return array{
     *     default_connection: string,
     *     queue_worker_running: bool|null,
     *     jobs_pending: int|null,
     *     jobs_by_queue: list<array{queue: string, count: int}>,
     *     jobs_failed_total: int|null,
     *     jobs_failed_last_hour: int|null,
     *     jobs_succeeded_last_hour: int,
     *     batches_open: int|null,
     *     recent_failed_jobs: list<array{id: int, uuid: string, connection: string, queue: string, failed_at: string|null, exception_preview: string}>,
     * }
     */
    public function queueWorkerReport(): array
    {
        return [
            'default_connection' => (string) config('queue.default'),
            'queue_worker_running' => $this->detectQueueWorkerRunning(),
            'jobs_pending' => $this->safeJobsPendingCount(),
            'jobs_by_queue' => $this->jobsPendingByQueue(),
            'jobs_failed_total' => $this->safeFailedJobsCount(),
            'jobs_failed_last_hour' => $this->safeFailedJobsLastHourCount(),
            'jobs_succeeded_last_hour' => $this->queueSuccessesLastRollingHour(),
            'batches_open' => $this->safeOpenBatchesCount(),
            'recent_failed_jobs' => $this->recentFailedJobsPreview(25),
        ];
    }

    /**
     * @return array{load: string|null, ram: string|null}
     */
    private function collectHostMetrics(): array
    {
        $load = $this->formatLoadAverage();
        $ram = $this->formatMemoryUsage();

        return [
            'load' => $load,
            'ram' => $ram,
        ];
    }

    private function formatLoadAverage(): ?string
    {
        if (! function_exists('sys_getloadavg')) {
            return null;
        }

        $avg = sys_getloadavg();
        if ($avg === false || ! is_array($avg) || count($avg) < 1) {
            return null;
        }

        return sprintf('%.2f / %.2f / %.2f', $avg[0], $avg[1] ?? $avg[0], $avg[2] ?? $avg[0]);
    }

    private function formatMemoryUsage(): ?string
    {
        if (is_readable('/proc/meminfo')) {
            return $this->memoryFromProcMeminfo();
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            return $this->memoryFromDarwin();
        }

        return null;
    }

    private function memoryFromProcMeminfo(): ?string
    {
        $raw = @file_get_contents('/proc/meminfo');
        if ($raw === false) {
            return null;
        }

        $kb = [];
        foreach (explode("\n", $raw) as $line) {
            if (preg_match('/^(\w+):\s+(\d+)\s+kB$/', $line, $m)) {
                $kb[$m[1]] = (int) $m[2];
            }
        }

        $totalKb = $kb['MemTotal'] ?? null;
        if ($totalKb === null || $totalKb <= 0) {
            return null;
        }

        $availKb = $kb['MemAvailable'] ?? $kb['MemFree'] ?? null;
        if ($availKb === null) {
            return $this->formatBytesPair($totalKb * 1024, null);
        }

        $usedKb = max(0, $totalKb - $availKb);
        $pct = (int) round(($usedKb / $totalKb) * 100);

        return sprintf('%d %% belegt (%s / %s)', $pct, $this->humanBytes($usedKb * 1024), $this->humanBytes($totalKb * 1024));
    }

    private function memoryFromDarwin(): ?string
    {
        $total = $this->sysctlInt('hw.memsize');
        if ($total === null || $total <= 0) {
            return null;
        }

        $vmStat = $this->runShell('/usr/bin/vm_stat 2>/dev/null');
        if ($vmStat === null) {
            return $this->formatBytesPair($total, null);
        }

        $pageSize = 4096;
        if (preg_match('/page size of (\d+) bytes/', $vmStat, $m)) {
            $pageSize = (int) $m[1];
        }

        $freePages = 0;
        if (preg_match('/Pages free:\s+([\d.]+)/', $vmStat, $m)) {
            $freePages = (int) str_replace('.', '', $m[1]);
        }

        $inactivePages = 0;
        if (preg_match('/Pages inactive:\s+([\d.]+)/', $vmStat, $m)) {
            $inactivePages = (int) str_replace('.', '', $m[1]);
        }

        $speculativePages = 0;
        if (preg_match('/Pages speculative:\s+([\d.]+)/', $vmStat, $m)) {
            $speculativePages = (int) str_replace('.', '', $m[1]);
        }

        $approxFreeBytes = ($freePages + $inactivePages + $speculativePages) * $pageSize;
        $approxFreeBytes = min($total, max(0, $approxFreeBytes));
        $used = max(0, $total - $approxFreeBytes);
        $pct = (int) round(($used / $total) * 100);

        return sprintf('%d %% belegt (~%s / %s)', $pct, $this->humanBytes($used), $this->humanBytes($total));
    }

    private function sysctlInt(string $name): ?int
    {
        $out = $this->runShell('/usr/sbin/sysctl -n '.escapeshellarg($name).' 2>/dev/null');
        if ($out === null) {
            return null;
        }

        $v = trim($out);

        return ctype_digit($v) ? (int) $v : null;
    }

    private function runShell(string $command): ?string
    {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(2);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $out = trim($process->getOutput());

        return $out === '' ? null : $out;
    }

    private function formatBytesPair(int $total, ?int $used): string
    {
        if ($used === null) {
            return $this->humanBytes($total).' gesamt';
        }

        $pct = (int) round(($used / $total) * 100);

        return sprintf('%d %% belegt (%s / %s)', $pct, $this->humanBytes($used), $this->humanBytes($total));
    }

    private function humanBytes(int $bytes): string
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
        $v = (float) $bytes;
        $u = 0;
        while ($v >= 1024 && $u < count($units) - 1) {
            $v /= 1024;
            $u++;
        }

        return $u === 0 ? sprintf('%d %s', $bytes, $units[0]) : sprintf('%.1f %s', $v, $units[$u]);
    }

    private function detectQueueWorkerRunning(): ?bool
    {
        $patterns = ['artisan queue:work', 'artisan horizon'];

        foreach ($patterns as $pattern) {
            $process = Process::fromShellCommandline('pgrep -f '.escapeshellarg($pattern).' 2>/dev/null');
            $process->setTimeout(2);
            $process->run();

            $exit = $process->getExitCode();
            $out = trim($process->getOutput());

            if ($exit === 0 && $out !== '') {
                return true;
            }

            if ($exit !== 1 && $exit !== null) {
                return null;
            }
        }

        return false;
    }

    private function schedulerHeartbeatFresh(): bool
    {
        $ts = Cache::get(self::CACHE_SCHEDULER);

        if (! is_int($ts) && ! is_numeric($ts)) {
            return false;
        }

        $ts = (int) $ts;

        return (time() - $ts) <= 120;
    }

    private function safeJobsPendingCount(): ?int
    {
        if (! Schema::hasTable('jobs')) {
            return null;
        }

        try {
            return (int) DB::table('jobs')->count();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return list<array{queue: string, count: int}>
     */
    private function jobsPendingByQueue(): array
    {
        if (! Schema::hasTable('jobs')) {
            return [];
        }

        try {
            $rows = DB::table('jobs')
                ->select('queue', DB::raw('count(*) as aggregate'))
                ->groupBy('queue')
                ->orderByDesc('aggregate')
                ->get();

            return $rows->map(fn ($r): array => [
                'queue' => (string) $r->queue,
                'count' => (int) $r->aggregate,
            ])->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function safeOpenBatchesCount(): ?int
    {
        if (! Schema::hasTable('job_batches')) {
            return null;
        }

        try {
            return (int) DB::table('job_batches')
                ->where('pending_jobs', '>', 0)
                ->whereNull('cancelled_at')
                ->whereNull('finished_at')
                ->count();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return list<array{id: int, uuid: string, connection: string, queue: string, failed_at: string|null, exception_preview: string}>
     */
    private function recentFailedJobsPreview(int $limit): array
    {
        if (! Schema::hasTable('failed_jobs')) {
            return [];
        }

        try {
            $rows = DB::table('failed_jobs')
                ->orderByDesc('failed_at')
                ->limit($limit)
                ->get(['id', 'uuid', 'connection', 'queue', 'failed_at', 'exception']);
        } catch (\Throwable) {
            return [];
        }

        return $rows->map(function ($r): array {
            $ex = (string) ($r->exception ?? '');
            $preview = mb_strlen($ex) > 400 ? mb_substr($ex, 0, 400).'…' : $ex;
            $preview = preg_replace("/\s+/u", ' ', $preview) ?? $preview;

            return [
                'id' => (int) $r->id,
                'uuid' => (string) $r->uuid,
                'connection' => (string) $r->connection,
                'queue' => (string) $r->queue,
                'failed_at' => $r->failed_at !== null ? (string) $r->failed_at : null,
                'exception_preview' => $preview,
            ];
        })->all();
    }

    private function safeFailedJobsCount(): ?int
    {
        if (! Schema::hasTable('failed_jobs')) {
            return null;
        }

        try {
            return (int) DB::table('failed_jobs')->count();
        } catch (\Throwable) {
            return null;
        }
    }

    private function safeFailedJobsLastHourCount(): ?int
    {
        if (! Schema::hasTable('failed_jobs')) {
            return null;
        }

        try {
            return (int) DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subHour())
                ->count();
        } catch (\Throwable) {
            return null;
        }
    }

    private function queueSuccessesLastRollingHour(): int
    {
        $sum = 0;

        for ($i = 0; $i < 60; $i++) {
            $minute = now()->subMinutes($i)->format('YmdHi');
            $sum += (int) Cache::get(self::CACHE_QUEUE_MINUTE_PREFIX.$minute, 0);
        }

        return $sum;
    }

    public static function incrementQueueSuccessForCurrentMinute(): void
    {
        $key = self::CACHE_QUEUE_MINUTE_PREFIX.now()->format('YmdHi');
        $value = (int) Cache::get($key, 0);
        Cache::put($key, $value + 1, now()->addHours(2));
    }
}
