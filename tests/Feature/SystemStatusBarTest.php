<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemStatusBarTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_includes_system_status_bar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('System', false);
        $response->assertSee('Queue-Worker:', false);
        $response->assertSee('Scheduler:', false);
        $response->assertSee(route('system.queue-worker'), false);
    }

    public function test_queue_worker_page_is_reachable_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('system.queue-worker'));

        $response->assertOk();
        $response->assertSee('Queue-Worker', false);
        $response->assertSee('Warteschlangen', false);
    }
}
