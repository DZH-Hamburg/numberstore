<?php

namespace Tests\Feature;

use App\Enums\ElementType;
use App\Enums\GroupMembershipRole;
use App\Models\Element;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_creator_can_create_group_and_becomes_group_creator(): void
    {
        $user = User::factory()->globalGroupCreator()->create();

        $response = $this->actingAs($user)->post('/groups', [
            'name' => 'Marketing KPIs',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('groups', ['name' => 'Marketing KPIs']);
        $group = Group::query()->where('name', 'Marketing KPIs')->firstOrFail();
        $this->assertTrue($user->fresh()->isGroupCreatorIn($group));
    }

    public function test_consumer_cannot_create_group(): void
    {
        $user = User::factory()->create([
            'can_create_groups' => false,
            'is_platform_admin' => false,
        ]);

        $this->actingAs($user)->post('/groups', ['name' => 'X'])->assertForbidden();
    }

    public function test_group_creator_can_create_element(): void
    {
        $user = User::factory()->globalGroupCreator()->create();
        $group = Group::query()->create(['name' => 'G1', 'slug' => 'g1', 'created_by' => $user->id]);
        $group->users()->attach($user->id, ['role' => GroupMembershipRole::GroupCreator]);

        $response = $this->actingAs($user)->post(route('groups.elements.store', $group), [
            'type' => ElementType::Number->value,
            'name' => 'Umsatz',
            'config' => [],
        ]);

        $response->assertRedirect(route('groups.show', $group));
        $this->assertDatabaseHas('elements', ['name' => 'Umsatz']);
    }

    public function test_group_creator_can_create_screenshot_element_with_url_only_and_empty_optionals(): void
    {
        $user = User::factory()->globalGroupCreator()->create();
        $group = Group::query()->create(['name' => 'Gshot', 'slug' => 'gshot', 'created_by' => $user->id]);
        $group->users()->attach($user->id, ['role' => GroupMembershipRole::GroupCreator]);

        $googleUrl = 'https://www.google.com/?client=safari';

        $response = $this->actingAs($user)->from(route('groups.elements.create', $group))->post(route('groups.elements.store', $group), [
            'type' => ElementType::Screenshot->value,
            'name' => 'Google',
            'config' => [
                'url' => $googleUrl,
                'selectors' => [
                    'username' => '',
                    'password' => '',
                    'totp' => '',
                    'submit' => '',
                ],
                'wait_for' => '',
                'timeout_ms' => '',
            ],
            'secrets' => [
                'username' => '',
                'password' => '',
                'totp_secret' => '',
            ],
        ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertRedirectToRoute('groups.show', $group);

        $this->assertDatabaseHas('elements', [
            'name' => 'Google',
            'type' => ElementType::Screenshot->value,
        ]);

        $element = Element::query()->where('name', 'Google')->firstOrFail();
        $this->assertSame($googleUrl, $element->config['url'] ?? null);
    }
}
