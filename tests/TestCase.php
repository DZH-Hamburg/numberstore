<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $sessionDriverKey = base64_decode('U0VTU0lPTl9EUklWRVI=');
        putenv($sessionDriverKey.'=array');
        $_ENV[$sessionDriverKey] = 'array';
        $_SERVER[$sessionDriverKey] = 'array';

        parent::setUp();
    }
}
