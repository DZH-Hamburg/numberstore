<?php

namespace Tests\Feature;

use App\Enums\TwoFactorMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OTPHP\TOTP;
use Tests\TestCase;

class ProfileTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_switch_to_totp_and_back_to_email(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('profile.two-factor.totp.start'), [
                'password' => 'password',
            ])
            ->assertRedirect(route('profile.edit'));

        $secret = session('totp_enrollment_secret');
        $this->assertIsString($secret);

        $code = TOTP::createFromSecret($secret)->now();

        $this->actingAs($user)
            ->post(route('profile.two-factor.totp.confirm'), [
                'code' => $code,
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame(TwoFactorMethod::Totp, $user->two_factor_method);
        $this->assertNotNull($user->two_factor_confirmed_at);

        $this->actingAs($user)
            ->post(route('profile.two-factor.email'), [
                'password' => 'password',
            ])
            ->assertRedirect(route('profile.edit'));

        $user->refresh();
        $this->assertSame(TwoFactorMethod::Email, $user->two_factor_method);
        $this->assertNull($user->two_factor_secret);
    }
}
