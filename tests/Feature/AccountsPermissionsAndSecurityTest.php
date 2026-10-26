<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\FieldPermission;
use App\Models\Profile;
use App\Models\ProfilePermission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountsPermissionsAndSecurityTest extends TestCase
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
            'email'      => "test_acc_{$unique}@example.com",
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
    protected function createTestProfile(string $name, array $accountsPerms): Profile
    {
        $profile = Profile::on('tenant')->create([
            'name'        => $name . '_' . Str::random(6),
            'description' => 'Test Profile for accounts permissions',
        ]);

        ProfilePermission::on('tenant')->create(array_merge([
            'profile_id'   => $profile->id,
            'module'       => 'accounts',
            'can_create'   => true,
            'can_read'     => true,
            'can_update'   => true,
            'can_delete'   => true,
            'can_transfer' => true,
            'record_scope' => 'All',
        ], $accountsPerms));

        return $profile;
    }

    /**
     * Helper to create a test account record.
     */
    protected function createTestAccount(array $attrs = []): Account
    {
        $unique = Str::random(6);
        return Account::on('tenant')->create(array_merge([
            'name'             => 'Test Account ' . $unique,
            'type'             => 'Customer',
            'industry'         => 'Technology',
            'website'          => "https://acc_{$unique}.com",
            'phone'            => '+1 (555) 010-0000',
            'currency'         => 'USD',
            'payment_terms'    => 'Net 30',
            'credit_limit'     => 50000.00,
            'annual_revenue'   => 500000.00,
            'billing_address'  => '123 Test St, Silicon Valley, CA',
            'shipping_address' => '123 Test St, Silicon Valley, CA',
        ], $attrs));
    }

    public function test_admin_user_has_unrestricted_full_access_to_accounts(): void
    {
        $adminRole = Role::on('tenant')->where('name', 'like', '%admin%')->first()
            ?? Role::on('tenant')->first();
        $adminProfile = Profile::on('tenant')->where('name', 'Administrator')->first()
            ?? Profile::on('tenant')->where('name', 'like', '%admin%')->first()
            ?? Profile::on('tenant')->create(['name' => 'Administrator', 'description' => 'Admin profile']);
        $adminUser = $this->createTestUser('Master Accounts Admin', $adminRole, $adminProfile);

        $account = $this->createTestAccount(['name' => 'Admin Accessible Account', 'owner_id' => $adminUser->id]);

        $this->actingAs($adminUser, 'web');

        // 1. List accounts
        $resList = $this->getJson('/api/accounts');
        $resList->assertStatus(200);

        // 2. View single account
        $resShow = $this->getJson("/api/accounts/{$account->id}");
        $resShow->assertStatus(200);
        $resShow->assertJsonPath('name', 'Admin Accessible Account');

        // 3. Create account
        $resCreate = $this->postJson('/api/accounts', [
            'name'     => 'Created By Admin Corp',
            'type'     => 'Partner',
            'industry' => 'Aerospace',
        ]);
        $resCreate->assertStatus(201);
        $createdId = $resCreate->json('account.id');

        // 4. Update account
        $resUpdate = $this->putJson("/api/accounts/{$createdId}", [
            'industry' => 'Defense & Space',
        ]);
        $resUpdate->assertStatus(200);

        // 5. Delete account
        $resDelete = $this->deleteJson("/api/accounts/{$createdId}");
        $resDelete->assertStatus(200);
    }

    public function test_can_read_false_denies_accounts_access(): void
    {
        $profile = $this->createTestProfile('No Read Profile', [
            'can_read' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Read Accounts User', $role, $profile);

        $account = $this->createTestAccount(['owner_id' => $user->id]);

        $this->actingAs($user, 'web');

        // Listing accounts returns 403 Forbidden
        $resList = $this->getJson('/api/accounts');
        $resList->assertStatus(403);
        $resList->assertJson(['error' => 'Access denied']);

        // Viewing single account returns 403 Forbidden
        $resShow = $this->getJson("/api/accounts/{$account->id}");
        $resShow->assertStatus(403);

        // Account 360 returns 403 Forbidden
        $res360 = $this->getJson("/api/accounts/{$account->id}/360");
        $res360->assertStatus(403);

        // Web view returns 403 Forbidden
        $resWeb = $this->get('/accounts');
        $resWeb->assertStatus(403);
    }

    public function test_can_create_false_denies_account_creation(): void
    {
        $profile = $this->createTestProfile('No Create Profile', [
            'can_read'   => true,
            'can_create' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Create Accounts User', $role, $profile);

        $this->actingAs($user, 'web');

        $resCreate = $this->postJson('/api/accounts', [
            'name' => 'Forbidden Account Corp',
            'type' => 'Customer',
        ]);
        $resCreate->assertStatus(403);
        $resCreate->assertJson([
            'error'   => 'Access denied',
            'message' => 'You do not have permission to create accounts',
        ]);
    }

    public function test_can_update_false_denies_account_update_and_files(): void
    {
        $profile = $this->createTestProfile('No Update Profile', [
            'can_read'   => true,
            'can_update' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Update Accounts User', $role, $profile);

        $account = $this->createTestAccount(['owner_id' => $user->id]);

        $this->actingAs($user, 'web');

        $resUpdate = $this->putJson("/api/accounts/{$account->id}", [
            'name' => 'Attempted Edit Name',
        ]);
        $resUpdate->assertStatus(403);
        $resUpdate->assertJson([
            'error'   => 'Access denied',
            'message' => 'You do not have permission to edit accounts',
        ]);
    }

    public function test_can_delete_false_denies_account_deletion(): void
    {
        $profile = $this->createTestProfile('No Delete Profile', [
            'can_read'   => true,
            'can_delete' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Delete Accounts User', $role, $profile);

        $account = $this->createTestAccount(['owner_id' => $user->id]);

        $this->actingAs($user, 'web');

        $resDelete = $this->deleteJson("/api/accounts/{$account->id}");
        $resDelete->assertStatus(403);
        $resDelete->assertJson([
            'error'   => 'Access denied',
            'message' => 'You do not have permission to delete accounts',
        ]);

        // Verify account was NOT deleted
        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
        ], 'tenant');
    }

    public function test_can_transfer_false_denies_account_ownership_transfer(): void
    {
        $profile = $this->createTestProfile('No Transfer Profile', [
            'can_read'     => true,
            'can_transfer' => false,
        ]);
        $role = Role::on('tenant')->first();
        $user = $this->createTestUser('No Transfer Accounts User', $role, $profile);

        $account = $this->createTestAccount(['owner_id' => $user->id]);

        $this->actingAs($user, 'web');

        $resTransfer = $this->postJson("/api/accounts/{$account->id}/transfer", [
            'assigned_to' => 'New Rep Name',
        ]);
        $resTransfer->assertStatus(403);
        $resTransfer->assertJson([
            'error'   => 'Access denied',
            'message' => 'You do not have permission to transfer account ownership',
        ]);
    }

    public function test_record_scope_own_restricts_to_own_accounts(): void
    {
        $profile = $this->createTestProfile('Own Scope Profile', [
            'can_read'     => true,
            'record_scope' => 'Own',
        ]);
        $role = Role::on('tenant')->first();
        $ownerUser = $this->createTestUser('Account Owner User', $role, $profile);
        $otherUser = $this->createTestUser('Other Account User', $role, $profile);

        $myAccount = $this->createTestAccount([
            'name'     => 'My Own Private Account',
            'owner_id' => $ownerUser->id,
        ]);

        $otherAccount = $this->createTestAccount([
            'name'     => 'Someone Else Private Account',
            'owner_id' => $otherUser->id,
        ]);

        $this->actingAs($ownerUser, 'web');

        // Listing accounts must contain myAccount and NOT contain otherAccount
        $resList = $this->getJson('/api/accounts');
        $resList->assertStatus(200);
        $accountIds = collect($resList->json())->pluck('id')->all();

        $this->assertContains($myAccount->id, $accountIds);
        $this->assertNotContains($otherAccount->id, $accountIds);

        // Attempting to view other user's account returns 403 Forbidden
        $resShowOther = $this->getJson("/api/accounts/{$otherAccount->id}");
        $resShowOther->assertStatus(403);
    }

    public function test_field_level_security_hidden_masks_sensitive_financials(): void
    {
        $profile = $this->createTestProfile('FLS Hidden Profile', [
            'can_read' => true,
        ]);
        $role = Role::on('tenant')->first();

        // Configure credit_limit and annual_revenue as Hidden for this profile
        FieldPermission::on('tenant')->updateOrCreate(
            ['profile_id' => $profile->id, 'module' => 'accounts', 'field_name' => 'credit_limit'],
            ['permission' => 'Hidden']
        );
        FieldPermission::on('tenant')->updateOrCreate(
            ['profile_id' => $profile->id, 'module' => 'accounts', 'field_name' => 'annual_revenue'],
            ['permission' => 'Hidden']
        );

        $user = $this->createTestUser('FLS Hidden User', $role, $profile);

        $account = $this->createTestAccount([
            'name'           => 'Secret Financials Corp',
            'credit_limit'   => 850000.00,
            'annual_revenue' => 15000000.00,
            'owner_id'       => $user->id,
        ]);

        $this->actingAs($user, 'web');

        $resShow = $this->getJson("/api/accounts/{$account->id}");
        $resShow->assertStatus(200);

        // Field values should be masked/null and have _hidden flag set
        $this->assertNull($resShow->json('credit_limit'));
        $this->assertTrue($resShow->json('_hidden_credit_limit'));
        $this->assertNull($resShow->json('annual_revenue'));
        $this->assertTrue($resShow->json('_hidden_annual_revenue'));
    }

    public function test_field_level_security_read_only_ignores_modifications(): void
    {
        $profile = $this->createTestProfile('FLS Read Only Profile', [
            'can_read'   => true,
            'can_update' => true,
        ]);
        $role = Role::on('tenant')->first();

        // Configure payment_terms as Read-Only
        FieldPermission::on('tenant')->updateOrCreate(
            ['profile_id' => $profile->id, 'module' => 'accounts', 'field_name' => 'payment_terms'],
            ['permission' => 'Read-Only']
        );

        $user = $this->createTestUser('FLS Read Only User', $role, $profile);

        $account = $this->createTestAccount([
            'name'          => 'Terms Protected Corp',
            'payment_terms' => 'Net 30',
            'industry'      => 'Manufacturing',
            'owner_id'      => $user->id,
        ]);

        $this->actingAs($user, 'web');

        // Attempt to update both payment_terms and industry
        $resUpdate = $this->putJson("/api/accounts/{$account->id}", [
            'payment_terms' => 'Advance Wire Only',
            'industry'      => 'Renewable Energy',
        ]);
        $resUpdate->assertStatus(200);

        // Verify: industry was updated, but payment_terms remained 'Net 30'
        $refreshed = Account::on('tenant')->find($account->id);
        $this->assertEquals('Renewable Energy', $refreshed->industry);
        $this->assertEquals('Net 30', $refreshed->payment_terms);
    }

    public function test_sidebar_dynamically_hides_accounts_when_can_read_is_false(): void
    {
        $restrictedProfile = $this->createTestProfile('Sidebar Restricted Accounts', [
            'can_read' => false,
        ]);
        $role = Role::on('tenant')->first();
        $restrictedUser = $this->createTestUser('Sidebar Restricted Accounts User', $role, $restrictedProfile);

        // Restricted user visits Dashboard
        $this->actingAs($restrictedUser, 'web');
        $resDashboard = $this->get('/dashboard');
        $resDashboard->assertStatus(200);

        // Sidebar does NOT contain Accounts navigation item
        $resDashboard->assertDontSee('title="Accounts"', false);
        // But still contains accessible modules like Dashboard
        $resDashboard->assertSee('title="Dashboard"', false);

        // Admin user DOES see Accounts in sidebar
        $adminRole = Role::on('tenant')->where('name', 'like', '%admin%')->first() ?? $role;
        $adminProfile = Profile::on('tenant')->where('name', 'Administrator')->first()
            ?? Profile::on('tenant')->where('name', 'like', '%admin%')->first()
            ?? Profile::on('tenant')->create(['name' => 'Administrator', 'description' => 'Admin profile']);
        $adminUser = $this->createTestUser('Sidebar Admin Accounts User', $adminRole, $adminProfile);

        $this->actingAs($adminUser, 'web');
        $resAdmin = $this->get('/dashboard');
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('title="Accounts"', false);
    }

    public function test_header_restricts_settings_and_schema_to_administrators_only(): void
    {
        $role = Role::on('tenant')->first();
        $standardProfile = $this->createTestProfile('Standard Employee Profile', [
            'can_read' => true,
        ]);
        $standardUser = $this->createTestUser('Standard Employee User', $role, $standardProfile);

        // 1. Non-admin standard user visiting dashboard
        $this->actingAs($standardUser, 'web');
        $resStandard = $this->get('/dashboard');
        $resStandard->assertStatus(200);

        // In header, Setting & Schema link should NOT be shown
        $resStandard->assertDontSee('<span>Setting &amp; Schema</span>', false);

        // 2. Tenant Administrator visiting dashboard
        $adminRole = Role::on('tenant')->where('name', 'like', '%admin%')->first() ?? $role;
        $adminProfile = Profile::on('tenant')->where('name', 'Administrator')->first()
            ?? Profile::on('tenant')->where('name', 'like', '%admin%')->first()
            ?? Profile::on('tenant')->create(['name' => 'Administrator', 'description' => 'Admin profile']);
        $adminUser = $this->createTestUser('Header Admin User', $adminRole, $adminProfile);

        $this->actingAs($adminUser, 'web');
        $resAdmin = $this->get('/dashboard');
        $resAdmin->assertStatus(200);

        // In header, Setting & Schema link IS shown to tenant admin
        $resAdmin->assertSee('<span>Setting &amp; Schema</span>', false);
    }
}
