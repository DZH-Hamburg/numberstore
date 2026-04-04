<?php

namespace App\Services;

use App\Enums\TwoFactorMethod;
use App\Models\User;
use App\Notifications\TwoFactorLoginCodeNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class TwoFactorEmailCodeService
{
    private const CACHE_CODE_PREFIX = 'two_factor_email_login:';

    private const CACHE_SENT_MARKER_PREFIX = 'two_factor_email_login_sent:';

    public function ensureLoginCodeSent(User $user): void
    {
        if ($user->two_factor_method !== TwoFactorMethod::Email) {
            return;
        }

        if (Cache::has(self::CACHE_SENT_MARKER_PREFIX.$user->id)) {
            return;
        }

        $this->issueAndDeliverEmailCode($user);
    }

    /**
     * Stellt sicher, dass ein gültiger E-Mail-Code existiert (ohne bei jedem POST neu zu mailen).
     */
    public function ensureLoginCodePrepared(User $user): void
    {
        if ($user->two_factor_method !== TwoFactorMethod::Email) {
            return;
        }

        if (Cache::has(self::CACHE_CODE_PREFIX.$user->id)) {
            return;
        }

        $this->issueAndDeliverEmailCode($user);
    }

    public function resendLoginCode(User $user): bool
    {
        if ($user->two_factor_method !== TwoFactorMethod::Email) {
            return false;
        }

        $allowed = RateLimiter::attempt(
            'two-factor-email-resend:'.$user->id,
            5,
            fn (): bool => true,
            600
        );

        if (! $allowed) {
            return false;
        }

        $this->issueAndDeliverEmailCode($user);

        return true;
    }

    public function verifyLoginCode(User $user, string $code): bool
    {
        if ($user->two_factor_method !== TwoFactorMethod::Email) {
            return false;
        }

        $key = self::CACHE_CODE_PREFIX.$user->id;
        $hash = Cache::get($key);
        if (! is_string($hash)) {
            return false;
        }

        if (! password_verify($code, $hash)) {
            return false;
        }

        Cache::forget($key);
        Cache::forget(self::CACHE_SENT_MARKER_PREFIX.$user->id);

        return true;
    }

    public function issueAndDeliverEmailCode(User $user): void
    {
        $code = $this->generateNumericCode();
        Cache::put(
            self::CACHE_CODE_PREFIX.$user->id,
            password_hash($code, PASSWORD_DEFAULT),
            now()->addMinutes(10)
        );
        Cache::put(self::CACHE_SENT_MARKER_PREFIX.$user->id, true, 60);
        $user->notify(new TwoFactorLoginCodeNotification($code));
    }

    private function generateNumericCode(): string
    {
        $fixed = config('two_factor.testing_email_code');
        if (app()->environment('testing') && is_string($fixed) && $fixed !== '') {
            $digits = preg_replace('/\D/', '', $fixed) ?? '';

            return str_pad(substr($digits, 0, 6), 6, '0', STR_PAD_LEFT);
        }

        return str_pad((string) random_int(0, 999_999), 6, '0', STR_PAD_LEFT);
    }
}
