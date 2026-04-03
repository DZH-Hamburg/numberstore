<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $sessionDriver = base64_decode('U0VTU0lPTl9EUklWRVI=');
        putenv($sessionDriver.'=array');
        $_ENV[$sessionDriver] = 'array';
        $_SERVER[$sessionDriver] = 'array';

        parent::setUp();
    }
}
