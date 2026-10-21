<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant;
use App\Models\Lead;
use App\Models\TenantUser;

class LeadShowDynamicViewTest extends TestCase
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

    public function test_lead_show_renders_target_dynamic_view_structure(): void
    {
        $user = TenantUser::on('tenant')->first();
        $this->actingAs($user, 'web');

        $lead = Lead::on('tenant')->where('company', 'EVENTS-TECHB2B')->first();
        if (!$lead) {
            $lead = Lead::on('tenant')->create([
                'first_name' => 'SaaS',
                'last_name' => 'World 2026',
                'company' => 'EVENTS-TECHB2B',
                'email' => 'invites@events-techb2b.org',
                'score' => 65,
                'status' => 'New',
                'source' => 'Inbound Email',
                'rating' => '0',
                'assigned_to' => $user->id,
            ]);
        }

        $response = $this->get("/leads/{$lead->id}");

        $response->assertStatus(200);

        // 1. Breadcrumbs
        $response->assertSee('DealSync');
        $response->assertSee('Leads');
        $response->assertSee('EVENTS-TECHB2B | SaaS');

        // 2. Left Cards: Lead Detail & System Information
        $response->assertSee('Lead Detail');
        $response->assertSee('System Information');
        $response->assertSee('First Name');
        $response->assertSee('Last Name');
        $response->assertSee('Company Name');
        $response->assertSee('Email Address');
        $response->assertSee('invites@events-techb2b.org');
        $response->assertSee('Lead Score');
        $response->assertSee('65');
        $response->assertSee('Rating');
        $response->assertSee('Current Owner');

        // 3. Right Sidebar Sub-Tabs & Stacked Widgets
        $response->assertSee('Related');
        $response->assertSee('Activity');
        $response->assertSee('Files');
        $response->assertSee('Approval History');
        $response->assertSee('Lead History');
        $response->assertSee('Emails');

        // 4. Fullscreen Modal Controls & Header
        $response->assertSee('Back to Leads');
        $response->assertSee('LEADS');
        $response->assertSee('Convert');
        $response->assertSee('Change Owner');
        $response->assertSee('Share');
        $response->assertSee('lead-show-page-root');
        $response->assertSee('fixed inset-0');

        // 5. Test modal partial query response for embedding in Leads page
        $modalResponse = $this->get("/leads/{$lead->id}?modal=1");
        $modalResponse->assertStatus(200);
        $modalResponse->assertSee('Lead Detail');
        $modalResponse->assertSee('Back to Leads');
        $modalResponse->assertSee('EVENTS-TECHB2B | SaaS');
    }
}
