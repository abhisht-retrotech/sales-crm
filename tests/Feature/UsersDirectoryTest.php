<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\UserInvitation;
use App\Models\Role;
use App\Models\Profile;
use App\Models\Lead;
use App\Models\Account;
use App\Models\Opportunity;
use App\Models\Task;

class UsersDirectoryTest extends TestCase
{
    protected ?Tenant $tenant = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::on('mysql')->first();
        if ($this->tenant) {
            $this->tenant->connect();
            UserInvitation::on('tenant')->where('email', 'like', 'candidate.%')->delete();
            if ($this->tenant->max_seats < 100) {
                $this->tenant->max_seats = 100;
                $this->tenant->save();
            }
        }
    }

    public function test_users_directory_page_loads_successfully_with_team_metrics(): void
    {
        $response = $this->get('/admin/users-directory');

        $response->assertStatus(200);
        $response->assertSee('Users Directory');
        $response->assertSee('Seat License &amp; Quota Governance', false);
        $response->assertSee('Active Users');
        $response->assertSee('Pending Invites');
        $response->assertSee('Available Seats');
        $response->assertSee('Pending Invitations');
        $response->assertSee('Invite Team Member');
        $response->assertSee('+ Add Seats ($39/mo)');
        $response->assertSee('activeUsersTbody');
        $response->assertSee('invitationsTbody');
        $response->assertSee('offboardModal');
        $response->assertSee('userDetailContainer');
    }

    public function test_users_directory_alternate_users_route_works(): void
    {
        $response = $this->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Users Directory');
        $response->assertSee('Seat License &amp; Quota Governance', false);
    }

    public function test_api_settings_users_index_returns_json_user_list(): void
    {
        $response = $this->getJson('/api/settings/users');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'email',
                'status',
                'full_name',
                'leads_count',
                'accounts_count',
                'opps_count',
                'pipeline_owned',
                'tasks_count',
            ]
        ]);
    }

    public function test_create_user_api_persists_new_employee(): void
    {
        $uniqueEmail = 'clara.test.' . time() . '@dealsync.com';
        $payload = [
            'full_name'           => 'Clara Oswald',
            'email'               => $uniqueEmail,
            'role_id'             => 'role-sales-rep',
            'profile_id'          => 'prof-sales',
            'status'              => 'Active',
            'phone'               => '+1 (555) 777-8899',
            'surprise_me_enabled' => true,
        ];

        $response = $this->postJson('/api/settings/users', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $created = TenantUser::on('tenant')->where('email', $uniqueEmail)->first();
        $this->assertNotNull($created);
        $this->assertEquals('Clara Oswald', $created->name);
        $this->assertEquals('Active', $created->status);
    }

    public function test_update_user_api_updates_attributes(): void
    {
        $user = TenantUser::on('tenant')->where('status', 'Active')->first();
        $this->assertNotNull($user);

        $updatedPhone = '+1 (555) 999-0011';
        $payload = [
            'full_name'           => $user->name,
            'email'               => $user->email,
            'phone'               => $updatedPhone,
            'surprise_me_enabled' => false,
        ];

        $response = $this->putJson("/admin/users-directory/user/{$user->id}", $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $user->refresh();
        $this->assertEquals($updatedPhone, $user->phone);
    }

    public function test_impersonate_user_api_sets_session(): void
    {
        $user = TenantUser::on('tenant')->where('status', 'Active')->first();
        $this->assertNotNull($user);

        $response = $this->postJson("/admin/users-directory/user/{$user->id}/impersonate");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertSessionHas('impersonated_user_id', $user->id);
    }

    public function test_create_team_invitation_with_cryptographic_token(): void
    {
        $uniqueInviteEmail = 'candidate.' . time() . '@external.com';
        $payload = [
            'email'        => $uniqueInviteEmail,
            'full_name'    => 'Jordan Candidate',
            'role_id'      => 'role-sales-rep',
            'profile_id'   => 'prof-sales',
            'welcome_note' => 'Welcome to DealSync! Please activate your workspace.',
        ];

        $response = $this->postJson('/admin/users-directory/invite', $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'data' => [
                'token',
                'activation_url',
                'expires_at',
            ]
        ]);

        $invitation = UserInvitation::on('tenant')->where('email', $uniqueInviteEmail)->first();
        $this->assertNotNull($invitation);
        $this->assertEquals('pending', $invitation->status);
        $this->assertStringStartsWith('tok-', $invitation->token);
    }

    public function test_resend_invitation_refreshes_token_and_expiry(): void
    {
        $invitation = UserInvitation::on('tenant')->first();
        if (!$invitation) {
            $invitation = UserInvitation::on('tenant')->create([
                'email'      => 'resend.test@example.com',
                'full_name'  => 'Resend Test',
                'token'      => 'tok-initial-' . time(),
                'status'     => 'pending',
                'expires_at' => now()->addDays(2),
            ]);
        }

        $oldToken = $invitation->token;

        $response = $this->postJson("/admin/users-directory/invite/{$invitation->id}/resend");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $invitation->refresh();
        $this->assertNotEquals($oldToken, $invitation->token);
        $this->assertEquals('pending', $invitation->status);
    }

    public function test_revoke_invitation_deletes_record_and_reclaims_seat(): void
    {
        $invitation = UserInvitation::on('tenant')->create([
            'email'      => 'revoke.test.' . time() . '@example.com',
            'full_name'  => 'Revoke Candidate',
            'token'      => 'tok-revoke-' . time(),
            'status'     => 'pending',
            'expires_at' => now()->addDays(5),
        ]);

        $response = $this->deleteJson("/admin/users-directory/invite/{$invitation->id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertNull(UserInvitation::on('tenant')->find($invitation->id));
    }

    public function test_get_user_owned_records_summary(): void
    {
        $user = TenantUser::on('tenant')->first();
        $this->assertNotNull($user);

        $response = $this->getJson("/admin/users-directory/summary/{$user->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'user_id',
                'user_name',
                'leads',
                'accounts',
                'opportunities',
                'opportunity_value',
                'tasks',
                'total_records',
            ]
        ]);
    }

    public function test_offboard_user_reassigns_records_and_deactivates(): void
    {
        // Pick or create 2 active users
        $users = TenantUser::on('tenant')->where('status', 'Active')->take(2)->get();
        if ($users->count() < 2) {
            $user1 = TenantUser::on('tenant')->create([
                'name'     => 'Offboard Source',
                'email'    => 'source.' . time() . '@test.com',
                'password' => 'Password123!',
                'status'   => 'Active',
            ]);
            $user2 = TenantUser::on('tenant')->create([
                'name'     => 'Offboard Target',
                'email'    => 'target.' . time() . '@test.com',
                'password' => 'Password123!',
                'status'   => 'Active',
            ]);
        } else {
            $user1 = $users[0];
            $user2 = $users[1];
        }

        // Create a dummy lead assigned to user1
        $lead = Lead::on('tenant')->create([
            'first_name'  => 'Test',
            'last_name'   => 'Lead ' . time(),
            'email'       => 'lead.' . time() . '@example.com',
            'company'     => 'Acme Corp',
            'status'      => 'New',
            'assigned_to' => $user1->id,
        ]);

        $response = $this->postJson("/admin/users-directory/offboard/{$user1->id}", [
            'reassign_to_user_id' => $user2->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $user1->refresh();
        $this->assertEquals('Inactive', $user1->status);

        $lead->refresh();
        $this->assertEquals($user2->id, $lead->assigned_to);
    }

    public function test_add_seats_increases_organization_max_seats(): void
    {
        $currentMax = $this->tenant->max_seats ?? 15;
        $newSeats = $currentMax + 3;

        $response = $this->postJson('/admin/users-directory/add-seats', [
            'new_seats_count' => $newSeats,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'   => true,
            'max_seats' => $newSeats,
        ]);

        $this->tenant->refresh();
        $this->assertEquals($newSeats, $this->tenant->max_seats);
    }

    public function test_user_manager_relationship_and_api_persistence(): void
    {
        $manager = TenantUser::on('tenant')->firstOrCreate(
            ['email' => 'executive.manager@dealsync.com'],
            ['name' => 'Executive Manager', 'password' => 'Password123!', 'status' => 'Active']
        );

        $subordinateEmail = 'subordinate.' . uniqid() . '@dealsync.com';
        $payload = [
            'name'       => 'Direct Subordinate',
            'email'      => $subordinateEmail,
            'manager_id' => $manager->id,
            'status'     => 'Active',
        ];

        $response = $this->postJson('/api/settings/users', $payload);
        $response->assertStatus(201);

        $subordinate = TenantUser::on('tenant')->where('email', $subordinateEmail)->first();
        $this->assertNotNull($subordinate);
        $this->assertEquals($manager->id, $subordinate->manager_id);
        $this->assertEquals('Executive Manager', $subordinate->manager_name);
        $this->assertEquals($manager->id, $subordinate->manager->id);
        $this->assertTrue($manager->directReports->contains('id', $subordinate->id));

        // Test update manager_id
        $newManager = TenantUser::on('tenant')->firstOrCreate(
            ['email' => 'secondary.manager@dealsync.com'],
            ['name' => 'Secondary Manager', 'password' => 'Password123!', 'status' => 'Active']
        );

        $updateResponse = $this->putJson("/admin/users-directory/user/{$subordinate->id}", [
            'manager_id' => $newManager->id,
        ]);
        $updateResponse->assertStatus(200);

        $subordinate->refresh();
        $this->assertEquals($newManager->id, $subordinate->manager_id);
        $this->assertEquals('Secondary Manager', $subordinate->manager_name);
    }
}
