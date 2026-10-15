<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Role;
use App\Models\Profile;
use App\Models\ProfilePermission;

class RolesPermissionsTest extends TestCase
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

    public function test_roles_permissions_page_renders_with_roles_and_profiles(): void
    {
        $response = $this->get('/admin/roles-permissions');

        $response->assertStatus(200);
        $response->assertSee('Roles Hierarchy');
        $response->assertSee('Security Profiles');
        $response->assertSee('Create Role');
        $response->assertSee('Create Profile');
        $response->assertSee('roleModal');
        $response->assertSee('profileModal');
    }

    public function test_record_rights_page_renders_with_modules(): void
    {
        $response = $this->get('/admin/record-rights');

        $response->assertStatus(200);
        $response->assertSee('Record-Level Rights Matrix');
        $response->assertSee('Configuring Profile:');
        $response->assertSee('matrixTableBody');
        $response->assertSee('Save Matrix');
    }

    public function test_get_roles_api_returns_hierarchy(): void
    {
        $response = $this->getJson('/api/settings/roles');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'description',
                'reports_to_role_id',
            ]
        ]);
    }

    public function test_create_update_delete_role_api(): void
    {
        $ceoRole = Role::on('tenant')->first();

        // 1. Create
        $createRes = $this->postJson('/api/settings/roles', [
            'name'               => 'Test Regional Lead',
            'description'        => 'Manages regional sales pod',
            'reports_to_role_id' => $ceoRole?->id,
        ]);

        $createRes->assertStatus(201);
        $createRes->assertJson([
            'name' => 'Test Regional Lead',
        ]);
        $roleId = $createRes->json('id');

        // 2. Update
        $updateRes = $this->putJson("/api/settings/roles/{$roleId}", [
            'name'               => 'Senior Regional Lead',
            'description'        => 'Updated description',
            'reports_to_role_id' => $ceoRole?->id,
        ]);

        $updateRes->assertStatus(200);
        $updateRes->assertJson([
            'name' => 'Senior Regional Lead',
        ]);

        // 3. Delete
        $deleteRes = $this->deleteJson("/api/settings/roles/{$roleId}");
        $deleteRes->assertStatus(200);
        $deleteRes->assertJson(['success' => true]);

        $this->assertNull(Role::on('tenant')->find($roleId));
    }

    public function test_get_profiles_api(): void
    {
        $response = $this->getJson('/api/settings/profiles');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'description',
                'is_admin',
            ]
        ]);
    }

    public function test_create_update_delete_profile_api(): void
    {
        // 1. Create custom profile
        $createRes = $this->postJson('/api/settings/profiles', [
            'name'                => 'Branch Officer',
            'description'         => 'Local branch operations profile',
            'ip_whitelist'        => '192.168.1.100',
            'login_time_start'    => '09:00',
            'login_time_end'      => '18:00',
            'surprise_me_enabled' => false,
            'is_admin'            => false,
        ]);

        $createRes->assertStatus(201);
        $createRes->assertJson([
            'name'         => 'Branch Officer',
            'ip_whitelist' => '192.168.1.100',
        ]);
        $profileId = $createRes->json('id');

        // 2. Update profile
        $updateRes = $this->putJson("/api/settings/profiles/{$profileId}", [
            'name'                => 'Senior Branch Officer',
            'description'         => 'Updated branch profile',
            'ip_whitelist'        => '192.168.1.0/24',
            'login_time_start'    => '08:00',
            'login_time_end'      => '20:00',
            'surprise_me_enabled' => true,
        ]);

        $updateRes->assertStatus(200);
        $updateRes->assertJson([
            'name'         => 'Senior Branch Officer',
            'ip_whitelist' => '192.168.1.0/24',
        ]);

        // 3. Delete custom profile
        $deleteRes = $this->deleteJson("/api/settings/profiles/{$profileId}");
        $deleteRes->assertStatus(200);
        $deleteRes->assertJson(['success' => true]);

        $this->assertNull(Profile::on('tenant')->find($profileId));
    }

    public function test_primary_admin_profile_cannot_be_deleted(): void
    {
        $adminProfile = Profile::on('tenant')->where('is_admin', 1)->first();
        if (!$adminProfile) {
            $adminProfile = Profile::on('tenant')->create([
                'name'     => 'System Administrator',
                'is_admin' => 1,
            ]);
        }

        $response = $this->deleteJson("/api/settings/profiles/{$adminProfile->id}");
        $response->assertStatus(422);
    }

    public function test_get_permissions_api_returns_record_and_field_permissions(): void
    {
        $profile = Profile::on('tenant')->first();
        $this->assertNotNull($profile);

        $response = $this->getJson("/api/settings/permissions/{$profile->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'profileId',
            'recordPermissions' => [
                '*' => [
                    'id',
                    'profile_id',
                    'module',
                    'can_create',
                    'can_read',
                    'can_update',
                    'can_delete',
                    'can_transfer',
                    'record_scope',
                ]
            ],
            'fieldPermissions',
        ]);
    }

    public function test_update_permissions_api_persists_crud_and_scope(): void
    {
        $profile = Profile::on('tenant')->where('is_admin', 0)->first()
            ?? Profile::on('tenant')->first();
        $this->assertNotNull($profile);

        $response = $this->putJson("/api/settings/permissions/{$profile->id}", [
            'recordPermissions' => [
                [
                    'module'       => 'leads',
                    'can_create'   => true,
                    'can_read'     => true,
                    'can_update'   => true,
                    'can_delete'   => false,
                    'can_transfer' => true,
                    'record_scope' => 'Team',
                ],
                [
                    'module'       => 'opportunities',
                    'can_create'   => true,
                    'can_read'     => true,
                    'can_update'   => true,
                    'can_delete'   => true,
                    'can_transfer' => false,
                    'record_scope' => 'Own',
                ]
            ],
            'fieldPermissions' => [
                [
                    'module'     => 'leads',
                    'field_name' => 'annual_revenue',
                    'permission' => 'Hidden',
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        // Verify stored in tenant DB
        $leadPerm = ProfilePermission::on('tenant')
            ->where('profile_id', $profile->id)
            ->where('module', 'leads')
            ->first();

        $this->assertNotNull($leadPerm);
        $this->assertTrue((bool)$leadPerm->can_create);
        $this->assertTrue((bool)$leadPerm->can_transfer);
        $this->assertFalse((bool)$leadPerm->can_delete);
        $this->assertEquals('Team', $leadPerm->record_scope);

        // Verify FieldPermission stored in tenant DB
        $fieldPerm = \App\Models\FieldPermission::on('tenant')
            ->where('profile_id', $profile->id)
            ->where('module', 'leads')
            ->where('field_name', 'annual_revenue')
            ->first();

        $this->assertNotNull($fieldPerm);
        $this->assertEquals('Hidden', $fieldPerm->permission);
    }

    public function test_field_rights_page_renders_with_modules(): void
    {
        $response = $this->get('/admin/field-rights');

        $response->assertStatus(200);
        $response->assertSee('Field-Level Security (FLS)');
        $response->assertSee('Configuring Profile:');
        $response->assertSee('flsTableBody');
        $response->assertSee('Save Field Security');
        $response->assertSee('row-leads-first_name');
        $response->assertSee('row-accounts-credit_limit');
        $response->assertSee('⚠️ Sensitive');
        $response->assertSee('Read/Write');
    }

    public function test_upsert_single_field_permission_api(): void
    {
        $profile = Profile::on('tenant')->first();
        $this->assertNotNull($profile);

        $response = $this->postJson("/api/settings/permissions/{$profile->id}/field", [
            'module'     => 'leads',
            'field_name' => 'phone',
            'permission' => 'Hidden',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'    => true,
            'permission' => [
                'module'     => 'leads',
                'field_name' => 'phone',
                'permission' => 'Hidden',
            ],
        ]);

        $this->assertDatabaseHas('field_permissions', [
            'profile_id' => $profile->id,
            'module'     => 'leads',
            'field_name' => 'phone',
            'permission' => 'Hidden',
        ], 'tenant');
    }

    public function test_upsert_single_module_permission_api(): void
    {
        $profile = Profile::on('tenant')->first();
        $this->assertNotNull($profile);

        $response = $this->postJson("/api/settings/permissions/{$profile->id}/module", [
            'module'       => 'obj_warehouses',
            'can_create'   => true,
            'can_read'     => true,
            'can_update'   => true,
            'can_delete'   => false,
            'can_transfer' => true,
            'record_scope' => 'Team',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'permission' => [
                'module'       => 'obj_warehouses',
                'can_create'   => 1,
                'can_delete'   => 0,
                'record_scope' => 'Team',
            ]
        ]);

        $this->assertDatabaseHas('profile_permissions', [
            'profile_id' => $profile->id,
            'module'     => 'obj_warehouses',
            'can_create' => 1,
        ], 'tenant');
    }

    public function test_delete_module_permission_api(): void
    {
        $profile = Profile::on('tenant')->first();
        $this->assertNotNull($profile);

        ProfilePermission::on('tenant')->updateOrCreate(
            ['profile_id' => $profile->id, 'module' => 'temp_module_xyz'],
            ['can_create' => 1, 'can_read' => 1]
        );

        $response = $this->deleteJson("/api/settings/permissions/{$profile->id}/module/temp_module_xyz");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('profile_permissions', [
            'profile_id' => $profile->id,
            'module'     => 'temp_module_xyz',
        ], 'tenant');
    }

    public function test_clone_permissions_api(): void
    {
        $adminProfile = Profile::on('tenant')->where('is_admin', 1)->first() ?? Profile::on('tenant')->first();
        $targetProfile = Profile::on('tenant')->where('id', '!=', $adminProfile->id)->first();
        if (!$targetProfile) {
            $targetProfile = Profile::on('tenant')->create([
                'name'        => 'Clone Target Profile',
                'description' => 'Target for cloning rights',
                'is_admin'    => 0,
            ]);
        }

        $response = $this->postJson("/api/settings/permissions/{$targetProfile->id}/clone", [
            'source_profile_id'         => $adminProfile->id,
            'include_field_permissions' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Target profile now has permissions from admin
        $targetPerm = ProfilePermission::on('tenant')
            ->where('profile_id', $targetProfile->id)
            ->where('module', 'leads')
            ->first();

        $this->assertNotNull($targetPerm);
        $this->assertTrue((bool)$targetPerm->can_create);
    }

    public function test_reset_permissions_api(): void
    {
        $profile = Profile::on('tenant')->where('name', 'like', '%Auditor%')->first()
            ?? Profile::on('tenant')->where('is_admin', 0)->first();
        $this->assertNotNull($profile);

        // Manually tamper with permission
        ProfilePermission::on('tenant')->updateOrCreate(
            ['profile_id' => $profile->id, 'module' => 'leads'],
            ['can_delete' => 1]
        );

        $response = $this->postJson("/api/settings/permissions/{$profile->id}/reset");
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // If auditor or read-only, can_delete should be 0 after reset
        $perm = ProfilePermission::on('tenant')
            ->where('profile_id', $profile->id)
            ->where('module', 'leads')
            ->first();

        $this->assertNotNull($perm);
    }
}
