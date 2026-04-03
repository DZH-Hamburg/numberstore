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

    public function test_member_can_open_groups_index_and_see_only_their_groups(): void
    {
        $member = User::factory()->create(['can_create_groups' => false, 'is_platform_admin' => false]);
        $other = User::factory()->create();

        $mine = Group::query()->create(['name' => 'Meine Gruppe', 'slug' => 'meine-gruppe', 'created_by' => $other->id]);
        $mine->users()->attach($member->id, ['role' => GroupMembershipRole::Consumer]);

        $foreign = Group::query()->create(['name' => 'Fremd', 'slug' => 'fremd', 'created_by' => $other->id]);
        $foreign->users()->attach($other->id, ['role' => GroupMembershipRole::GroupCreator]);

        $response = $this->actingAs($member)->get(route('groups.index'));
        $response->assertOk();
        $response->assertSee('Meine Gruppe', false);
        $response->assertDontSee('Fremd', false);
    }

    public function test_groups_index_search_filters_by_name(): void
    {
        $user = User::factory()->globalGroupCreator()->create();
        $g1 = Group::query()->create(['name' => 'Alpha Team', 'slug' => 'alpha-team', 'created_by' => $user->id]);
        $g1->users()->attach($user->id, ['role' => GroupMembershipRole::GroupCreator]);
        $g2 = Group::query()->create(['name' => 'Beta Club', 'slug' => 'beta-club', 'created_by' => $user->id]);
        $g2->users()->attach($user->id, ['role' => GroupMembershipRole::GroupCreator]);

        $response = $this->actingAs($user)->get(route('groups.index', ['q' => 'alpha']));
        $response->assertOk();
        $response->assertSee('Alpha Team', false);
        $response->assertDontSee('Beta Club', false);
    }

    public function test_groups_index_role_filter_limits_to_matching_membership(): void
    {
        $user = User::factory()->globalGroupCreator()->create();

        $asCreator = Group::query()->create(['name' => 'Creator Only', 'slug' => 'creator-only', 'created_by' => $user->id]);
        $asCreator->users()->attach($user->id, ['role' => GroupMembershipRole::GroupCreator]);

        $asConsumer = Group::query()->create(['name' => 'Consumer Only', 'slug' => 'consumer-only', 'created_by' => $user->id]);
        $asConsumer->users()->attach($user->id, ['role' => GroupMembershipRole::Consumer]);

        $response = $this->actingAs($user)->get(route('groups.index', ['role' => GroupMembershipRole::GroupCreator->value]));
        $response->assertOk();
        $response->assertSee('Creator Only', false);
        $response->assertDontSee('Consumer Only', false);
    }

    public function test_group_creator_can_update_group_name(): void
    {
        $user = User::factory()->globalGroupCreator()->create();
        $group = Group::query()->create(['name' => 'Alt', 'slug' => 'alt', 'created_by' => $user->id]);
        $group->users()->attach($user->id, ['role' => GroupMembershipRole::GroupCreator]);

        $response = $this->actingAs($user)->patch(route('groups.update', $group), ['name' => 'Neu']);
        $response->assertRedirect(route('groups.show', $group));
        $this->assertDatabaseHas('groups', ['id' => $group->id, 'name' => 'Neu', 'slug' => 'neu']); // pragma: allowlist secret
    }

    public function test_consumer_cannot_update_group(): void
    {
        $creator = User::factory()->globalGroupCreator()->create();
        $consumer = User::factory()->create(['can_create_groups' => false, 'is_platform_admin' => false]);
        $group = Group::query()->create(['name' => 'G', 'slug' => 'g', 'created_by' => $creator->id]);
        $group->users()->attach($consumer->id, ['role' => GroupMembershipRole::Consumer]);

        $this->actingAs($consumer)->patch(route('groups.update', $group), ['name' => 'Hacked'])->assertForbidden();
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
