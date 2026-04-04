<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Test-Code (nur APP_ENV=testing)
    |--------------------------------------------------------------------------
    |
    | Wenn gesetzt, wird dieser 6-stellige Code statt eines Zufallscodes für
    | E-Mail-2FA beim Login verwendet (Feature-Tests).
    |
    */
    'testing_email_code' => env('TWO_FACTOR_TEST_CODE'),

];
