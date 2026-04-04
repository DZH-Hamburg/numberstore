<?php

namespace App\Enums;

enum TwoFactorMethod: string
{
    case Email = 'email';
    case Totp = 'totp';
}
