<?php

namespace App\Services;

use OTPHP\TOTP;

class TotpService
{
    public function now(string $base32Secret, int $period = 30, int $digits = 6): string
    {
        $totp = TOTP::create($base32Secret, $period, 'sha1', $digits);

        return $totp->now();
    }
}
