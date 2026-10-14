<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_dashboard_returns_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Executive Command Center');
    }

    public function test_leads_returns_successful_response(): void
    {
        $response = $this->get('/leads');
        $response->assertStatus(200);
        $response->assertSee('Inbound Leads Pipeline');
        $response->assertSee('Alexander Wright');
    }

    public function test_accounts_returns_successful_response(): void
    {
        $response = $this->get('/accounts');
        $response->assertStatus(200);
        $response->assertSee('Accounts Directory');
        $response->assertSee('Skyline Cloud Systems');
    }

    public function test_opportunities_returns_successful_response(): void
    {
        $response = $this->get('/opportunities');
        $response->assertStatus(200);
        $response->assertSee('Opportunities & Sales Pipeline');
        $response->assertSee('PO-882194');
    }
}
