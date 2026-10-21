<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\Lead;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LeadActivityStudioTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $tenant = Tenant::on('mysql')->first();
        if ($tenant) {
            config(['database.connections.tenant.database' => $tenant->db_name]);
            DB::purge('tenant');
            app()->instance('currentTenant', $tenant);
        }
    }

    public function test_lead_detail_renders_activity_studio_and_timeline()
    {
        $tenantUser = TenantUser::on('tenant')->first();
        $lead = Lead::on('tenant')->first();
        $this->assertNotNull($lead, 'Lead record required for test');

        $response = $this->actingAs($tenantUser, 'tenant')
            ->get('/leads/' . $lead->id);

        $response->assertStatus(200);
        $response->assertSee('Activity');
        $response->assertSee('Log a Call');
        $response->assertSee('New Task');
        $response->assertSee('New Event');
        $response->assertSee('Call Subject');
        $response->assertSee('Date &amp; Time of Call', false);
        $response->assertSee('Description / Discussion Notes');
        $response->assertSee('Log Call');
        $response->assertSee('Activity History &amp; Timeline', false);
    }

    public function test_api_store_and_get_activities_for_lead()
    {
        $tenantUser = TenantUser::on('tenant')->first();
        $lead = Lead::on('tenant')->first();

        // 1. Post new activity
        $postRes = $this->actingAs($tenantUser, 'tenant')
            ->postJson("/api/leads/{$lead->id}/activities", [
                'type' => 'Call',
                'subject' => 'Automated Test Call',
                'call_datetime' => '2026-10-20 18:30:00',
                'details' => 'Discussed enterprise roadmap and pricing structure.',
            ]);

        $postRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'activity' => [
                    'type' => 'Call',
                    'subject' => 'Automated Test Call',
                    'details' => 'Discussed enterprise roadmap and pricing structure.',
                ]
            ]);

        // 2. Fetch activities via API
        $getRes = $this->actingAs($tenantUser, 'tenant')
            ->getJson("/api/leads/{$lead->id}/activities");

        $getRes->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonFragment(['subject' => 'Automated Test Call']);
    }

    public function test_api_store_task_and_renders_task_view()
    {
        $tenantUser = TenantUser::on('tenant')->first();
        $lead = Lead::on('tenant')->first();
        $this->assertNotNull($lead, 'Lead record required for test');

        // Check HTML renders Task Promo View & New Task Modal
        $response = $this->actingAs($tenantUser, 'tenant')
            ->get('/leads/' . $lead->id);

        $response->assertStatus(200);
        $response->assertSee('Create Task for this Record');
        $response->assertSee('Open the full Task creation popup with priority, status, assignee, reminders, and recurring scheduling.');
        $response->assertSee('Add New Task');
        $response->assertSee('id="leadNewTaskModal"', false);
        $response->assertSee('Create and assign an actionable task, follow-up, or reminder.');
        $response->assertSee('Create Recurring Series');

        // Post new task via API
        $postRes = $this->actingAs($tenantUser, 'tenant')
            ->postJson("/api/leads/{$lead->id}/tasks", [
                'subject' => 'Follow up on proposal pricing',
                'priority' => 'High',
                'status' => 'Open',
                'due_date' => '2026-10-25',
                'comments' => 'Discussion points from executive call.',
                'reminder_set' => '1 hour before',
                'is_recurring' => 1,
            ]);

        $postRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'task' => [
                    'subject' => 'Follow up on proposal pricing',
                    'priority' => 'High',
                    'status' => 'Open',
                ],
                'activity' => [
                    'type' => 'Task',
                    'subject' => 'Follow up on proposal pricing',
                ]
            ]);

        // Assert Task was saved to database
        $this->assertDatabaseHas('tasks', [
            'subject' => 'Follow up on proposal pricing',
            'related_entity_id' => $lead->id,
            'priority' => 'High',
        ], 'tenant');

        // Assert Activity was logged
        $this->assertDatabaseHas('activities', [
            'subject' => 'Follow up on proposal pricing',
            'type' => 'Task',
            'entity_id' => $lead->id,
        ], 'tenant');
    }

    public function test_event_form_renders_and_logs_meeting_activity()
    {
        $tenantUser = TenantUser::on('tenant')->first();
        $lead = Lead::on('tenant')->first();
        $this->assertNotNull($lead, 'Lead record required for test');

        // Check HTML renders Event Form view
        $response = $this->actingAs($tenantUser, 'tenant')
            ->get('/leads/' . $lead->id);

        $response->assertStatus(200);
        $response->assertSee('id="activity-event-view"', false);
        $response->assertSee('Event / Meeting Title');
        $response->assertSee('Start Date &amp; Time', false);
        $response->assertSee('End Date &amp; Time', false);
        $response->assertSee('Log Meeting');

        // Post new event / meeting via API
        $postRes = $this->actingAs($tenantUser, 'tenant')
            ->postJson("/api/leads/{$lead->id}/activities", [
                'type' => 'Meeting',
                'subject' => 'Product Architecture Demo & Onboarding',
                'start_date' => '2026-10-21 00:19:00',
                'end_date' => '2026-10-21 01:19:00',
                'details' => 'Key details, discussion points, or action items...',
            ]);

        $postRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'activity' => [
                    'type' => 'Meeting',
                    'subject' => 'Product Architecture Demo & Onboarding',
                    'details' => 'Key details, discussion points, or action items...',
                ]
            ]);

        // Assert database record
        $this->assertDatabaseHas('activities', [
            'subject' => 'Product Architecture Demo & Onboarding',
            'type' => 'Meeting',
            'entity_id' => $lead->id,
        ], 'tenant');
    }

    public function test_note_form_and_api_store_note()
    {
        $tenantUser = TenantUser::on('tenant')->first();
        $lead = Lead::on('tenant')->first();
        $this->assertNotNull($lead, 'Lead record required for test');

        // Check HTML renders Add Note pill
        $response = $this->actingAs($tenantUser, 'tenant')
            ->get('/leads/' . $lead->id);

        $response->assertStatus(200);
        $response->assertSee('id="act-pill-note"', false);
        $response->assertSee('Add Note');

        // Post new Note via API
        $postRes = $this->actingAs($tenantUser, 'tenant')
            ->postJson("/api/leads/{$lead->id}/activities", [
                'type' => 'Note',
                'subject' => 'Key executive sponsorship discussion',
                'call_datetime' => '20-10-2026 19:00',
                'details' => 'Discussed long-term cloud commitment and custom licensing terms.',
            ]);

        $postRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Note saved successfully.',
                'activity' => [
                    'type' => 'Note',
                    'subject' => 'Key executive sponsorship discussion',
                    'details' => 'Discussed long-term cloud commitment and custom licensing terms.',
                ]
            ]);

        // Assert database record
        $this->assertDatabaseHas('activities', [
            'subject' => 'Key executive sponsorship discussion',
            'type' => 'Note',
            'entity_id' => $lead->id,
        ], 'tenant');
    }

    public function test_lead_file_upload_and_listing()
    {
        Storage::fake('public');
        $tenantUser = TenantUser::on('tenant')->first();
        $lead = Lead::on('tenant')->first();
        $this->assertNotNull($lead, 'Lead record required for test');

        // 1. Initially get files via API
        $initialRes = $this->actingAs($tenantUser, 'tenant')
            ->getJson("/api/leads/{$lead->id}/files");
        $initialRes->assertStatus(200)
            ->assertJsonStructure(['success', 'count', 'files']);
        $initialCount = $initialRes->json('count');

        // 2. Upload excel file
        $file1 = UploadedFile::fake()->create('excel_attachment.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $file2 = UploadedFile::fake()->create('pdf_attachment.pdf', 25, 'application/pdf');

        $uploadRes = $this->actingAs($tenantUser, 'tenant')
            ->post("/api/leads/{$lead->id}/files", [
                'files' => [$file1, $file2]
            ], ['Accept' => 'application/json']);

        $uploadRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => $initialCount + 2,
            ]);

        $uploadedData = $uploadRes->json('files');
        $this->assertCount(2, $uploadedData);
        $this->assertEquals('excel_attachment.xlsx', $uploadedData[0]['name']);
        $this->assertEquals('excel', $uploadedData[0]['icon_type']);
        $this->assertEquals('pdf_attachment.pdf', $uploadedData[1]['name']);
        $this->assertEquals('pdf', $uploadedData[1]['icon_type']);

        $file1Id = $uploadedData[0]['id'];

        // 3. Check database persistence
        $this->assertDatabaseHas('files', [
            'name' => 'excel_attachment.xlsx',
        ], 'tenant');

        $this->assertDatabaseHas('file_links', [
            'file_id' => $file1Id,
            'entity_type' => 'leads',
            'entity_id' => (string)$lead->id,
        ], 'tenant');

        // 4. View HTML detail page includes Files & Attachments card and uploaded filenames
        $detailRes = $this->actingAs($tenantUser, 'tenant')
            ->get("/leads/{$lead->id}");
        $detailRes->assertStatus(200);
        $detailRes->assertSee('Files &amp; Attachments', false);
        $detailRes->assertSee('excel_attachment.xlsx');
        $detailRes->assertSee('pdf_attachment.pdf');
        $detailRes->assertSee('+ Add File');

        // 5. Delete file
        $deleteRes = $this->actingAs($tenantUser, 'tenant')
            ->deleteJson("/api/leads/{$lead->id}/files/{$file1Id}");
        $deleteRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => $initialCount + 1,
            ]);

        $this->assertDatabaseMissing('files', [
            'id' => $file1Id,
        ], 'tenant');
    }
}
