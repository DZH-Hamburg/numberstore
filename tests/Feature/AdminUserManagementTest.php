<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_platform_admin_can_open_user_index(): void
    {
        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee(__('Benutzerverwaltung'), false);
    }

    public function test_non_admin_cannot_open_user_index(): void
    {
        $user = User::factory()->create([
            'is_platform_admin' => false,
            'can_create_groups' => true,
        ]);

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_platform_admin_can_create_user_and_sends_password_reset_notification(): void
    {
        Notification::fake();

        $admin = User::factory()->platformAdmin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'Neu Nutzer',
                'email' => 'neu@example.com',
                'is_platform_admin' => false,
                'can_create_groups' => true,
            ])
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'neu@example.com',
            'can_create_groups' => true,
            'is_platform_admin' => false,
        ]);

        $created = User::query()->where('email', 'neu@example.com')->firstOrFail();
        Notification::assertSentTo($created, ResetPassword::class);
    }
}
