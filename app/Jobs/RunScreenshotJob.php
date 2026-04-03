<?php

namespace App\Jobs;

use App\Models\Element;
use App\Services\TotpService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class RunScreenshotJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $elementId,
    ) {}

    public function handle(): void
    {
        $element = Element::query()->findOrFail($this->elementId);

        $config = is_array($element->config) ? $element->config : [];
        $url = $config['url'] ?? null;
        if (! is_string($url) || trim($url) === '') {
            throw new \RuntimeException('Screenshot element is missing config.url.');
        }

        $selectors = is_array($config['selectors'] ?? null) ? $config['selectors'] : [];

        $secrets = $this->decryptSecrets($element->encrypted_credentials);
        $username = $secrets['username'] ?? null;
        $password = $secrets['password'] ?? null;

        $totpCode = null;
        if (isset($secrets['totp_secret']) && is_string($secrets['totp_secret']) && trim($secrets['totp_secret']) !== '') {
            $totpCode = app(TotpService::class)->now($secrets['totp_secret']);
        }

        $timestamp = Carbon::now()->utc()->format('Ymd_His');
        $relativePath = "screenshots/elements/{$element->id}/{$timestamp}.png";

        $tmpDir = storage_path('app/tmp/screenshots');
        File::ensureDirectoryExists($tmpDir);
        $tmpPath = $tmpDir."/element_{$element->id}_{$timestamp}.png";

        $fullPage = ! array_key_exists('full_page', $config)
            || filter_var($config['full_page'], FILTER_VALIDATE_BOOLEAN);

        $payload = [
            'url' => $url,
            'outputPath' => $tmpPath,
            'selectors' => [
                'username' => $selectors['username'] ?? null,
                'password' => $selectors['password'] ?? null,
                'totp' => $selectors['totp'] ?? null,
                'submit' => $selectors['submit'] ?? null,
                'totp_submit' => $selectors['totp_submit'] ?? null,
            ],
            'username' => $username,
            'password' => $password,
            'totpCode' => $totpCode,
            'timeoutMs' => $config['timeout_ms'] ?? null,
            'waitUntil' => $config['wait_until'] ?? null,
            'waitFor' => $config['wait_for'] ?? null,
            'fullPage' => $fullPage,
        ];

        $process = new Process([
            'node',
            base_path('scripts/playwright/screenshot.mjs'),
        ]);
        $process->setTimeout(120);
        $process->setInput(json_encode($payload, JSON_THROW_ON_ERROR));
        $process->run();

        if (! $process->isSuccessful()) {
            Log::warning('Screenshot runner failed.', [
                'element_id' => $element->id,
                'exit_code' => $process->getExitCode(),
                'stderr' => $process->getErrorOutput(),
            ]);
            throw new \RuntimeException('Screenshot runner failed.');
        }

        if (! file_exists($tmpPath)) {
            throw new \RuntimeException('Screenshot runner did not produce an output file.');
        }

        $defaultDisk = (string) config('filesystems.default');
        Storage::disk($defaultDisk)->put($relativePath, (string) file_get_contents($tmpPath));

        $element->last_screenshot_disk = $defaultDisk;
        $element->last_screenshot_path = $relativePath;
        $element->last_screenshot_at = now();
        $element->save();

        @unlink($tmpPath);
    }

    /**
     * @return array{username?:string,password?:string,totp_secret?:string}
     */
    private function decryptSecrets(mixed $encryptedCredentials): array
    {
        if (! is_string($encryptedCredentials) || $encryptedCredentials === '') {
            return [];
        }
        try {
            $decoded = json_decode($encryptedCredentials, true, flags: JSON_THROW_ON_ERROR);
            if (! is_array($decoded)) {
                return [];
            }
            $out = [];
            foreach (['username', 'password', 'totp_secret'] as $key) {
                $v = $decoded[$key] ?? null;
                if (is_string($v) && trim($v) !== '') {
                    $out[$key] = $v;
                }
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
    }
}
