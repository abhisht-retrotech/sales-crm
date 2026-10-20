<?php

namespace Tests\Feature;

use Tests\TestCase;

class LeadsTableLayoutTest extends TestCase
{
    public function test_leads_page_matches_exact_target_layout(): void
    {
        $response = $this->get('/leads');

        $response->assertStatus(200);

        // 1. Header
        $response->assertSee('Leads &amp; Intake', false);
        $response->assertSee('Capture, score, and qualify inbound leads with instant decoupled conversion into Accounts and Contacts.', false);
        $response->assertSee('bg-[#7c3aed]', false);
        $response->assertSee('Add Lead', false);

        // 2. Control Toolbar
        $response->assertSee('border-cyan-400', false);
        $response->assertSee('shadow-[0_0_18px_rgba(56,189,248,0.22)]', false);
        $response->assertSee('Search leads by name, company, email...', false);
        $response->assertSee('btn-lead-view-table', false);
        $response->assertSee('btn-lead-view-kanban', false);
        $response->assertSee('Customize', false);
        $response->assertSee('Filter', false);
        $response->assertSee('Bulk Email', false);

        // 3. Table Container & Hydration
        $response->assertSee('id="leads-table-container"', false);
        $response->assertSee('window.__LEADS_DATA__', false);
        $response->assertSee('js/leads.js', false);
    }
}
