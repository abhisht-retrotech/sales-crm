<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Lead;
use App\Models\Role;
use App\Models\Profile;
use App\Models\ProfilePermission;
use App\Models\FieldPermission;
use App\Models\TenantUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LeadsPermissionsAndSecurityTest extends TestCase
{
    protected ?Tenant $tenant = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::on('mysql')->first();
        if ($this->tenant) {
            $this->tenant->connect();
        }
    }

    /**
     * Helper to create a dedicated test tenant user with specified role and profile.
     */
    protected function createTestUser(string $name, ?Role $role = null, ?Profile $profile = null, ?int $managerId = null): TenantUser
    {
        $unique = Str::random(8);
        return TenantUser::on('tenant')->create([
            'name'       => $name,
            'email'      => "test_{$unique}@example.com",
            'password'   => Hash::make('password123'),
            'role_id'    => $role?->id,
            'profile_id' => $profile?->id,
            'manager_id' => $managerId,
            'status'     => 'Active',
        ]);
    }

    /**
     * Helper to create a test security profile with custom module permissions.
     */
    protected function createTestProfile(string $name, array $leadsPerms): Profile
    {
        $profile = Profile::on('tenant')->create([
            'name'        => $name . '_' . Str::random(6),
            'description' => 'Test Profile for leads permissions',
        ]);

        ProfilePermission::on('tenant')->create(array_merge([
            'profile_id'   => $profile->id,
            'module'       => 'leads',
            'can_create'   => true,
            'can_read'     => true,
            'can_update'   => true,
            'can_delete'   => true,
            'can_transfer' => true,
            'record_scope' => 'All',
        ], $leadsPerms));

        return $profile;
    }

    /**
     * Helper to create a test lead record with guaranteed valid standard fields.
     */
    protected function createTestLead(array $attrs = []): Lead
    {
        $unique = Str::random(6);
        return Lead::on('tenant')->create(array_merge([
            'first_name'  => 'Test',
            'last_name'   => 'Lead_' . $unique,
            'company'     => 'Company_' . $unique,
            'email'       => "lead_{$unique}@example.com",
            'status'      => 'New',
            'score'       => 50,
            'rating'      => 'Warm',
            'source'      => 'Website',
        ], $attrs));
    }

    public function test_admin_user_has_unrestricted_full_access(): void
    {
        $admin = TenantUser::on('tenant')->where('name', 'Alex Morgan')->first()
            ?? TenantUser::on('tenant')->first();

        $this->actingAs($admin, 'web');

        // 1. Can view leads page
        $resPage = $this->get('/leads');
        $resPage->assertStatus(200);
        $resPage->assertSee('Add Lead');

        // 2. Can list leads via API
        $resList = $this->getJson('/api/leads');
        $resList->assertStatus(200);

        // 3. Can create lead
        $resCreate = $this->postJson('/api/leads', [
            'first_name' => 'Enterprise',
            'last_name'  => 'Client ' . Str::random(4),
            'company'    => 'Acme Global',
            'email'      => 'client@acmeglobal.test',
            'status'     => 'New',
            'score'      => 85,
        ]);
        $resCreate->assertStatus(201);
        $createdLeadId = $resCreate->json('lead.id');

        // 4. Can update lead
        $resUpdate = $this->putJson("/api/leads/{$createdLeadId}", [
            'title' => 'Chief Technology Officer',
            'score' => 92,
        ]);
        $resUpdate->assertStatus(200);
        $this->assertEquals(92, Lead::on('tenant')->find($createdLeadId)->score);

        // 5. Can transfer lead
        $targetUser = TenantUser::on('tenant')->where('id', '!=', $admin->id)->first() ?? $admin;
        $resTransfer = $this->postJson("/api/leads/{$createdLeadId}/transfer", [
            'targetUserId' => $targetUser->id,
            'note'         => 'Reassigned to account manager',
        ]);
        $resTransfer->assertStatus(200);

        // 6. Can delete lead
        $resDelete = $this->deleteJson("/api/leads/{$createdLeadId}");
        $resDelete->assertStatus(200);
        $this->assertNull(Lead::on('tenant')->find($createdLeadId));
    }

    public function test_security_profile_can_read_false_denies_access(): void
    {
        $profile = $this->createTestProfile('Restricted Reader', ['can_read' => false]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Read User', $role, $profile);

        $this->actingAs($user, 'web');

        // Page view renders Access Denied and hides Leads from sidebar
        $resPage = $this->get('/leads');
        $resPage->assertStatus(200);
        $resPage->assertSee('Access Denied: Leads Module');
        $resPage->assertDontSee('title="Leads"', false);

        // API list returns 403 Forbidden
        $resList = $this->getJson('/api/leads');
        $resList->assertStatus(403);
        $resList->assertJsonFragment(['success' => false]);

        // Show individual lead returns 403
        $existingLead = Lead::on('tenant')->first();
        if ($existingLead) {
            $resShow = $this->get("/leads/{$existingLead->id}");
            $resShow->assertStatus(403);
        }
    }

    public function test_security_profile_can_create_false_blocks_creation(): void
    {
        $profile = $this->createTestProfile('Read Only Intake', [
            'can_read'   => true,
            'can_create' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Create User', $role, $profile);

        $this->actingAs($user, 'web');

        // Page does not render Add Lead button
        $resPage = $this->get('/leads');
        $resPage->assertStatus(200);
        $resPage->assertDontSee('+</span>' . "\n" . '            <span>Add Lead</span>', false);

        // API POST fails with 403
        $resCreate = $this->postJson('/api/leads', [
            'first_name' => 'Illegal',
            'last_name'  => 'Lead',
            'company'    => 'Blocked Inc',
            'email'      => 'illegal@blocked.test',
        ]);
        $resCreate->assertStatus(403);
        $resCreate->assertJsonFragment(['success' => false]);
    }

    public function test_security_profile_can_update_false_blocks_mutation_and_conversion(): void
    {
        $profile = $this->createTestProfile('No Edit Profile', [
            'can_read'   => true,
            'can_update' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Edit User', $role, $profile);

        $lead = $this->createTestLead([
            'first_name'  => 'Immutable',
            'last_name'   => 'Lead',
            'company'     => 'Safe Corp',
            'assigned_to' => $user->id,
            'score'       => 50,
        ]);

        $this->actingAs($user, 'web');

        // Update PUT fails with 403
        $resUpdate = $this->putJson("/api/leads/{$lead->id}", [
            'score' => 77,
        ]);
        $resUpdate->assertStatus(403);

        // Convert POST fails with 403
        $resConvert = $this->postJson("/api/leads/{$lead->id}/convert", [
            'create_opportunity' => false,
        ]);
        $resConvert->assertStatus(403);
    }

    public function test_security_profile_can_delete_false_blocks_deletion(): void
    {
        $profile = $this->createTestProfile('No Delete Profile', [
            'can_read'   => true,
            'can_delete' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Delete User', $role, $profile);

        $lead = $this->createTestLead([
            'first_name'  => 'Protected',
            'last_name'   => 'Record',
            'company'     => 'Guarded Systems',
            'assigned_to' => $user->id,
        ]);

        $this->actingAs($user, 'web');

        $resDelete = $this->deleteJson("/api/leads/{$lead->id}");
        $resDelete->assertStatus(403);
        $this->assertNotNull(Lead::on('tenant')->find($lead->id));
    }

    public function test_security_profile_can_transfer_false_blocks_transfer(): void
    {
        $profile = $this->createTestProfile('No Transfer Profile', [
            'can_read'     => true,
            'can_transfer' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Transfer User', $role, $profile);

        $lead = $this->createTestLead([
            'first_name'  => 'NonTransferable',
            'last_name'   => 'Lead',
            'company'     => 'Fixed Owner LLC',
            'assigned_to' => $user->id,
        ]);

        $this->actingAs($user, 'web');

        $otherUser = TenantUser::on('tenant')->where('id', '!=', $user->id)->first();
        $resTransfer = $this->postJson("/api/leads/{$lead->id}/transfer", [
            'targetUserId' => $otherUser->id,
        ]);
        $resTransfer->assertStatus(403);
    }

    public function test_record_scope_own_enforcement(): void
    {
        $profile = $this->createTestProfile('Own Records Only', [
            'can_read'     => true,
            'can_update'   => true,
            'record_scope' => 'Own',
        ]);
        $role = Role::on('tenant')->first();
        $userA = $this->createTestUser('Scoped User A', $role, $profile);
        $userB = $this->createTestUser('Scoped User B', $role, $profile);

        $leadA = $this->createTestLead([
            'first_name'  => 'UserA',
            'last_name'   => 'Lead',
            'company'     => 'Alpha Enterprise',
            'assigned_to' => $userA->id,
            'created_by'  => $userA->id,
        ]);

        $leadB = $this->createTestLead([
            'first_name'  => 'UserB',
            'last_name'   => 'Lead',
            'company'     => 'Beta Enterprise',
            'assigned_to' => $userB->id,
            'created_by'  => $userB->id,
        ]);

        $this->actingAs($userA, 'web');

        // List API only returns User A's lead
        $resList = $this->getJson('/api/leads');
        $resList->assertStatus(200);
        $leadIds = collect($resList->json())->pluck('id')->map(fn($id) => (int)$id)->all();

        $this->assertContains((int)$leadA->id, $leadIds);
        $this->assertNotContains((int)$leadB->id, $leadIds);

        // Attempting to view or update User B's lead returns 403
        $resShow = $this->get("/leads/{$leadB->id}");
        $resShow->assertStatus(403);

        $resUpdate = $this->putJson("/api/leads/{$leadB->id}", [
            'company' => 'Attempted Hack',
        ]);
        $resUpdate->assertStatus(403);
    }

    public function test_record_scope_team_hierarchy_enforcement(): void
    {
        // Setup Role Hierarchy: Manager Role -> Rep Role
        $managerRole = Role::on('tenant')->create([
            'name'        => 'Sales Director ' . Str::random(4),
            'description' => 'Team Manager',
        ]);
        $repRole = Role::on('tenant')->create([
            'name'               => 'Sales Rep ' . Str::random(4),
            'description'        => 'Subordinate Rep',
            'reports_to_role_id' => $managerRole->id,
        ]);
        $outsideRole = Role::on('tenant')->create([
            'name'        => 'Operations Lead ' . Str::random(4),
            'description' => 'Unrelated Branch',
        ]);

        $teamProfile = $this->createTestProfile('Team Scope Profile', [
            'can_read'     => true,
            'can_update'   => true,
            'record_scope' => 'Team',
        ]);

        $managerUser = $this->createTestUser('Team Manager', $managerRole, $teamProfile);
        $repUser = $this->createTestUser('Team Rep', $repRole, $teamProfile, $managerUser->id);
        $outsideUser = $this->createTestUser('Outside User', $outsideRole, $teamProfile);

        $leadOfRep = $this->createTestLead([
            'first_name'  => 'Subordinate',
            'last_name'   => 'Lead',
            'company'     => 'Team Account',
            'assigned_to' => $repUser->id,
        ]);

        $leadOfOutside = $this->createTestLead([
            'first_name'  => 'Outside',
            'last_name'   => 'Lead',
            'company'     => 'Foreign Account',
            'assigned_to' => $outsideUser->id,
        ]);

        $this->actingAs($managerUser, 'web');

        // Manager should see subordinate rep's lead, but NOT outside user's lead
        $resList = $this->getJson('/api/leads');
        $resList->assertStatus(200);
        $leadIds = collect($resList->json())->pluck('id')->map(fn($id) => (int)$id)->all();

        $this->assertContains((int)$leadOfRep->id, $leadIds);
        $this->assertNotContains((int)$leadOfOutside->id, $leadIds);
    }

    public function test_field_permission_hidden_masks_field_and_ignores_input(): void
    {
        $profile = $this->createTestProfile('Hidden Fields Profile', [
            'can_read'   => true,
            'can_create' => true,
            'can_update' => true,
        ]);

        // Hide company
        FieldPermission::on('tenant')->create([
            'profile_id' => $profile->id,
            'module'     => 'leads',
            'field_name' => 'company',
            'permission' => 'Hidden',
        ]);

        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('Field Restricted User', $role, $profile);

        $lead = $this->createTestLead([
            'first_name'  => 'Diana',
            'last_name'   => 'Prince',
            'company'     => 'Classified Industry Corp',
            'assigned_to' => $user->id,
        ]);

        $this->actingAs($user, 'web');

        // In API list, company should be sanitized / null
        $resList = $this->getJson('/api/leads');
        $resList->assertStatus(200);
        $leadData = collect($resList->json())->firstWhere('id', $lead->id);

        $this->assertNotNull($leadData);
        $this->assertNull($leadData['company']);
        $this->assertTrue(!empty($leadData['_hidden_company']));

        // When creating a lead, hidden field input is ignored/nulled
        $resCreate = $this->postJson('/api/leads', [
            'first_name' => 'Clark',
            'last_name'  => 'Kent',
            'company'    => 'Daily Planet (Should be hidden)',
            'email'      => 'clark@dailyplanet.test',
        ]);
        $resCreate->assertStatus(201);
        $this->assertNull($resCreate->json('lead.company'));
        $this->assertTrue(!empty($resCreate->json('lead._hidden_company')));

        $newLead = Lead::on('tenant')->find($resCreate->json('lead.id'));
        $this->assertNotEquals('Daily Planet (Should be hidden)', $newLead->company);
    }

    public function test_field_permission_readonly_prevents_field_modification(): void
    {
        $profile = $this->createTestProfile('Read Only Field Profile', [
            'can_read'   => true,
            'can_update' => true,
        ]);

        // Mark rating as Read-Only
        FieldPermission::on('tenant')->create([
            'profile_id' => $profile->id,
            'module'     => 'leads',
            'field_name' => 'rating',
            'permission' => 'Read-Only',
        ]);

        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('Read Only Field User', $role, $profile);

        $lead = $this->createTestLead([
            'first_name'  => 'Barry',
            'last_name'   => 'Allen',
            'company'     => 'Central City Labs',
            'rating'      => 'Cold',
            'assigned_to' => $user->id,
        ]);

        $this->actingAs($user, 'web');

        // Attempting to update rating to 'Hot' while editing title to 'Lead Scientist'
        $resUpdate = $this->putJson("/api/leads/{$lead->id}", [
            'rating' => 'Hot',
            'title'  => 'Lead Scientist',
        ]);
        $resUpdate->assertStatus(200);

        // Verification: title was updated, but rating remained 'Cold'
        $refreshed = Lead::on('tenant')->find($lead->id);
        $this->assertEquals('Lead Scientist', $refreshed->title);
        $this->assertEquals('Cold', $refreshed->rating);
    }

    public function test_sidebar_dynamically_hides_leads_when_can_read_is_false(): void
    {
        $restrictedProfile = $this->createTestProfile('Sidebar Restricted', [
            'can_read' => false,
        ]);
        $role = Role::on('tenant')->first();
        $restrictedUser = $this->createTestUser('Sidebar Restricted User', $role, $restrictedProfile);

        // When restricted user visits Dashboard
        $this->actingAs($restrictedUser, 'web');
        $resDashboard = $this->get('/dashboard');
        $resDashboard->assertStatus(200);

        // Sidebar does NOT contain Leads navigation item
        $resDashboard->assertDontSee('title="Leads"', false);
        // But still contains other accessible modules like Dashboard
        $resDashboard->assertSee('title="Dashboard"', false);

        // Conversely, admin user DOES see Leads in sidebar
        $adminRole = Role::on('tenant')->where('name', 'like', '%admin%')->first() ?? $role;
        $adminProfile = Profile::on('tenant')->where('name', 'like', '%admin%')->first();
        $adminUser = $this->createTestUser('Sidebar Admin User', $adminRole, $adminProfile);

        $this->actingAs($adminUser, 'web');
        $resAdmin = $this->get('/dashboard');
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('title="Leads"', false);
    }
}
