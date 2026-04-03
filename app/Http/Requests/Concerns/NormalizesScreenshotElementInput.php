<?php

namespace App\Http\Requests\Concerns;

trait NormalizesScreenshotElementInput
{
    /**
     * Leere Strings für optionale Screenshot-Felder entfernen, damit Regeln wie
     * `integer` oder `min:16` nicht auf "" scheitern.
     */
    protected function normalizeSecretsInput(): void
    {
        $secrets = $this->input('secrets');
        if (! is_array($secrets)) {
            return;
        }

        foreach (['username', 'password', 'totp_secret'] as $key) {
            if (! array_key_exists($key, $secrets)) {
                continue;
            }
            $value = $secrets[$key];
            if ($value === null || (is_string($value) && trim($value) === '')) {
                unset($secrets[$key]);
            }
        }

        if ($secrets === []) {
            $this->request->remove('secrets');

            return;
        }

        $this->merge(['secrets' => $secrets]);
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    protected function trimEmptyScreenshotConfigFields(array $config): array
    {
        if (isset($config['url']) && is_string($config['url'])) {
            $config['url'] = trim($config['url']);
            if ($config['url'] === '') {
                unset($config['url']);
            }
        }

        foreach (['wait_for', 'wait_until'] as $key) {
            if (! isset($config[$key])) {
                continue;
            }
            if (! is_string($config[$key]) || trim($config[$key]) !== '') {
                continue;
            }
            unset($config[$key]);
        }

        if (array_key_exists('timeout_ms', $config)) {
            $tm = $config['timeout_ms'];
            if ($tm === null || $tm === '') {
                unset($config['timeout_ms']);
            }
        }

        if (isset($config['selectors']) && is_array($config['selectors'])) {
            foreach ($config['selectors'] as $sk => $sv) {
                if (! is_string($sv) || trim($sv) === '') {
                    unset($config['selectors'][$sk]);
                }
            }
            if ($config['selectors'] === []) {
                unset($config['selectors']);
            }
        }

        return $config;
    }
}
