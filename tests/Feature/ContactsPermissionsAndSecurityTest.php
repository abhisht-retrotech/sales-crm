<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Contact;
use App\Models\FieldPermission;
use App\Models\Profile;
use App\Models\ProfilePermission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactsPermissionsAndSecurityTest extends TestCase
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
            'email'      => "test_ct_{$unique}@example.com",
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
    protected function createTestProfile(string $name, array $contactsPerms): Profile
    {
        $profile = Profile::on('tenant')->create([
            'name'        => $name . '_' . Str::random(6),
            'description' => 'Test Profile for contacts permissions',
        ]);

        ProfilePermission::on('tenant')->create(array_merge([
            'profile_id'   => $profile->id,
            'module'       => 'contacts',
            'can_create'   => true,
            'can_read'     => true,
            'can_update'   => true,
            'can_delete'   => true,
            'can_transfer' => true,
            'record_scope' => 'All',
        ], $contactsPerms));

        return $profile;
    }

    /**
     * Helper to create a test contact record.
     */
    protected function createTestContact(array $attrs = []): Contact
    {
        $unique = Str::random(6);
        return Contact::on('tenant')->create(array_merge([
            'first_name'   => 'Test',
            'last_name'    => 'Contact_' . $unique,
            'email'        => "contact_{$unique}@example.com",
            'phone'        => '+1 (555) 012-3456',
            'title'        => 'Director of Engineering',
            'department'   => 'R&D',
            'contact_role' => 'Decision Maker',
            'is_primary'   => true,
        ], $attrs));
    }

    public function test_admin_user_has_unrestricted_full_access_to_contacts(): void
    {
        $adminRole = Role::on('tenant')->where('name', 'like', '%admin%')->first()
            ?? Role::on('tenant')->first();
        $adminProfile = Profile::on('tenant')->where('name', 'Administrator')->first()
            ?? Profile::on('tenant')->where('name', 'like', '%admin%')->first()
            ?? Profile::on('tenant')->create(['name' => 'Administrator', 'description' => 'Admin profile']);
        $adminUser = $this->createTestUser('Master Contacts Admin', $adminRole, $adminProfile);

        $contact = $this->createTestContact(['first_name' => 'Root', 'last_name' => 'AdminContact', 'assigned_to' => $adminUser->id]);

        $this->actingAs($adminUser, 'web');

        // 1. List contacts
        $resList = $this->getJson('/api/contacts');
        $resList->assertStatus(200);

        // 2. View single contact
        $resShow = $this->getJson("/api/contacts/{$contact->id}");
        $resShow->assertStatus(200);
        $resShow->assertJsonPath('first_name', 'Root');

        // 3. Create contact
        $resCreate = $this->postJson('/api/contacts', [
            'first_name' => 'New',
            'last_name'  => 'Employee',
            'email'      => 'new.emp@example.com',
            'title'      => 'VP of Sales',
        ]);
        $resCreate->assertStatus(201);
        $createdId = $resCreate->json('contact.id');

        // 4. Update contact
        $resUpdate = $this->putJson("/api/contacts/{$createdId}", [
            'title' => 'Senior VP of Sales',
        ]);
        $resUpdate->assertStatus(200);

        // 5. Delete contact
        $resDelete = $this->deleteJson("/api/contacts/{$createdId}");
        $resDelete->assertStatus(200);
    }

    public function test_can_read_false_denies_contacts_access(): void
    {
        $profile = $this->createTestProfile('No Read Profile', [
            'can_read' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Read Contacts User', $role, $profile);

        $contact = $this->createTestContact(['assigned_to' => $user->id]);

        $this->actingAs($user, 'web');

        // Listing contacts returns 403 Forbidden
        $resList = $this->getJson('/api/contacts');
        $resList->assertStatus(403);
        $resList->assertJson(['error' => 'Access denied']);

        // Viewing single contact returns 403 Forbidden
        $resShow = $this->getJson("/api/contacts/{$contact->id}");
        $resShow->assertStatus(403);

        // Contact 360 returns 403 Forbidden
        $res360 = $this->getJson("/api/contacts/{$contact->id}/360");
        $res360->assertStatus(403);

        // Web view returns 403 Forbidden
        $resWeb = $this->get('/contacts');
        $resWeb->assertStatus(403);
    }

    public function test_can_create_false_denies_contact_creation(): void
    {
        $profile = $this->createTestProfile('No Create Profile', [
            'can_read'   => true,
            'can_create' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Create Contacts User', $role, $profile);

        $this->actingAs($user, 'web');

        $resCreate = $this->postJson('/api/contacts', [
            'first_name' => 'Forbidden',
            'last_name'  => 'Contact',
            'email'      => 'forbidden.contact@example.com',
        ]);
        $resCreate->assertStatus(403);
        $resCreate->assertJson([
            'error'   => 'Access denied',
            'message' => 'You do not have permission to create contacts',
        ]);
    }

    public function test_can_update_false_denies_contact_update_and_files(): void
    {
        $profile = $this->createTestProfile('No Update Profile', [
            'can_read'   => true,
            'can_update' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Update Contacts User', $role, $profile);

        $contact = $this->createTestContact(['assigned_to' => $user->id]);

        $this->actingAs($user, 'web');

        $resUpdate = $this->putJson("/api/contacts/{$contact->id}", [
            'title' => 'Updated Title Should Fail',
        ]);
        $resUpdate->assertStatus(403);
        $resUpdate->assertJson([
            'error'   => 'Access denied',
            'message' => 'You do not have permission to edit contacts',
        ]);
    }

    public function test_can_delete_false_denies_contact_deletion(): void
    {
        $profile = $this->createTestProfile('No Delete Profile', [
            'can_read'   => true,
            'can_delete' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Delete Contacts User', $role, $profile);

        $contact = $this->createTestContact(['assigned_to' => $user->id]);

        $this->actingAs($user, 'web');

        $resDelete = $this->deleteJson("/api/contacts/{$contact->id}");
        $resDelete->assertStatus(403);
        $resDelete->assertJson([
            'error'   => 'Access denied',
            'message' => 'You do not have permission to delete contacts',
        ]);

        // Verify contact was NOT deleted
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
        ], 'tenant');
    }

    public function test_can_transfer_false_denies_contact_transfer(): void
    {
        $profile = $this->createTestProfile('No Transfer Profile', [
            'can_read'     => true,
            'can_transfer' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Transfer Contacts User', $role, $profile);
        $targetUser = $this->createTestUser('Target User', $role, $profile);

        $contact = $this->createTestContact(['assigned_to' => $user->id]);

        $this->actingAs($user, 'web');

        $resTransfer = $this->postJson("/api/contacts/{$contact->id}/transfer", [
            'assigned_to' => $targetUser->id,
        ]);
        $resTransfer->assertStatus(403);
        $resTransfer->assertJson([
            'error'   => 'Access denied',
            'message' => 'You do not have permission to transfer contact ownership',
        ]);
    }

    public function test_record_scope_own_restricts_to_own_contacts(): void
    {
        $profile = $this->createTestProfile('Own Scope Profile', [
            'can_read'     => true,
            'record_scope' => 'Own',
        ]);
        $role = Role::on('tenant')->first();
        $ownerUser = $this->createTestUser('Contact Owner User', $role, $profile);
        $otherUser = $this->createTestUser('Other Contact User', $role, $profile);

        $myContact = $this->createTestContact([
            'first_name'  => 'MyOwn',
            'last_name'   => 'Contact',
            'assigned_to' => $ownerUser->id,
        ]);

        $otherContact = $this->createTestContact([
            'first_name'  => 'Other',
            'last_name'   => 'Contact',
            'assigned_to' => $otherUser->id,
        ]);

        $this->actingAs($ownerUser, 'web');

        // Listing contacts must contain myContact and NOT otherContact
        $resList = $this->getJson('/api/contacts');
        $resList->assertStatus(200);
        $contactIds = collect($resList->json())->pluck('id')->all();

        $this->assertContains($myContact->id, $contactIds);
        $this->assertNotContains($otherContact->id, $contactIds);

        // Attempting to view other user's contact returns 403 Forbidden
        $resShowOther = $this->getJson("/api/contacts/{$otherContact->id}");
        $resShowOther->assertStatus(403);
    }

    public function test_field_level_security_hidden_masks_sensitive_fields(): void
    {
        $profile = $this->createTestProfile('FLS Hidden Profile', [
            'can_read' => true,
        ]);
        $role = Role::on('tenant')->first();

        // Configure phone as Hidden for this profile
        FieldPermission::on('tenant')->updateOrCreate(
            ['profile_id' => $profile->id, 'module' => 'contacts', 'field_name' => 'phone'],
            ['permission' => 'Hidden']
        );

        $user = $this->createTestUser('FLS Hidden User', $role, $profile);

        $contact = $this->createTestContact([
            'first_name'  => 'Sarah',
            'last_name'   => 'Connor',
            'phone'       => '+1 (800) 999-8888',
            'assigned_to' => $user->id,
        ]);

        $this->actingAs($user, 'web');

        $resShow = $this->getJson("/api/contacts/{$contact->id}");
        $resShow->assertStatus(200);

        // Field value should be masked/null and have _hidden flag set
        $this->assertNull($resShow->json('phone'));
        $this->assertTrue($resShow->json('_hidden_phone'));
    }

    public function test_field_level_security_read_only_ignores_modifications(): void
    {
        $profile = $this->createTestProfile('FLS Read Only Profile', [
            'can_read'   => true,
            'can_update' => true,
        ]);
        $role = Role::on('tenant')->first();

        // Configure department as Read-Only
        FieldPermission::on('tenant')->updateOrCreate(
            ['profile_id' => $profile->id, 'module' => 'contacts', 'field_name' => 'department'],
            ['permission' => 'Read-Only']
        );

        $user = $this->createTestUser('FLS Read Only User', $role, $profile);

        $contact = $this->createTestContact([
            'first_name'  => 'John',
            'last_name'   => 'Doe',
            'department'  => 'Legal & Compliance',
            'title'       => 'Counsel',
            'assigned_to' => $user->id,
        ]);

        $this->actingAs($user, 'web');

        // Attempt to update both department and title
        $resUpdate = $this->putJson("/api/contacts/{$contact->id}", [
            'department' => 'Marketing',
            'title'      => 'Senior Counsel',
        ]);
        $resUpdate->assertStatus(200);

        // Verify: title was updated, but department remained 'Legal & Compliance'
        $refreshed = Contact::on('tenant')->find($contact->id);
        $this->assertEquals('Senior Counsel', $refreshed->title);
        $this->assertEquals('Legal & Compliance', $refreshed->department);
    }

    public function test_sidebar_dynamically_hides_contacts_when_can_read_is_false(): void
    {
        $restrictedProfile = $this->createTestProfile('Sidebar Restricted Contacts', [
            'can_read' => false,
        ]);
        $role = Role::on('tenant')->first();
        $restrictedUser = $this->createTestUser('Sidebar Restricted Contacts User', $role, $restrictedProfile);

        // Restricted user visits Dashboard
        $this->actingAs($restrictedUser, 'web');
        $resDashboard = $this->get('/dashboard');
        $resDashboard->assertStatus(200);

        // Sidebar does NOT contain Contacts navigation item
        $resDashboard->assertDontSee('title="Contacts"', false);
        // But still contains accessible modules like Dashboard
        $resDashboard->assertSee('title="Dashboard"', false);

        // Admin user DOES see Contacts in sidebar
        $adminRole = Role::on('tenant')->where('name', 'like', '%admin%')->first() ?? $role;
        $adminProfile = Profile::on('tenant')->where('name', 'Administrator')->first()
            ?? Profile::on('tenant')->where('name', 'like', '%admin%')->first()
            ?? Profile::on('tenant')->create(['name' => 'Administrator', 'description' => 'Admin profile']);
        $adminUser = $this->createTestUser('Sidebar Admin Contacts User', $adminRole, $adminProfile);

        $this->actingAs($adminUser, 'web');
        $resAdmin = $this->get('/dashboard');
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('title="Contacts"', false);
    }
}
