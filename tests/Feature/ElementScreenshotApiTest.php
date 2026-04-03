<?php

namespace Tests\Feature;

use App\Enums\ElementType;
use App\Enums\GroupMembershipRole;
use App\Jobs\RunScreenshotJob;
use App\Models\Element;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ElementScreenshotApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_screenshot_dispatches_job_for_group_creator(): void
    {
        Bus::fake();

        $user = User::factory()->globalGroupCreator()->create();
        $group = Group::query()->create(['name' => 'G', 'slug' => 'g', 'created_by' => $user->id]);
        $group->users()->attach($user->id, ['role' => GroupMembershipRole::GroupCreator]);

        $element = Element::query()->create([
            'type' => ElementType::Screenshot,
            'name' => 'Shot',
            'config' => ['url' => 'https://example.com'],
            'created_by' => $user->id,
        ]);
        $element->groups()->attach($group->id, ['consumer_can_read_via_api' => true]);

        $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/groups/{$group->id}/elements/{$element->id}/screenshot")
            ->assertStatus(202)
            ->assertJsonPath('status', 'queued');

        Bus::assertDispatched(RunScreenshotJob::class, fn (RunScreenshotJob $job) => $job->elementId === $element->id);
    }

    public function test_consumer_cannot_post_screenshot(): void
    {
        Bus::fake();

        $creator = User::factory()->globalGroupCreator()->create();
        $consumer = User::factory()->create([
            'can_create_groups' => false,
            'is_platform_admin' => false,
        ]);
        $group = Group::query()->create(['name' => 'G2', 'slug' => 'g2', 'created_by' => $creator->id]);
        $group->users()->attach($creator->id, ['role' => GroupMembershipRole::GroupCreator]);
        $group->users()->attach($consumer->id, ['role' => GroupMembershipRole::Consumer]);

        $element = Element::query()->create([
            'type' => ElementType::Screenshot,
            'name' => 'Shot',
            'config' => ['url' => 'https://example.com'],
            'created_by' => $creator->id,
        ]);
        $element->groups()->attach($group->id, ['consumer_can_read_via_api' => true]);

        $this->actingAs($consumer, 'sanctum')
            ->postJson("/api/v1/groups/{$group->id}/elements/{$element->id}/screenshot")
            ->assertForbidden();

        Bus::assertNothingDispatched();
    }

    public function test_get_screenshot_returns_404_when_none_stored(): void
    {
        $user = User::factory()->globalGroupCreator()->create();
        $group = Group::query()->create(['name' => 'G3', 'slug' => 'g3', 'created_by' => $user->id]);
        $group->users()->attach($user->id, ['role' => GroupMembershipRole::GroupCreator]);

        $element = Element::query()->create([
            'type' => ElementType::Screenshot,
            'name' => 'Shot',
            'config' => ['url' => 'https://example.com'],
            'created_by' => $user->id,
        ]);
        $element->groups()->attach($group->id, ['consumer_can_read_via_api' => true]);

        $this->actingAs($user, 'sanctum')
            ->get("/api/v1/groups/{$group->id}/elements/{$element->id}/screenshot")
            ->assertNotFound();
    }

    public function test_get_screenshot_forbidden_when_consumer_read_disabled(): void
    {
        $creator = User::factory()->globalGroupCreator()->create();
        $consumer = User::factory()->create([
            'can_create_groups' => false,
            'is_platform_admin' => false,
        ]);
        $group = Group::query()->create(['name' => 'G4', 'slug' => 'g4', 'created_by' => $creator->id]);
        $group->users()->attach($creator->id, ['role' => GroupMembershipRole::GroupCreator]);
        $group->users()->attach($consumer->id, ['role' => GroupMembershipRole::Consumer]);

        $element = Element::query()->create([
            'type' => ElementType::Screenshot,
            'name' => 'Shot',
            'config' => ['url' => 'https://example.com'],
            'created_by' => $creator->id,
        ]);
        $element->groups()->attach($group->id, ['consumer_can_read_via_api' => false]);

        $this->actingAs($consumer, 'sanctum')
            ->get("/api/v1/groups/{$group->id}/elements/{$element->id}/screenshot")
            ->assertForbidden();
    }
}
