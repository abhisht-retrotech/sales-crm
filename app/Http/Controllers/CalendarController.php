<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display the enterprise multi-object calendar.
     */
    public function index(Request $request)
    {
        // 1. Calendar Sources / Layers Configuration
        $sources = [
            [
                'id' => 'cal-src-meetings',
                'object_label' => 'Meetings & Demos',
                'color_code' => '#8b5cf6',
                'icon_name' => 'video',
            ],
            [
                'id' => 'cal-src-tasks',
                'object_label' => 'CRM Tasks & Deadlines',
                'color_code' => '#3b82f6',
                'icon_name' => 'check-square',
            ],
            [
                'id' => 'cal-src-deals',
                'object_label' => 'Opportunity Target Closes',
                'color_code' => '#10b981',
                'icon_name' => 'briefcase',
            ],
            [
                'id' => 'cal-src-emails',
                'object_label' => 'Scheduled Outbox Emails',
                'color_code' => '#f59e0b',
                'icon_name' => 'mail',
            ],
            [
                'id' => 'cal-src-shipping',
                'object_label' => 'Freight Deliveries & Shipments',
                'color_code' => '#06b6d4',
                'icon_name' => 'truck',
            ],
            [
                'id' => 'cal-src-payments',
                'object_label' => 'Invoices & Settlement Due',
                'color_code' => '#ec4899',
                'icon_name' => 'credit-card',
            ],
        ];

        // 2. Realistic Mock Calendar Events (Synchronized with October 2026)
        $events = [
            [
                'id' => 'evt-01',
                'title' => 'Technical Architecture Committee Demo',
                'subtitle' => 'https://meet.google.com/dsk-qmwe-opt',
                'date' => '2026-10-15',
                'start_time' => '2026-10-15T14:00:00',
                'end_time' => '2026-10-15T15:00:00',
                'is_all_day' => false,
                'source_id' => 'cal-src-meetings',
                'source_label' => 'Meetings & Demos',
                'color_code' => '#8b5cf6',
                'icon_name' => 'video',
                'related_entity_type' => 'leads',
                'related_entity_id' => 'lead-01',
                'related_entity_name' => 'Sarah Jenkins (Acme Corporation)',
                'attendee_emails' => ['s.jenkins@acmecorp.io', 'cto@acmecorp.io', 'alex.morgan@dealsync.app'],
                'description' => 'Review single-tenant database isolation, SOC2 Type II compliance pack, and legacy Salesforce migration pathways.'
            ],
            [
                'id' => 'evt-02',
                'title' => 'Finalize MSA Addendum B with Legal',
                'subtitle' => 'Due by 5:00 PM EST',
                'date' => '2026-10-15',
                'start_time' => '2026-10-15T11:00:00',
                'end_time' => '2026-10-15T11:45:00',
                'is_all_day' => false,
                'source_id' => 'cal-src-tasks',
                'source_label' => 'CRM Tasks & Deadlines',
                'color_code' => '#3b82f6',
                'icon_name' => 'check-square',
                'related_entity_type' => 'contacts',
                'related_entity_id' => 'con-02',
                'related_entity_name' => 'Marcus Bell (TechFlow Inc)',
                'attendee_emails' => ['alex.morgan@dealsync.app'],
                'description' => 'Route DocuSign envelope to CFO Evelyn Reed.'
            ],
            [
                'id' => 'evt-03',
                'title' => 'Scheduled: Q4 Renewal Notice Dispatch',
                'subtitle' => 'rachel@vertex-logistics.com',
                'date' => '2026-10-16',
                'start_time' => '2026-10-16T09:00:00',
                'end_time' => '2026-10-16T09:15:00',
                'is_all_day' => false,
                'source_id' => 'cal-src-emails',
                'source_label' => 'Scheduled Outbox Emails',
                'color_code' => '#f59e0b',
                'icon_name' => 'mail',
                'related_entity_type' => 'contacts',
                'related_entity_id' => 'con-05',
                'related_entity_name' => 'Rachel Adams (Vertex Logistics)',
                'attendee_emails' => ['rachel@vertex-logistics.com'],
                'description' => 'Automated Outbox delivery of multi-year renewal rebate agreement.'
            ],
            [
                'id' => 'evt-04',
                'title' => 'Skyline Cloud Systems Target Close ($125,000)',
                'subtitle' => 'Stage: Negotiation / Review',
                'date' => '2026-10-20',
                'start_time' => '2026-10-20T17:00:00',
                'end_time' => '2026-10-20T17:30:00',
                'is_all_day' => true,
                'source_id' => 'cal-src-deals',
                'source_label' => 'Opportunity Target Closes',
                'color_code' => '#10b981',
                'icon_name' => 'briefcase',
                'related_entity_type' => 'opportunities',
                'related_entity_id' => 'opp-03',
                'related_entity_name' => 'Skyline Cloud Systems (Q4 Expansion)',
                'attendee_emails' => ['vp.sales@dealsync.app'],
                'description' => 'Expected contract execution date for enterprise multi-tenant rollout.'
            ],
            [
                'id' => 'evt-05',
                'title' => 'Air Freight ETA: Consignment AF-9021',
                'subtitle' => 'Route: ORD -> FRA (Lufthansa Cargo)',
                'date' => '2026-10-18',
                'start_time' => '2026-10-18T16:30:00',
                'end_time' => '2026-10-18T17:00:00',
                'is_all_day' => false,
                'source_id' => 'cal-src-shipping',
                'source_label' => 'Freight Deliveries & Shipments',
                'color_code' => '#06b6d4',
                'icon_name' => 'truck',
                'related_entity_type' => 'shipments',
                'related_entity_id' => 'shp-01',
                'related_entity_name' => 'Air Cargo Delivery #9021',
                'attendee_emails' => ['logistics@dealsync.app'],
                'description' => 'Customs clearance inspection at Frankfurt cargo terminal.'
            ],
            [
                'id' => 'evt-06',
                'title' => 'Invoice Settlement Due: INV-2026-088 ($48,500)',
                'subtitle' => 'Net-30 Wire Settlement Term',
                'date' => '2026-10-24',
                'start_time' => '2026-10-24T10:00:00',
                'end_time' => '2026-10-24T10:30:00',
                'is_all_day' => true,
                'source_id' => 'cal-src-payments',
                'source_label' => 'Invoices & Settlement Due',
                'color_code' => '#ec4899',
                'icon_name' => 'credit-card',
                'related_entity_type' => 'payments',
                'related_entity_id' => 'pay-02',
                'related_entity_name' => 'Hyperion Systems EMEA Distribution',
                'attendee_emails' => ['finance@hyperion-global.com', 'billing@dealsync.app'],
                'description' => 'Q4 licensing invoice due via SWIFT / Fedwire transfer.'
            ],
            [
                'id' => 'evt-07',
                'title' => 'Executive QBR with Biogen Dynamics',
                'subtitle' => 'https://zoom.us/j/981029381',
                'date' => '2026-10-08',
                'start_time' => '2026-10-08T15:00:00',
                'end_time' => '2026-10-08T16:00:00',
                'is_all_day' => false,
                'source_id' => 'cal-src-meetings',
                'source_label' => 'Meetings & Demos',
                'color_code' => '#8b5cf6',
                'icon_name' => 'video',
                'related_entity_type' => 'accounts',
                'related_entity_id' => 'acc-04',
                'related_entity_name' => 'Biogen Dynamics',
                'attendee_emails' => ['aris.thorne@biogen-dynamics.org', 'alex.morgan@dealsync.app'],
                'description' => 'Quarterly business review on clinical data pipeline velocity.'
            ],
            [
                'id' => 'evt-08',
                'title' => 'Follow up on SOC2 Type II Questionnaire',
                'subtitle' => 'Security Audit Milestone',
                'date' => '2026-10-22',
                'start_time' => '2026-10-22T13:00:00',
                'end_time' => '2026-10-22T14:00:00',
                'is_all_day' => false,
                'source_id' => 'cal-src-tasks',
                'source_label' => 'CRM Tasks & Deadlines',
                'color_code' => '#3b82f6',
                'icon_name' => 'check-square',
                'related_entity_type' => 'leads',
                'related_entity_id' => 'lead-01',
                'related_entity_name' => 'Acme Corporation Architecture Team',
                'attendee_emails' => ['alex.morgan@dealsync.app'],
                'description' => 'Review security team responses to vendor assessment questionnaire.'
            ]
        ];

        // Layer event counts
        $sourceCounts = [];
        foreach ($events as $evt) {
            $srcId = $evt['source_id'];
            $sourceCounts[$srcId] = ($sourceCounts[$srcId] ?? 0) + 1;
        }

        return view('calendar', compact('sources', 'events', 'sourceCounts'));
    }
}
