<?php

use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Orchestra\Testbench\Attributes\WithMigration;
use Whilesmart\Organizations\Events\OrganizationCreatedEvent;
use Whilesmart\Organizations\Events\OrganizationUpdatedEvent;
use Whilesmart\Organizations\Models\Organization;
use Whilesmart\Roles\Models\Role;
use Workbench\App\Models\User;

#[WithMigration]
class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    public function test_get_all_organizations()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $organization = $this->createOrganization($user);
        $organization2 = $this->createOrganization($user);
        $user->assignRole('owner', 'organization', $organization->id);
        $user->assignRole('member', 'organization', $organization2->id);

        $response = $this->getJson('/organizations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
        $this->assertCount(2, $response->json('data.data'));

    }

    protected function createUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'email' => Factory::create()->unique()->safeEmail,
            'password' => Hash::make('password123'),
            'name' => 'John',
        ], $attributes));
    }

    protected function createOrganization(User $owner, array $attributes = []): Organization
    {
        $name = Factory::create()->unique()->name;
        return Organization::create(array_merge([
            'name' => $name,
            'type' => 'organization',
            'email' => 'test@example.com',
            'phone' => '1234567890',
            'address' => '123 Test St',
            'website' => 'https://example.com',
            'slug' => Str::slug($name),
            'owner_type' => get_class($owner),
            'owner_id' => $owner->id,
        ], $attributes));
    }

    public function test_create_organization()
    {
        Event::fake();

        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->postJson('/organizations', [
            'name' => 'New Organization',
            'type' => 'organization',
            'email' => 'new@example.com',
            'phone' => '9876543210',
            'address' => '456 New St',
            'website' => 'https://new-example.com',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);

        $this->assertDatabaseHas('organizations', [
            'name' => 'New Organization',
            'email' => 'new@example.com',
        ]);

        Event::assertDispatched(OrganizationCreatedEvent::class);
    }

    public function test_create_organization_validation_error()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->postJson('/organizations', [
            // Missing required fields
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The given data was invalid.',
            ]);
    }

    public function test_show_organization()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $organization = $this->createOrganization($user);
        $user->assignRole('owner', 'organization', $organization->id);

        $response = $this->getJson("/organizations/{$organization->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_show_nonexistent_organization()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->getJson("/organizations/999");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'This organization does not exist',
            ]);
    }

    public function test_update_organization()
    {
        Event::fake();

        $user = $this->createUser();
        $this->actingAs($user);

        $organization = $this->createOrganization($user);
        $user->assignRole('owner', 'organization', $organization->id);

        $response = $this->putJson("/organizations/$organization->id", [
            'name' => 'Updated Organization',
            'type' => 'organization',
            'email' => 'updated@example.com',
            'phone' => '5555555555',
            'address' => '789 Updated St',
            'website' => 'https://updated-example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Updated Organization',
            'email' => 'updated@example.com',
        ]);

        Event::assertDispatched(OrganizationUpdatedEvent::class);
    }

    public function test_update_organization_validation_error()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $organization = $this->createOrganization($user);
        $user->assignRole('owner', 'organization', $organization->id);

        $response = $this->putJson("/organizations/{$organization->id}", [
            // Missing required fields
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The given data was invalid.',
            ]);
    }

    public function test_delete_organization()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $organization = $this->createOrganization($user);
        $user->assignRole('owner', 'organization', $organization->id);

        $response = $this->deleteJson("/organizations/{$organization->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Organization Deleted',
            ]);

        $this->assertDatabaseMissing('organizations', [
            'id' => $organization->id,
        ]);
    }

    public function test_admin_can_add_member_to_organization()
    {
        $owner = $this->createUser();

        $organization = $this->createOrganization($owner);
        $owner->assignRole('owner', 'organization', $organization->id);

        $admin = $this->createUser([
            'email' => 'admin@example.com',
        ]);

        $admin->assignRole('admin', 'organization', $organization->id);

        $member = $this->createUser([
            'email' => 'member@example.com',
        ]);

        $this->actingAs($admin);

        $response = $this->postJson("/organizations/{$organization->id}/members", [
            'email' => $member->email,
            'role' => 'member',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Member Added',
            ]);

        $this->assertTrue($member->hasRole('member', 'organization', $organization->id));
    }

    public function test_owner_can_add_member_to_organization()
    {
        $owner = $this->createUser();
        $this->actingAs($owner);

        $organization = $this->createOrganization($owner);
        $owner->assignRole('owner', 'organization', $organization->id);

        $member = $this->createUser([
            'email' => 'member@example.com',
        ]);

        $response = $this->postJson("/organizations/{$organization->id}/members", [
            'email' => $member->email,
            'role' => 'member',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Member Added',
            ]);

        $this->assertTrue($member->hasRole('member', 'organization', $organization->id));
    }

    public function test_member_and_non_member_can_not_add_another_member_to_organization()
    {
        $owner = $this->createUser();

        $organization = $this->createOrganization($owner);
        $owner->assignRole('owner', 'organization', $organization->id);

        $member1 = $this->createUser([
            'email' => 'member1@example.com',
        ]);

        $member1->assignRole('member', 'organization', $organization->id);
        $member2 = $this->createUser([
            'email' => 'member2@example.com',
        ]);
        $this->actingAs($member1);

        $response = $this->postJson("/organizations/{$organization->id}/members", [
            'email' => $member2->email,
            'role' => 'member',
        ]);

        $response->assertStatus(403);


        $this->assertFalse($member2->hasRole('member', 'organization', $organization->id));
    }

    public function test_owner_can_remove_member_from_organization()
    {
        $owner = $this->createUser();
        $this->actingAs($owner);

        $organization = $this->createOrganization($owner);
        $owner->assignRole('owner', 'organization', $organization->id);

        $member = $this->createUser([
            'email' => 'member@example.com',
        ]);
        $member->assignRole('member', 'organization', $organization->id);

        $response = $this->deleteJson("/organizations/{$organization->id}/members/{$member->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Member Deleted',
            ]);

        $this->assertFalse($member->hasRole('member', 'organization', $organization->id));
    }

    public function test_admin_can_remove_member_from_organization()
    {
        $owner = $this->createUser();

        $organization = $this->createOrganization($owner);
        $owner->assignRole('owner', 'organization', $organization->id);

        $admin = $this->createUser([
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin', 'organization', $organization->id);

        $member = $this->createUser([
            'email' => 'member@example.com',
        ]);
        $member->assignRole('member', 'organization', $organization->id);

        $this->actingAs($admin);

        $response = $this->deleteJson("/organizations/{$organization->id}/members/{$member->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Member Deleted',
            ]);

        $this->assertFalse($member->hasRole('member', 'organization', $organization->id));
    }

    public function test_member_and_non_member_can_not_remove_member_from_organization()
    {
        $owner = $this->createUser();

        $organization = $this->createOrganization($owner);
        $owner->assignRole('owner', 'organization', $organization->id);

        $member1 = $this->createUser([
            'email' => 'member@example.com',
        ]);
        $member1->assignRole('member', 'organization', $organization->id);

        $member2 = $this->createUser([
            'email' => 'member2@example.com',
        ]);
        $member2->assignRole('member', 'organization', $organization->id);
        $this->actingAs($member1);

        $response = $this->deleteJson("/organizations/{$organization->id}/members/{$member2->id}");

        $response->assertStatus(403);

        $this->assertTrue($member2->hasRole('member', 'organization', $organization->id));
    }

    public function test_owner_get_list_of_members_in_organization()
    {
        $owner = $this->createUser();

        $organization = $this->createOrganization($owner);
        $owner->assignRole('owner', 'organization', $organization->id);

        $admin = $this->createUser([
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin', 'organization', $organization->id);

        $member = $this->createUser([
            'email' => 'member@example.com',
        ]);
        $member->assignRole('member', 'organization', $organization->id);

        $this->actingAs($owner);
        $response = $this->getJson("/organizations/{$organization->id}/members/");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_admin_get_list_of_members_in_organization()
    {
        $owner = $this->createUser();

        $organization = $this->createOrganization($owner);
        $owner->assignRole('owner', 'organization', $organization->id);

        $admin = $this->createUser([
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin', 'organization', $organization->id);

        $member = $this->createUser([
            'email' => 'member@example.com',
        ]);
        $member->assignRole('member', 'organization', $organization->id);

        $this->actingAs($admin);
        $response = $this->getJson("/organizations/{$organization->id}/members/");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_member_get_list_of_members_in_organization()
    {
        $owner = $this->createUser();

        $organization = $this->createOrganization($owner);
        $owner->assignRole('owner', 'organization', $organization->id);

        $admin = $this->createUser([
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin', 'organization', $organization->id);

        $member = $this->createUser([
            'email' => 'member@example.com',
        ]);
        $member->assignRole('member', 'organization', $organization->id);

        $this->actingAs($member);
        $response = $this->getJson("/organizations/{$organization->id}/members/");

        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_non_member_can_not_get_list_of_members_in_organization()
    {
        $owner = $this->createUser();

        $organization = $this->createOrganization($owner);
        $owner->assignRole('owner', 'organization', $organization->id);

        $admin = $this->createUser([
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin', 'organization', $organization->id);

        $member = $this->createUser([
            'email' => 'member@example.com',
        ]);

        $this->actingAs($member);
        $response = $this->getJson("/organizations/{$organization->id}/members/");

        $response->assertStatus(403);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Role::create(['name' => 'owner', 'slug' => 'owner']);
        Role::create(['name' => 'admin', 'slug' => 'admin']);
        Role::create(['name' => 'member', 'slug' => 'member']);
        config(['organizations.user_model' => User::class]);
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            'migrations'
        );
        $this->loadMigrationsFrom(\Orchestra\Testbench\workbench_path('migrations'));
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            'Whilesmart\Organizations\OrganizationsServiceProvider',
        ];
    }
}
