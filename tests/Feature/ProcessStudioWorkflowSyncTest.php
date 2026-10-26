<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\User;
use App\Models\ProcessFlow;
use App\Models\ApprovalProcess;
use App\Models\ApprovalStep;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcessStudioWorkflowSyncTest extends TestCase
{
    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::on('mysql')->first() ?? Tenant::on('mysql')->create([
            'company_name' => 'RetroTech CRM Unit',
            'subdomain'    => 'retrotech',
            'db_name'      => 'crm_retrotech_nna59',
            'is_active'    => true,
        ]);

        config(['database.connections.tenant.database' => $this->tenant->db_name]);
        DB::purge('tenant');

        app()->instance('currentTenant', $this->tenant);

        $this->user = User::on('tenant')->first() ?? User::on('tenant')->create([
            'name'     => 'Workflow Admin',
            'email'    => 'admin@retrotech.io',
            'password' => bcrypt('secret123'),
        ]);

        $this->actingAs($this->user);
    }

    public function test_process_studio_canvas_route_loads_successfully()
    {
        $response = $this->get('/process-studio');
        $response->assertStatus(200);

        $responsePlural = $this->get('/process-studios');
        $responsePlural->assertStatus(200);
    }

    public function test_saving_lead_approval_flow_syncs_with_approval_process_and_steps()
    {
        $payload = [
            'preset_key'   => 'lead-qualification-approval',
            'name'         => 'Lead Intake Governance & Approval',
            'title'        => 'Lead Intake Governance & Approval',
            'subtitle'     => 'All newly created leads initialize in Pending status with task operations locked',
            'entity'       => 'Leads',
            'categoryType' => 'Approval Process',
            'status'       => 'active',
            'nodes'        => [
                [
                    'id'   => 'node-lqa-1',
                    'type' => 'processTrigger',
                    'data' => [
                        'category' => '1. TRIGGER CRITERIA',
                        'subtype'  => 'record_event',
                        'title'    => 'New Lead Intake Created',
                    ]
                ],
                [
                    'id'   => 'node-lqa-lock',
                    'type' => 'processAction',
                    'data' => [
                        'category' => 'SECURITY GOVERNANCE',
                        'subtype'  => 'lock_record',
                        'title'    => 'Enforce Task Execution Lock',
                    ]
                ],
                [
                    'id'   => 'node-lqa-2',
                    'type' => 'processApprovalStep',
                    'data' => [
                        'stepBadge' => 'STEP 1: MANAGER / ADMIN',
                        'approver'  => 'Direct Manager or Administrator',
                        'sla'       => '12h SLA',
                        'title'     => 'Lead Qualification Sign-Off',
                    ]
                ],
                [
                    'id'   => 'node-lqa-approved',
                    'type' => 'processAction',
                    'data' => [
                        'category' => 'FINAL APPROVAL ACTION',
                        'subtype'  => 'update_field',
                        'title'    => 'Authorize Lead & Unlock Tasks',
                    ]
                ]
            ],
            'edges'        => [
                ['id' => 'e1', 'source' => 'node-lqa-1', 'target' => 'node-lqa-2']
            ],
        ];

        $response = $this->postJson('/api/flows', $payload);
        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify ProcessFlow was stored in tenant database
        $this->assertDatabaseHas('process_flows', [
            'entity_type' => 'Leads',
            'status'      => 'active',
        ], 'tenant');

        // Verify ApprovalProcess was dynamically synced
        $this->assertDatabaseHas('approval_processes', [
            'module'                     => 'leads',
            'is_active'                  => 1,
            'lock_record_until_approved' => 1,
        ], 'tenant');

        $approvalProcess = ApprovalProcess::on('tenant')->where('module', 'leads')->first();
        $this->assertNotNull($approvalProcess);

        // Verify multi-tier ApprovalStep was created
        $this->assertDatabaseHas('approval_steps', [
            'process_id'    => $approvalProcess->id,
            'step_number'   => 1,
            'approver_name' => 'Direct Manager or Administrator',
        ], 'tenant');
    }

    public function test_disabling_flow_in_process_studio_deactivates_approval_process()
    {
        $payload = [
            'preset_key'   => 'lead-qualification-approval',
            'name'         => 'Lead Intake Governance & Approval',
            'entity'       => 'Leads',
            'categoryType' => 'Approval Process',
            'status'       => 'disabled',
            'nodes'        => [
                [
                    'id'   => 'node-1',
                    'type' => 'processApprovalStep',
                    'data' => ['title' => 'Sign Off', 'approver' => 'Admin Only']
                ]
            ],
            'edges'        => [],
        ];

        $response = $this->postJson('/api/flows', $payload);
        $response->assertStatus(200);

        // Verify ApprovalProcess is deactivated
        $this->assertDatabaseHas('approval_processes', [
            'module'    => 'leads',
            'is_active' => 0,
        ], 'tenant');
    }

    public function test_get_flow_by_preset_key_returns_saved_data()
    {
        $response = $this->getJson('/api/flows/lead-qualification-approval');
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    protected function tearDown(): void
    {
        app(\App\Services\Leads\LeadSecurityService::class)->ensureLeadApprovalWorkflow();
        parent::tearDown();
    }
}
