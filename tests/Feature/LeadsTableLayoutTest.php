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

        // 4. Customize Table Fields & Order Modal
        $response->assertSee('id="viewConfigModal"', false);
        $response->assertSee('Customize Table Fields &amp; Order', false);
        $response->assertSee('Drag rows or click ↑ / ↓ to rearrange column sequence from left to right.', false);
        $response->assertSee('customize-fields-search-input', false);
        $response->assertSee('customize-select-all-btn', false);
        $response->assertSee('customize-deselect-all-btn', false);
        $response->assertSee('customize-reset-defaults-btn', false);
        $response->assertSee('view-config-fields-list', false);
        $response->assertSee('Showing', false);
        $response->assertSee('Apply &amp; Save View', false);
    }

    public function test_api_save_view_fields_persists_to_database(): void
    {
        $testFields = ['company', 'score', 'email', 'assigned_to', 'name'];
        $response = $this->postJson('/api/leads/view-fields', [
            'fields' => $testFields,
            'view_mode' => 'table',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'fields' => $testFields,
        ]);

        // Verify index view provides the saved visible fields in hydration data
        $indexResponse = $this->get('/leads');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('"tableVisibleFields":["company","score","email","assigned_to","name"]', false);
    }
}
