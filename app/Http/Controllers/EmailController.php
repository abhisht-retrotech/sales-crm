<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EmailController extends Controller
{
    /**
     * Display the email hub workstation.
     */
    public function index(Request $request)
    {
        $currentUserName = 'Alex Morgan';

        // Static Emails matching 1:1 with React CRM & Database Seeds
        $emails = [
            [
                'id' => 'em-001',
                'folder' => 'inbox',
                'direction' => 'inbound',
                'sender_name' => 'Sarah Jenkins',
                'sender_email' => 's.jenkins@acmecorp.io',
                'sender_city' => 'Austin',
                'sender_country' => 'United States',
                'sender_ip' => '198.51.100.24',
                'sender_client' => 'Apple Mail 16.4 (macOS)',
                'recipient_name' => 'Alex Morgan',
                'recipient_email' => 'sales@dealsync.app',
                'subject' => 'Request for Proposal: Enterprise Cloud Migration (Acme Corp)',
                'snippet' => 'Hi team, we reviewed your solution deck and would like to schedule a formal demo for our architecture committee next Thursday...',
                'body_html' => '<div style="font-family: inherit; line-height: 1.6; color: #1e293b;">
                    <p>Hi Alex,</p>
                    <p>We thoroughly reviewed your solution deck from last week\'s initial discovery call. Our leadership and technical architecture committee are very interested in DealSync\'s tenant isolation capabilities and decoupled multi-tenant architecture.</p>
                    <p>We would like to coordinate a formal 60-minute technical deep dive next <strong>Thursday at 2:00 PM EST</strong>. Our Chief Architect and Security Director will be attending to review:</p>
                    <ul style="margin: 0.75rem 0; padding-left: 1.5rem; line-height: 1.7;">
                        <li>Single-tenant database isolation & encryption key ownership</li>
                        <li>SOC2 Type II and GDPR data residency compliance guarantees</li>
                        <li>Automated migration pathways from legacy Salesforce instances</li>
                        <li>Custom object schema extensibility and webhook latency SLAs</li>
                    </ul>
                    <p>Attached please find our formal <strong>RFP Requirements Matrix</strong> and initial <strong>Vendor Security Questionnaire</strong>. Could you review and let us know if the proposed time works?</p>
                    <p style="margin-top: 1.5rem;">Best regards,<br/><strong>Sarah Jenkins</strong><br/><span style="color: #64748b; font-size: 0.85rem;">VP of Enterprise Technology & Infrastructure<br/>Acme Corporation</span></p>
                </div>',
                'body_text' => "Hi Alex,\n\nWe thoroughly reviewed your solution deck from last week's initial discovery call. Our leadership and technical architecture committee are very interested in DealSync's tenant isolation capabilities.\n\nWe would like to coordinate a formal 60-minute technical deep dive next Thursday at 2:00 PM EST. Attached please find our formal RFP Requirements Matrix.",
                'received_at' => 'Today at 10:42 AM',
                'received_raw' => now()->subHours(2)->toIso8601String(),
                'is_read' => false,
                'is_starred' => true,
                'is_deleted' => false,
                'entity_tag' => 'Lead',
                'lead_id' => 'lead-01',
                'lead_name' => 'Sarah Jenkins',
                'lead_company' => 'Acme Corporation',
                'contact_id' => null,
                'contact_name' => null,
                'account_name' => 'Acme Corporation',
                'open_count' => 0,
                'click_count' => 0,
                'attachments' => [
                    [
                        'id' => 'att-01',
                        'file_name' => 'RFP_Requirements_Matrix_2026.xlsx',
                        'file_size' => 2450000,
                        'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'file_url' => '#'
                    ],
                    [
                        'id' => 'att-02',
                        'file_name' => 'Vendor_Security_Questionnaire.pdf',
                        'file_size' => 840000,
                        'mime_type' => 'application/pdf',
                        'file_url' => '#'
                    ]
                ],
                'ai_summary' => [
                    'summary' => 'Sarah Jenkins (Acme Corp) is requesting a comprehensive technical architecture demo with emphasis on multi-tenant data sovereignty, enterprise SOC2 compliance, and single-tenant database isolation. Target rollout is Q3 2026.',
                    'keyPoints' => [
                        'Architecture committee demo requested for next Thursday at 2:00 PM EST',
                        'Security questionnaire and migration RFP matrix attached for review',
                        'Key decision makers: Chief Architect & Security Director attending',
                        'Estimated annual license scope: $180,000 – $250,000 ARR'
                    ],
                    'recommendedAction' => 'Confirm Thursday 2:00 PM EST slot with Solutions Engineering, attach SOC2 Type II compliance pack, and prep migration pricing proposal.'
                ],
                'thread' => [
                    [
                        'id' => 'em-001-t1',
                        'sender_name' => 'Sarah Jenkins',
                        'sender_email' => 's.jenkins@acmecorp.io',
                        'received_at' => 'Today at 10:42 AM',
                        'snippet' => 'Hi team, we reviewed your solution deck and would like to schedule a formal demo...',
                        'body_text' => 'Hi Alex, We thoroughly reviewed your solution deck. We would like to coordinate a formal 60-minute technical deep dive next Thursday at 2:00 PM EST.'
                    ],
                    [
                        'id' => 'em-001-t0',
                        'sender_name' => 'Alex Morgan',
                        'sender_email' => 'sales@dealsync.app',
                        'received_at' => 'Yesterday at 4:15 PM',
                        'snippet' => 'Thank you for connecting Sarah! Attached is our comprehensive enterprise architecture deck...',
                        'body_text' => 'Hi Sarah, It was a pleasure speaking with you today. Attached is our enterprise architecture overview and SOC2 Type II summary.'
                    ]
                ]
            ],
            [
                'id' => 'em-002',
                'folder' => 'inbox',
                'direction' => 'inbound',
                'sender_name' => 'Marcus Bell',
                'sender_email' => 'marcus.bell@techflow.com',
                'sender_city' => 'San Francisco',
                'sender_country' => 'United States',
                'sender_ip' => '64.233.160.1',
                'sender_client' => 'Outlook 365 (Web)',
                'recipient_name' => 'Alex Morgan',
                'recipient_email' => 'alex.morgan@dealsync.app',
                'subject' => 'Re: Contract Addendum & Revised Delivery Schedule',
                'snippet' => 'Alex, our legal team reviewed the revised addendum and everything looks solid. Ready to proceed with signing through DocuSign...',
                'body_html' => '<div style="font-family: inherit; line-height: 1.6; color: #1e293b;">
                    <p>Hi Alex,</p>
                    <p>Good news — our corporate legal counsel has completed their redline review of the <strong>Master Services Agreement Addendum B</strong>. All indemnification clauses and uptime service level credits are now fully approved as drafted.</p>
                    <p>Please send the final execution copy via DocuSign to our signatory, <strong>Evelyn Reed (Chief Financial Officer)</strong> with carbon copy to me. Once executed, our procurement team will release the initial wire transfer for Q4 onboarding.</p>
                    <p>Thanks again for turning around the revised terms so promptly!</p>
                    <p style="margin-top: 1.5rem;">Best regards,<br/><strong>Marcus Bell</strong><br/><span style="color: #64748b; font-size: 0.85rem;">Director of Strategic Procurement<br/>TechFlow Inc.</span></p>
                </div>',
                'body_text' => "Hi Alex,\n\nGood news — our corporate legal counsel has completed their redline review of the Master Services Agreement Addendum B. All terms approved.\n\nPlease route the final copy through DocuSign to Evelyn Reed.",
                'received_at' => 'Yesterday at 3:18 PM',
                'received_raw' => now()->subDay()->toIso8601String(),
                'is_read' => true,
                'is_starred' => true,
                'is_deleted' => false,
                'entity_tag' => 'Contact',
                'lead_id' => null,
                'lead_name' => null,
                'lead_company' => null,
                'contact_id' => 'con-02',
                'contact_name' => 'Marcus Bell',
                'account_name' => 'TechFlow Inc',
                'open_count' => 4,
                'click_count' => 2,
                'last_opened_city' => 'San Francisco',
                'last_opened_country' => 'United States',
                'last_opened_ip' => '64.233.160.1',
                'attachments' => [
                    [
                        'id' => 'att-03',
                        'file_name' => 'MSA_Addendum_B_Signed_Approval.pdf',
                        'file_size' => 1280000,
                        'mime_type' => 'application/pdf',
                        'file_url' => '#'
                    ]
                ],
                'ai_summary' => [
                    'summary' => 'TechFlow legal team approved Master Services Agreement Addendum B with zero pending redlines. Execution copy requested for CFO Evelyn Reed.',
                    'keyPoints' => [
                        'Legal counsel review 100% complete with no additional changes',
                        'DocuSign envelope required for CFO Evelyn Reed',
                        'Initial Q4 onboarding payment wire will release upon signing'
                    ],
                    'recommendedAction' => 'Generate and dispatch DocuSign envelope to Evelyn Reed (evelyn.reed@techflow.com) and notify accounting.'
                ],
                'thread' => []
            ],
            [
                'id' => 'em-003',
                'folder' => 'inbox',
                'direction' => 'inbound',
                'sender_name' => 'Elena Rostova',
                'sender_email' => 'elena.rostova@hyperion-global.com',
                'sender_city' => 'London',
                'sender_country' => 'United Kingdom',
                'sender_ip' => '194.28.112.44',
                'sender_client' => 'Google Workspace (Chrome)',
                'recipient_name' => 'DealSync Inquiries',
                'recipient_email' => 'contact@dealsync.app',
                'subject' => 'Inquiry: Global Distribution Partnership & White-Label Licensing',
                'snippet' => 'Good afternoon, Hyperion Global is evaluating regional CRM distribution partners for our Q1 EMEA rollout across 12 countries...',
                'body_html' => '<div style="font-family: inherit; line-height: 1.6; color: #1e293b;">
                    <p>Good afternoon,</p>
                    <p>I am reaching out on behalf of <strong>Hyperion Global Systems</strong>. We are currently scouting enterprise-ready SaaS CRM platforms to power our white-label distribution network across 12 EMEA territories starting in Q1 2027.</p>
                    <p>We require a multi-tenant platform capable of providing:</p>
                    <ul style="margin: 0.75rem 0; padding-left: 1.5rem; line-height: 1.7;">
                        <li>Complete white-label re-theming with custom tenant subdomains</li>
                        <li>Independent tenant database separation for compliance</li>
                        <li>Multi-currency settlement (EUR, GBP, CHF, USD) with dynamic FX tables</li>
                    </ul>
                    <p>Could you connect me with your Global Partnerships lead to discuss co-selling economics and partner tier margins?</p>
                    <p style="margin-top: 1.5rem;">Kind regards,<br/><strong>Elena Rostova</strong><br/><span style="color: #64748b; font-size: 0.85rem;">Head of Corporate Strategy & Partnerships<br/>Hyperion Global Systems Ltd (London, UK)</span></p>
                </div>',
                'body_text' => "Good afternoon,\n\nI am reaching out on behalf of Hyperion Global Systems. We are scouting enterprise-ready SaaS CRM platforms for our white-label distribution network across 12 EMEA territories.",
                'received_at' => 'Oct 13 at 11:05 AM',
                'received_raw' => now()->subDays(2)->toIso8601String(),
                'is_read' => false,
                'is_starred' => false,
                'is_deleted' => false,
                'entity_tag' => 'None', // Untagged -> shows + Convert to Lead / Contact
                'lead_id' => null,
                'lead_name' => null,
                'lead_company' => null,
                'contact_id' => null,
                'contact_name' => null,
                'account_name' => null,
                'open_count' => 0,
                'click_count' => 0,
                'attachments' => [],
                'ai_summary' => [
                    'summary' => 'High-value white-label partnership inquiry from Hyperion Global (London) covering 12 EMEA territories. Requires white-labeling, isolated databases, and multi-currency capabilities.',
                    'keyPoints' => [
                        'Sender is not currently in DealSync CRM',
                        '12 EMEA territory rollout proposed for Q1',
                        'Inquiry involves white-label licensing and partner revenue-share'
                    ],
                    'recommendedAction' => 'Convert sender to Lead under "Enterprise Partner" pipeline and schedule intro call with Partner Sales Director.'
                ],
                'thread' => []
            ],
            [
                'id' => 'em-004',
                'folder' => 'sent',
                'direction' => 'outbound',
                'sender_name' => 'Alex Morgan',
                'sender_email' => 'alex.morgan@dealsync.app',
                'recipient_name' => 'David Chen',
                'recipient_email' => 'd.chen@apextechnologies.com',
                'subject' => 'Sent: Enterprise SLA Tier & Onboarding Agreement',
                'snippet' => 'David, thank you for your commitment to DealSync. Attached is the customized Enterprise 99.99% SLA agreement with 24/7 dedicated engineering response...',
                'body_html' => '<div style="font-family: inherit; line-height: 1.6; color: #1e293b;">
                    <p>Hi David,</p>
                    <p>Thank you for choosing DealSync as your operational platform. Attached you will find our finalized <strong>Enterprise 99.99% SLA Tier & Support Agreement</strong>.</p>
                    <p>This includes:</p>
                    <ul style="margin: 0.75rem 0; padding-left: 1.5rem; line-height: 1.7;">
                        <li>15-minute response SLA for Severity 1 production tickets</li>
                        <li>Assigned Enterprise Customer Success Manager</li>
                        <li>Automated daily encrypted snapshot backups</li>
                    </ul>
                    <p>Please review and sign at your earliest convenience so our deployment team can provision your dedicated production instance.</p>
                    <p style="margin-top: 1.5rem;">Best regards,<br/><strong>Alex Morgan</strong><br/>Enterprise Accounts Team</p>
                </div>',
                'body_text' => "Hi David, Thank you for choosing DealSync. Attached is our finalized Enterprise 99.99% SLA Tier Agreement.",
                'received_at' => 'Oct 12 at 2:30 PM',
                'received_raw' => now()->subDays(3)->toIso8601String(),
                'is_read' => true,
                'is_starred' => false,
                'is_deleted' => false,
                'entity_tag' => 'Lead',
                'lead_id' => 'lead-03',
                'lead_name' => 'David Chen',
                'lead_company' => 'Apex Technologies',
                'contact_id' => null,
                'contact_name' => null,
                'account_name' => 'Apex Technologies',
                'open_count' => 2,
                'click_count' => 1,
                'last_opened_city' => 'New York',
                'last_opened_country' => 'United States',
                'last_opened_ip' => '172.56.21.89',
                'attachments' => [
                    [
                        'id' => 'att-04',
                        'file_name' => 'Enterprise_SLA_Agreement_v4.pdf',
                        'file_size' => 1840000,
                        'mime_type' => 'application/pdf',
                        'file_url' => '#'
                    ]
                ],
                'ai_summary' => null,
                'thread' => []
            ],
            [
                'id' => 'em-005',
                'folder' => 'drafts',
                'direction' => 'outbound',
                'sender_name' => 'Alex Morgan',
                'sender_email' => 'alex.morgan@dealsync.app',
                'recipient_name' => 'Dr. Aris Thorne',
                'recipient_email' => 'aris.thorne@biogen-dynamics.org',
                'subject' => 'Draft: Follow-up on Technical Discovery Session',
                'snippet' => 'Dr. Thorne, thank you for attending our technical discovery session earlier today. As promised, I am compiling the documentation on HIPAA compliance and database encryption...',
                'body_html' => '<div style="font-family: inherit; line-height: 1.6; color: #1e293b;">
                    <p>Dr. Thorne,</p>
                    <p>Thank you for taking the time to meet with our solutions engineering team earlier today. We were excited to learn about Biogen Dynamics\' clinical trial workflow and international expansion plans.</p>
                    <p>As requested during our call, I am compiling the whitepapers regarding our HIPAA Business Associate Agreement (BAA), field-level cryptographic encryption at rest, and audit trail retention capabilities.</p>
                    <p>[Draft In Progress - Complete before sending tomorrow morning]</p>
                </div>',
                'body_text' => "Dr. Thorne, Thank you for taking the time to meet with our solutions engineering team today. [Draft In Progress]",
                'received_at' => 'Oct 11 at 5:45 PM',
                'received_raw' => now()->subDays(4)->toIso8601String(),
                'is_read' => true,
                'is_starred' => false,
                'is_deleted' => false,
                'entity_tag' => 'Contact',
                'lead_id' => null,
                'lead_name' => null,
                'lead_company' => null,
                'contact_id' => 'con-04',
                'contact_name' => 'Dr. Aris Thorne',
                'account_name' => 'Biogen Dynamics',
                'open_count' => 0,
                'click_count' => 0,
                'attachments' => [],
                'ai_summary' => null,
                'thread' => []
            ],
            [
                'id' => 'em-006',
                'folder' => 'outbox',
                'direction' => 'outbound',
                'sender_name' => 'Alex Morgan',
                'sender_email' => 'alex.morgan@dealsync.app',
                'recipient_name' => 'Rachel Adams',
                'recipient_email' => 'rachel@vertex-logistics.com',
                'subject' => 'Scheduled: Q4 Enterprise Renewal Notice & Volume Rebate Program',
                'snippet' => 'Hi Rachel, your DealSync Enterprise subscription is coming up for its annual renewal next month. We have prepared an exclusive multi-year volume rebate program...',
                'body_html' => '<div style="font-family: inherit; line-height: 1.6; color: #1e293b;">
                    <p>Hi Rachel,</p>
                    <p>Your DealSync Enterprise license tier is scheduled for its regular annual renewal next month. Over the past 12 months, Vertex Logistics has processed over 18,000 shipments through our platform with 100% data integrity.</p>
                    <p>We are excited to share our <strong>Q4 Multi-Year Growth Incentive</strong>, providing 18% savings when renewing on a 24-month commitment.</p>
                    <p>Scheduled to send tomorrow morning at 9:00 AM EST.</p>
                </div>',
                'body_text' => "Hi Rachel, Your DealSync Enterprise license tier is scheduled for annual renewal next month.",
                'received_at' => 'Scheduled for Tomorrow at 9:00 AM',
                'scheduled_at' => now()->addDay()->setTime(9, 0)->toIso8601String(),
                'received_raw' => now()->toIso8601String(),
                'is_read' => true,
                'is_starred' => false,
                'is_deleted' => false,
                'entity_tag' => 'Contact',
                'lead_id' => null,
                'lead_name' => null,
                'lead_company' => null,
                'contact_id' => 'con-05',
                'contact_name' => 'Rachel Adams',
                'account_name' => 'Vertex Logistics',
                'open_count' => 0,
                'click_count' => 0,
                'attachments' => [],
                'ai_summary' => null,
                'thread' => []
            ],
            [
                'id' => 'em-007',
                'folder' => 'trash',
                'direction' => 'inbound',
                'sender_name' => 'SaaS World Expo',
                'sender_email' => 'invites@events-techb2b.org',
                'sender_city' => 'Chicago',
                'sender_country' => 'United States',
                'sender_ip' => '203.0.113.195',
                'recipient_name' => 'Sales Team',
                'recipient_email' => 'sales@dealsync.app',
                'subject' => 'Special Invite: Global SaaS & Enterprise AI Summit 2026',
                'snippet' => 'Register today for early bird passes to the 2026 Global Enterprise SaaS Expo in San Francisco. Keynotes by cloud computing thought leaders...',
                'body_html' => '<div style="font-family: inherit; line-height: 1.6; color: #1e293b;">
                    <p>Dear Sales Leader,</p>
                    <p>Join 8,000+ B2B software executives at the premier Global SaaS & Enterprise AI Summit this November in San Francisco, California.</p>
                    <p>Claim your complimentary VIP pass with promo code <strong>SAAS2026-VIP</strong> before registration closes on Friday.</p>
                </div>',
                'body_text' => "Dear Sales Leader, Join 8,000+ B2B software executives at the premier Global SaaS & Enterprise AI Summit.",
                'received_at' => 'Oct 09 at 8:20 AM',
                'received_raw' => now()->subDays(6)->toIso8601String(),
                'is_read' => true,
                'is_starred' => false,
                'is_deleted' => true,
                'entity_tag' => 'None',
                'lead_id' => null,
                'lead_name' => null,
                'lead_company' => null,
                'contact_id' => null,
                'contact_name' => null,
                'account_name' => null,
                'open_count' => 0,
                'click_count' => 0,
                'attachments' => [],
                'ai_summary' => null,
                'thread' => []
            ]
        ];

        // Folder Counters
        $folderCounts = [
            'inbox' => 3,
            'sent' => 1,
            'drafts' => 1,
            'outbox' => 1,
            'trash' => 1,
            'campaigns' => 3,
        ];

        // Bulk Campaigns
        $campaigns = [
            [
                'id' => 'camp-01',
                'name' => 'Q4 Enterprise Cloud Modernization Outreach',
                'subject' => 'Scale your revenue with DealSync Isolated Multi-Tenant CRM',
                'target_object' => 'leads',
                'sent_at' => '2026-10-10 14:30:00',
                'total_recipients' => 450,
                'sent_count' => 442,
                'open_count' => 218,
                'click_count' => 76,
                'unsubscribe_count' => 2,
            ],
            [
                'id' => 'camp-02',
                'name' => 'Annual Customer Satisfaction & Feature Survey',
                'subject' => 'Help shape the future of DealSync — Share your thoughts',
                'target_object' => 'contacts',
                'sent_at' => '2026-10-04 10:15:00',
                'total_recipients' => 820,
                'sent_count' => 812,
                'open_count' => 412,
                'click_count' => 154,
                'unsubscribe_count' => 1,
            ],
            [
                'id' => 'camp-03',
                'name' => 'Product Update: High-Velocity Decoupled Conversion Engine',
                'subject' => 'Major update: 3-way atomic lead conversion is live',
                'target_object' => 'leads',
                'sent_at' => '2026-09-28 09:00:00',
                'total_recipients' => 570,
                'sent_count' => 564,
                'open_count' => 265,
                'click_count' => 88,
                'unsubscribe_count' => 0,
            ]
        ];

        return view('emails', compact('emails', 'folderCounts', 'campaigns', 'currentUserName'));
    }
}
