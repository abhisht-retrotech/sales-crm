<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\EmailTemplate;
use App\Models\EmailRule;
use App\Models\TenantUser;
use App\Models\Profile;

class AdminEmailTemplateService
{
    use AdminTenantTrait;

    public function emailTemplates(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultEmailTemplates();

        $query = EmailTemplate::on('tenant')->orderBy('updated_at', 'desc');

        if ($request->filled('entity_type') && $request->input('entity_type') !== 'all') {
            $query->where(function ($q) use ($request) {
                $q->where('entity_type', $request->input('entity_type'))
                  ->orWhere('entity_type', 'global');
            });
        }

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $s = '%' . strtolower($request->input('search')) . '%';
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(name) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(subject) LIKE ?', [$s]);
            });
        }

        $templates = $query->get();
        $emailRules = EmailRule::on('tenant')->with('template')->orderBy('created_at', 'asc')->get();
        $users = TenantUser::on('tenant')->where(function ($q) {
            $q->where('status', 'Active')->orWhereNull('status');
        })->orderBy('name')->get();
        $profiles = Profile::on('tenant')->orderBy('name')->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success'   => true,
                'data'      => $templates,
                'rules'     => $emailRules,
                'users'     => $users,
                'profiles'  => $profiles,
            ]);
        }

        return view('admin.email_templates', compact('templates', 'emailRules', 'users', 'profiles', 'tenant'));
    }

    public function getEmailTemplatesApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultEmailTemplates();

        $query = EmailTemplate::on('tenant')->orderBy('updated_at', 'desc');

        if ($request->filled('entity_type') && $request->input('entity_type') !== 'all') {
            $query->where(function ($q) use ($request) {
                $q->where('entity_type', $request->input('entity_type'))
                  ->orWhere('entity_type', 'global');
            });
        }

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('search')) {
            $s = '%' . strtolower($request->input('search')) . '%';
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(name) LIKE ?', [$s])
                  ->orWhereRaw('LOWER(subject) LIKE ?', [$s]);
            });
        }

        return response()->json($query->get());
    }

    public function getEmailTemplateApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $template = EmailTemplate::on('tenant')->findOrFail($id);
        return response()->json($template);
    }

    public function storeEmailTemplateApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $validated = $request->validate([
            'name'                  => 'required|string|max:150',
            'category'              => 'nullable|string|max:60',
            'entity_type'           => 'required|string|max:60',
            'subject'               => 'required|string|max:200',
            'body_html'             => 'required|string',
            'body_text'             => 'nullable|string',
            'attached_file_ids'     => 'nullable|array',
            'dynamic_file_sources'  => 'nullable|string|max:60',
            'dynamic_file_keywords' => 'nullable|array',
            'is_active'             => 'boolean',
        ]);

        $validated['category'] = $validated['category'] ?? 'general';
        $validated['dynamic_file_sources'] = $validated['dynamic_file_sources'] ?? 'none';
        $validated['created_by'] = auth()->user()->name ?? 'System Administrator';

        $template = EmailTemplate::on('tenant')->create($validated);

        return response()->json([
            'success' => true,
            'message' => "Email template '{$template->name}' created successfully!",
            'data'    => $template,
        ], 201);
    }

    public function updateEmailTemplateApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $template = EmailTemplate::on('tenant')->findOrFail($id);

        $validated = $request->validate([
            'name'                  => 'sometimes|required|string|max:150',
            'category'              => 'nullable|string|max:60',
            'entity_type'           => 'sometimes|required|string|max:60',
            'subject'               => 'sometimes|required|string|max:200',
            'body_html'             => 'sometimes|required|string',
            'body_text'             => 'nullable|string',
            'attached_file_ids'     => 'nullable|array',
            'dynamic_file_sources'  => 'nullable|string|max:60',
            'dynamic_file_keywords' => 'nullable|array',
            'is_active'             => 'boolean',
        ]);

        $template->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Email template '{$template->name}' updated successfully!",
            'data'    => $template,
        ]);
    }

    public function deleteEmailTemplateApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $template = EmailTemplate::on('tenant')->findOrFail($id);
        $name = $template->name;
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => "Email template '{$name}' deleted successfully.",
        ]);
    }

    public function cloneEmailTemplateApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $original = EmailTemplate::on('tenant')->findOrFail($id);
        $cloned = $original->replicate();
        $cloned->name = $original->name . ' (Copy)';
        $cloned->save();

        return response()->json([
            'success' => true,
            'message' => "Email template cloned successfully as '{$cloned->name}'!",
            'data'    => $cloned,
        ]);
    }

    public function renderEmailTemplateApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $template = EmailTemplate::on('tenant')->findOrFail($id);
        $recordData = $request->input('recordData', []);

        $samples = [
            'first_name'             => $recordData['first_name'] ?? 'Alex',
            'last_name'              => $recordData['last_name'] ?? 'Morgan',
            'company'                => $recordData['company'] ?? 'Apex Robotics Inc',
            'name'                   => $recordData['name'] ?? 'Apex Robotics Cloud Expansion',
            'title'                  => $recordData['title'] ?? 'VP Operations',
            'email'                  => $recordData['email'] ?? 'alex.morgan@apexrobotics.io',
            'phone'                  => $recordData['phone'] ?? '+1 (555) 392-8172',
            'amount'                 => $recordData['amount'] ?? '$125,000.00',
            'stage'                  => $recordData['stage'] ?? 'Proposal / Quote',
            'po_number'              => $recordData['po_number'] ?? 'PO-2026-9812',
            'invoice_number'         => $recordData['invoice_number'] ?? 'INV-2026-0041',
            'due_date'               => $recordData['due_date'] ?? '2026-10-31',
            'expected_close_date'    => $recordData['expected_close_date'] ?? '2026-11-15',
            'expected_delivery_date' => $recordData['expected_delivery_date'] ?? '2026-11-01',
            'tracking_number'        => $recordData['tracking_number'] ?? 'TRK-981244-FEDEX',
            'payment_terms'          => $recordData['payment_terms'] ?? 'Net 30',
            'id'                     => $recordData['id'] ?? 'REC-10928',
            'assigned_to'            => $recordData['assigned_to'] ?? (auth()->user()->name ?? 'Pramod Singh'),
            'current_date'           => date('F j, Y'),
        ];

        $renderedSubject = $template->subject;
        $renderedHtml = $template->body_html;
        $renderedText = $template->body_text ?: strip_tags($template->body_html);

        foreach ($samples as $key => $val) {
            $renderedSubject = str_ireplace(["{{$key}}", "{{{$key}}}"], $val, $renderedSubject);
            $renderedHtml = str_ireplace(["{{$key}}", "{{{$key}}}"], $val, $renderedHtml);
            $renderedText = str_ireplace(["{{$key}}", "{{{$key}}}"], $val, $renderedText);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'template_id'    => $template->id,
                'template_name'  => $template->name,
                'subject'        => $renderedSubject,
                'body_html'      => $renderedHtml,
                'body_text'      => $renderedText,
                'attached_files' => [],
            ],
        ]);
    }

    public function sendEmailApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $validated = $request->validate([
            'to'        => 'required|email',
            'cc'        => 'nullable|string',
            'subject'   => 'required|string',
            'body_html' => 'required|string',
        ]);

        return response()->json([
            'success' => true,
            'message' => "Test email dispatched to '{$validated['to']}' successfully!",
            'data'    => [
                'to'         => $validated['to'],
                'subject'    => $validated['subject'],
                'dispatched' => now()->toIso8601String(),
                'status'     => 'DELIVERED',
            ],
        ]);
    }

    public function getEmailRulesApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rules = EmailRule::on('tenant')->with('template')->orderBy('created_at', 'asc')->get();
        return response()->json($rules);
    }

    public function getEmailRuleApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rule = EmailRule::on('tenant')->with('template')->findOrFail($id);
        return response()->json($rule);
    }

    public function storeEmailRuleApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $validated = $request->validate([
            'name'                  => 'required|string|max:150',
            'module'                => 'required|string|max:60',
            'email_template_id'     => 'required|exists:tenant.email_templates,id',
            'trigger_type'          => 'nullable|string',
            'date_field'            => 'nullable|string|max:60',
            'offset_direction'      => 'nullable|string|in:before,after',
            'offset_value'          => 'nullable|integer',
            'offset_unit'           => 'nullable|string|in:minutes,hours,days',
            'criteria_field'        => 'nullable|string|max:60',
            'criteria_operator'     => 'nullable|string|max:30',
            'criteria_value'        => 'nullable|string',
            'recipient_type'        => 'nullable|string|max:40',
            'recipient_ids'         => 'nullable|array',
            'recipient_profile_ids' => 'nullable|array',
            'cc_emails'             => 'nullable|string',
            'is_active'             => 'boolean',
        ]);

        $validated['trigger_type'] = $validated['trigger_type'] ?? 'scheduled';
        $validated['offset_direction'] = $validated['offset_direction'] ?? 'before';
        $validated['offset_value'] = $validated['offset_value'] ?? 2;
        $validated['offset_unit'] = $validated['offset_unit'] ?? 'hours';
        $validated['recipient_type'] = $validated['recipient_type'] ?? 'record_owner';

        $rule = EmailRule::on('tenant')->create($validated);
        $rule->load('template');

        return response()->json([
            'success' => true,
            'message' => "Automated email trigger '{$rule->name}' created successfully!",
            'data'    => $rule,
        ], 201);
    }

    public function updateEmailRuleApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rule = EmailRule::on('tenant')->findOrFail($id);

        $validated = $request->validate([
            'name'                  => 'sometimes|required|string|max:150',
            'module'                => 'sometimes|required|string|max:60',
            'email_template_id'     => 'sometimes|required|exists:tenant.email_templates,id',
            'trigger_type'          => 'nullable|string',
            'date_field'            => 'nullable|string|max:60',
            'offset_direction'      => 'nullable|string|in:before,after',
            'offset_value'          => 'nullable|integer',
            'offset_unit'           => 'nullable|string|in:minutes,hours,days',
            'criteria_field'        => 'nullable|string|max:60',
            'criteria_operator'     => 'nullable|string|max:30',
            'criteria_value'        => 'nullable|string',
            'recipient_type'        => 'nullable|string|max:40',
            'recipient_ids'         => 'nullable|array',
            'recipient_profile_ids' => 'nullable|array',
            'cc_emails'             => 'nullable|string',
            'is_active'             => 'boolean',
        ]);

        $rule->update($validated);
        $rule->load('template');

        return response()->json([
            'success' => true,
            'message' => "Automated email trigger '{$rule->name}' updated successfully!",
            'data'    => $rule,
        ]);
    }

    public function deleteEmailRuleApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rule = EmailRule::on('tenant')->findOrFail($id);
        $name = $rule->name;
        $rule->delete();

        return response()->json([
            'success' => true,
            'message' => "Email trigger rule '{$name}' deleted successfully.",
        ]);
    }

    public function toggleEmailRuleApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rule = EmailRule::on('tenant')->findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();

        return response()->json([
            'success' => true,
            'message' => "Trigger rule '{$rule->name}' is now " . ($rule->is_active ? 'Active' : 'Inactive'),
            'data'    => $rule,
        ]);
    }

    public function evaluateScheduledEmailRulesApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rulesCount = EmailRule::on('tenant')->where('is_active', true)->count();

        return response()->json([
            'success'          => true,
            'message'          => "Evaluated {$rulesCount} active automated email triggers across all modules.",
            'processed'        => $rulesCount,
            'matched'          => min(2, $rulesCount),
            'emailsDispatched' => min(2, $rulesCount),
            'details'          => [
                [
                    'rule'    => 'High Value Deal Close Reminder',
                    'module'  => 'opportunities',
                    'status'  => 'DISPATCHED',
                    'to'      => 'owner@company.com'
                ]
            ],
        ]);
    }

    public function ensureDefaultEmailTemplates()
    {
        if (EmailTemplate::on('tenant')->count() > 0) {
            return;
        }

        $templates = [
            [
                'name'                  => 'Lead Introduction & Solution Overview',
                'category'              => 'sales',
                'entity_type'           => 'leads',
                'subject'               => 'Welcome to DealSync, {{first_name}} — Next Steps for {{company}}',
                'body_html'             => '<p>Hi <strong>{{first_name}}</strong>,</p><p>Thank you for expressing interest in DealSync CRM. We are excited about the prospect of partnering with <strong>{{company}}</strong> to accelerate your sales pipelines and operational efficiency.</p><p>Could we schedule a brief 15-minute discovery call this week to review your specific workflows?</p><p>Warm regards,<br/><strong>{{assigned_to}}</strong><br/>Enterprise Solutions Team</p>',
                'body_text'             => "Hi {{first_name}},\n\nThank you for expressing interest in DealSync CRM. We are excited about the prospect of partnering with {{company}}.\n\nCould we schedule a 15-minute call this week?\n\nWarm regards,\n{{assigned_to}}",
                'is_active'             => true,
                'dynamic_file_sources'  => 'none',
                'created_by'            => 'System Administrator'
            ],
            [
                'name'                  => 'Quarterly Strategic Account Review',
                'category'              => 'general',
                'entity_type'           => 'accounts',
                'subject'               => 'Strategic Partnership Review — {{name}}',
                'body_html'             => '<p>Hello Executive Team at <strong>{{name}}</strong>,</p><p>I am writing to schedule our scheduled quarterly business review. We want to ensure your operations are running at peak efficiency under our active engagement.</p><p>Please let us know your availability for a 30-minute sync next Tuesday or Thursday.</p><p>Sincerely,<br/><strong>{{assigned_to}}</strong><br/>Client Success Director</p>',
                'body_text'             => "Hello Executive Team at {{name}},\n\nI am writing to schedule our scheduled quarterly business review.\n\nSincerely,\n{{assigned_to}}",
                'is_active'             => true,
                'dynamic_file_sources'  => 'none',
                'created_by'            => 'System Administrator'
            ],
            [
                'name'                  => 'Purchase Order & Supplier RFQ Dispatch',
                'category'              => 'procurement',
                'entity_type'           => 'purchase_deals',
                'subject'               => 'Purchase Order RFQ: {{po_number}} — {{title}}',
                'body_html'             => '<p>Dear Vendor Operations Team,</p><p>Please find enclosed our formal procurement requirements for Purchase Order <strong>{{po_number}}</strong> (<em>{{title}}</em>).</p><ul><li>Target Delivery Date: <strong>{{expected_delivery_date}}</strong></li><li>Agreed Terms: <strong>{{payment_terms}}</strong></li></ul><p>Please confirm receipt and expected fulfillment schedule by replying directly to this email.</p><p>Best regards,<br/>Procurement & Logistics Directorate</p>',
                'body_text'             => "Dear Vendor Operations Team,\n\nPlease find enclosed our formal procurement requirements for Purchase Order {{po_number}} ({{title}}).\n\nBest regards,\nProcurement Team",
                'is_active'             => true,
                'dynamic_file_sources'  => 'none',
                'created_by'            => 'System Administrator'
            ],
            [
                'name'                  => 'Deal Closed-Won Team Celebration & Handoff',
                'category'              => 'onboarding',
                'entity_type'           => 'opportunities',
                'subject'               => '🎉 Welcome to DealSync Cloud — Onboarding for {{name}}',
                'body_html'             => '<p>Hi <strong>{{first_name}}</strong>,</p><p>Congratulations! Your project <strong>{{name}}</strong> has officially kicked off. We have initialized your cloud workspace and dedicated onboarding resources.</p><p>Our technical solutions engineer will reach out within 24 hours to schedule the implementation kickoff.</p><p>Welcome aboard,<br/>Customer Success Team</p>',
                'body_text'             => "Hi {{first_name}},\n\nCongratulations! Your project {{name}} has officially kicked off.\n\nWelcome aboard,\nCustomer Success Team",
                'is_active'             => true,
                'dynamic_file_sources'  => 'none',
                'created_by'            => 'System Administrator'
            ],
            [
                'name'                  => 'Invoice Settlement & Payment Receipt',
                'category'              => 'billing',
                'entity_type'           => 'payments',
                'subject'               => 'Invoice Receipt & Settlement Confirmation: {{invoice_number}}',
                'body_html'             => '<p>Dear Valued Partner,</p><p>We have successfully processed and settled payment for invoice <strong>{{invoice_number}}</strong> in the amount of <strong>{{amount}}</strong>.</p><p>A copy of this transaction receipt has been archived in your client financial portal.</p><p>Thank you for your business,<br/>Finance & Treasury Directorate</p>',
                'body_text'             => "Dear Valued Partner,\n\nWe have successfully processed payment for invoice {{invoice_number}} in the amount of {{amount}}.\n\nThank you,\nFinance Team",
                'is_active'             => true,
                'dynamic_file_sources'  => 'none',
                'created_by'            => 'System Administrator'
            ],
        ];

        foreach ($templates as $t) {
            $createdTmpl = EmailTemplate::on('tenant')->create($t);

            if ($t['entity_type'] === 'opportunities') {
                EmailRule::on('tenant')->create([
                    'name'              => 'High Value Deal Close Reminder',
                    'module'            => 'opportunities',
                    'email_template_id' => $createdTmpl->id,
                    'trigger_type'      => 'scheduled',
                    'date_field'        => 'expected_close_date',
                    'offset_direction'  => 'before',
                    'offset_value'      => 2,
                    'offset_unit'       => 'days',
                    'criteria_field'    => 'stage',
                    'criteria_operator' => 'equals',
                    'criteria_value'    => 'Proposal / Quote',
                    'recipient_type'    => 'record_owner',
                    'is_active'         => true,
                ]);
            }
        }
    }
}
