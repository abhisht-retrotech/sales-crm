<?php

namespace Tests\Feature;

use App\Models\CustomField;
use App\Models\Lead;
use App\Models\Profile;
use App\Models\ProfilePermission;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Services\Leads\LeadSecurityService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class LeadApprovalWorkflowTest extends TestCase
{
    protected ?Tenant $tenant = null;
    protected ?TenantUser $adminUser = null;
    protected ?TenantUser $managerUser = null;
    protected ?TenantUser $salesRepUser = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::on('mysql')->first();
        if ($this->tenant) {
            $this->tenant->connect();
        }

        $rnd = Str::random(6);

        // Ensure Admin Profile
        $adminProfile = Profile::on('tenant')->firstOrCreate(
            ['name' => 'Administrator Profile ' . $rnd],
            ['description' => 'Full Administrative Access']
        );
        ProfilePermission::on('tenant')->updateOrCreate(
            ['profile_id' => $adminProfile->id, 'module' => 'leads'],
            [
                'can_read' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true,
                'record_scope' => 'all',
            ]
        );

        // Ensure Sales Rep Profile
        $repProfile = Profile::on('tenant')->firstOrCreate(
            ['name' => 'Standard Rep Profile ' . $rnd],
            ['description' => 'Standard Sales Representative']
        );
        ProfilePermission::on('tenant')->updateOrCreate(
            ['profile_id' => $repProfile->id, 'module' => 'leads'],
            [
                'can_read' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => false,
                'record_scope' => 'all',
            ]
        );

        // Ensure Manager Role
        $managerRole = Role::on('tenant')->firstOrCreate(
            ['name' => 'Sales Manager ' . $rnd],
            ['description' => 'Regional Sales Manager']
        );

        // Ensure Rep Role
        $repRole = Role::on('tenant')->firstOrCreate(
            ['name' => 'Sales Rep ' . $rnd],
            ['description' => 'Junior Representative']
        );

        // 1. Admin User
        $this->adminUser = TenantUser::on('tenant')->create([
            'name' => "Admin {$rnd}",
            'email' => "admin_{$rnd}@example.com",
            'password' => Hash::make('password'),
            'profile_id' => $adminProfile->id,
            'status' => 'Active',
        ]);

        // 2. Manager User
        $this->managerUser = TenantUser::on('tenant')->create([
            'name' => "Manager {$rnd}",
            'email' => "manager_{$rnd}@example.com",
            'password' => Hash::make('password'),
            'profile_id' => $repProfile->id,
            'role_id' => $managerRole->id,
            'status' => 'Active',
        ]);

        // 3. Sales Rep User (subordinate to manager)
        $this->salesRepUser = TenantUser::on('tenant')->create([
            'name' => "Rep {$rnd}",
            'email' => "rep_{$rnd}@example.com",
            'password' => Hash::make('password'),
            'profile_id' => $repProfile->id,
            'role_id' => $repRole->id,
            'manager_id' => $this->managerUser->id,
            'status' => 'Active',
        ]);

        // Ensure dynamic custom fields and active lead approval workflow exist
        app(LeadSecurityService::class)->ensureCustomFields();
        app(LeadSecurityService::class)->ensureLeadApprovalWorkflow();
    }

    public function test_newly_created_lead_defaults_to_pending_approval_state(): void
    {
        $payload = [
            'first_name' => 'Enterprise',
            'last_name' => 'Prospect',
            'company' => 'Acme Corp Global',
            'email' => 'prospect_' . Str::random(5) . '@acme.com',
            'status' => 'New',
        ];

        $response = $this->actingAs($this->salesRepUser, 'tenant')
            ->postJson(route('api.leads.store'), $payload);

        $response->assertStatus(201);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $lead = $data['lead'];
        $custom = is_array($lead['custom_data']) ? $lead['custom_data'] : (json_decode($lead['custom_data'] ?? '[]', true) ?: []);

        $this->assertEquals('Pending', $custom['approval_status']);
        $this->assertEquals(0, (int)($custom['is_approved'] ?? 0));
        $this->assertEquals(0, (int)($custom['is_rejected'] ?? 0));
    }

    public function test_tasks_are_locked_when_lead_is_pending_approval(): void
    {
        $lead = Lead::on('tenant')->create([
            'first_name' => 'Locked',
            'last_name' => 'Lead',
            'company' => 'Testing Lock',
            'email' => 'lock_' . Str::random(5) . '@lock.com',
            'status' => 'New',
            'assigned_to' => $this->salesRepUser->id,
            'created_by' => $this->salesRepUser->id,
            'custom_data' => [
                'approval_status' => 'Pending',
                'is_approved' => 0,
                'is_rejected' => 0,
            ],
        ]);

        $taskPayload = [
            'subject' => 'Follow up on proposal terms',
            'priority' => 'High',
            'status' => 'Open',
        ];

        $response = $this->actingAs($this->salesRepUser, 'tenant')
            ->postJson(route('api.leads.tasks.store', ['id' => $lead->id]), $taskPayload);

        $response->assertStatus(422);
        $data = $response->json();

        $this->assertFalse($data['success']);
        $this->assertStringContainsString('pending approval', strtolower($data['message']));
    }

    public function test_unauthorized_user_cannot_approve_or_reject_lead(): void
    {
        // Another independent rep without manager role
        $unauthRep = TenantUser::on('tenant')->create([
            'name' => 'Unauthorized Rep ' . Str::random(4),
            'email' => 'unauth_' . Str::random(5) . '@example.com',
            'password' => Hash::make('password'),
            'profile_id' => $this->salesRepUser->profile_id,
            'role_id' => $this->salesRepUser->role_id,
            'status' => 'Active',
        ]);

        $lead = Lead::on('tenant')->create([
            'first_name' => 'Awaiting',
            'last_name' => 'Decision',
            'company' => 'Target Inc',
            'email' => 'await_' . Str::random(5) . '@target.com',
            'status' => 'New',
            'assigned_to' => $this->salesRepUser->id,
            'created_by' => $this->salesRepUser->id,
            'custom_data' => [
                'approval_status' => 'Pending',
                'is_approved' => 0,
                'is_rejected' => 0,
            ],
        ]);

        $response = $this->actingAs($unauthRep, 'tenant')
            ->postJson(route('api.leads.decide_approval', ['id' => $lead->id]), [
                'decision' => 'approve',
                'comments' => 'Self approving lead',
            ]);

        $response->assertStatus(403);
        $this->assertFalse($response->json('success'));
    }

    public function test_manager_or_admin_can_approve_lead_and_unlock_tasks(): void
    {
        $lead = Lead::on('tenant')->create([
            'first_name' => 'Pending',
            'last_name' => 'ToApprove',
            'company' => 'BlueShift Labs',
            'email' => 'blueshift_' . Str::random(5) . '@blueshift.com',
            'status' => 'New',
            'assigned_to' => $this->salesRepUser->id,
            'created_by' => $this->salesRepUser->id,
            'custom_data' => [
                'approval_status' => 'Pending',
                'is_approved' => 0,
                'is_rejected' => 0,
            ],
        ]);

        // 1. Manager approves lead
        $approveResponse = $this->actingAs($this->managerUser, 'tenant')
            ->postJson(route('api.leads.decide_approval', ['id' => $lead->id]), [
                'decision' => 'approve',
                'comments' => 'Approved by direct sales manager after initial diligence',
            ]);

        $approveResponse->assertStatus(200);
        $approveData = $approveResponse->json();

        $this->assertTrue($approveData['success']);
        $this->assertEquals('approved', $approveData['decision']);
        $this->assertTrue($approveData['is_approved']);

        // Check fresh database state
        $lead->refresh();
        $custom = is_array($lead->custom_data) ? $lead->custom_data : json_decode($lead->custom_data, true);

        $this->assertEquals('Approved', $custom['approval_status']);
        $this->assertEquals(1, (int)$custom['is_approved']);
        $this->assertEquals(0, (int)$custom['is_rejected']);
        $this->assertEquals($this->managerUser->id, (int)$custom['approved_by']);

        // 2. Rep can now successfully create a task on this approved lead
        $taskPayload = [
            'subject' => 'Schedule discovery call with engineering VP',
            'priority' => 'Normal',
            'status' => 'Open',
        ];

        $taskResponse = $this->actingAs($this->salesRepUser, 'tenant')
            ->postJson(route('api.leads.tasks.store', ['id' => $lead->id]), $taskPayload);

        $taskResponse->assertStatus(200);
        $this->assertTrue($taskResponse->json('success'));
    }

    public function test_manager_or_admin_can_reject_lead(): void
    {
        $lead = Lead::on('tenant')->create([
            'first_name' => 'Rejected',
            'last_name' => 'Target',
            'company' => 'Unqualified Corp',
            'email' => 'unqual_' . Str::random(5) . '@unqual.com',
            'status' => 'New',
            'assigned_to' => $this->salesRepUser->id,
            'created_by' => $this->salesRepUser->id,
            'custom_data' => [
                'approval_status' => 'Pending',
                'is_approved' => 0,
                'is_rejected' => 0,
            ],
        ]);

        // Admin rejects lead
        $rejectResponse = $this->actingAs($this->adminUser, 'tenant')
            ->postJson(route('api.leads.decide_approval', ['id' => $lead->id]), [
                'decision' => 'reject',
                'comments' => 'Budget does not match enterprise criteria',
            ]);

        $rejectResponse->assertStatus(200);
        $rejectData = $rejectResponse->json();

        $this->assertTrue($rejectData['success']);
        $this->assertEquals('rejected', $rejectData['decision']);
        $this->assertFalse($rejectData['is_approved']);
        $this->assertTrue($rejectData['is_rejected']);

        $lead->refresh();
        $custom = is_array($lead->custom_data) ? $lead->custom_data : json_decode($lead->custom_data, true);

        $this->assertEquals('Rejected', $custom['approval_status']);
        $this->assertEquals(0, (int)$custom['is_approved']);
        $this->assertEquals(1, (int)$custom['is_rejected']);
        $this->assertEquals($this->adminUser->id, (int)$custom['rejected_by']);

        // Tasks remain locked
        $taskResponse = $this->actingAs($this->salesRepUser, 'tenant')
            ->postJson(route('api.leads.tasks.store', ['id' => $lead->id]), [
                'subject' => 'Attempting task on rejected lead',
            ]);

        $taskResponse->assertStatus(422);
    }
}
