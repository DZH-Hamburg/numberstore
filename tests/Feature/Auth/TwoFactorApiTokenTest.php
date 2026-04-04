<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TwoFactorApiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_endpoint_requires_otp_after_valid_password(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $first = $this->postJson('/api/v1/auth/token', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $first->assertStatus(422);
        $first->assertJsonValidationErrors(['otp']);

        $second = $this->postJson('/api/v1/auth/token', [
            'email' => $user->email,
            'password' => 'password',
            'otp' => '123456',
        ]);

        $second->assertOk();
        $second->assertJsonStructure(['token', 'token_type', 'user']);
    }
}
