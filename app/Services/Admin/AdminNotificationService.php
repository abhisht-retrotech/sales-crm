<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use App\Models\NotificationRule;

class AdminNotificationService
{
    use AdminTenantTrait;

    public function notificationRules(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);
        $this->ensureDefaultNotificationRules();

        $rules = NotificationRule::on('tenant')->orderBy('created_at', 'asc')->get();

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'data'    => $rules,
            ]);
        }

        return view('admin.notification_rules', compact('rules', 'tenant'));
    }

    public function ensureDefaultNotificationRules()
    {
        if (NotificationRule::on('tenant')->count() > 0) {
            return;
        }

        $defaults = [
            [
                'name'             => 'Mega Deal Won Celebration',
                'operation'        => 'UPDATE',
                'module'           => 'opportunities',
                'type'             => 'success',
                'title'            => '🎉 Mega Deal Won Celebration',
                'message_template' => 'Deal "{record_name}" over $100k has closed won! High five the team!',
                'actions'          => ['view_record', 'download', 'view_details'],
                'duration_ms'      => 7000,
                'position'         => 'top-right',
                'is_active'        => true,
                'trigger_type'     => 'immediate',
                'criteria_field'   => 'stage',
                'criteria_operator'=> 'equals',
                'criteria_value'   => 'Closed Won',
                'recipient_type'   => 'all_users',
            ],
            [
                'name'             => 'Task Deadline Warning',
                'operation'        => 'UPDATE',
                'module'           => 'tasks',
                'type'             => 'warning',
                'title'            => '⏰ Task Deadline Approaching',
                'message_template' => 'Urgent: Task "{record_name}" is due within the next 24 hours.',
                'actions'          => ['view_record', 'snooze', 'view_details'],
                'duration_ms'      => 10000,
                'position'         => 'top-right',
                'is_active'        => true,
                'trigger_type'     => 'scheduled',
                'date_field'       => 'due_date',
                'offset_direction' => 'before',
                'offset_value'     => 24,
                'offset_unit'      => 'hours',
                'recipient_type'   => 'record_owner',
            ],
            [
                'name'             => 'Wire Transfer Credited',
                'operation'        => 'UPDATE',
                'module'           => 'wires',
                'type'             => 'success',
                'title'            => '💵 Wire Transfer Credited',
                'message_template' => 'Wire settlement for "{record_name}" has matched bank credit records.',
                'actions'          => ['view_record', 'view_details'],
                'duration_ms'      => 5000,
                'position'         => 'top-right',
                'is_active'        => true,
                'trigger_type'     => 'immediate',
                'criteria_field'   => 'status',
                'criteria_operator'=> 'equals',
                'criteria_value'   => 'Matched',
                'recipient_type'   => 'all_users',
            ],
            [
                'name'             => 'Hot Inbound Lead Intake',
                'operation'        => 'CREATE',
                'module'           => 'leads',
                'type'             => 'warning',
                'title'            => '🔥 Hot Inbound Lead Intake',
                'message_template' => 'High-propensity lead "{record_name}" scored >= 85 and needs fast outreach.',
                'actions'          => ['view_record', 'view_details'],
                'duration_ms'      => 6000,
                'position'         => 'top-right',
                'is_active'        => true,
                'trigger_type'     => 'immediate',
                'recipient_type'   => 'record_owner',
            ],
            [
                'name'             => 'Record Saved & Updated',
                'operation'        => 'UPDATE',
                'module'           => 'all',
                'type'             => 'success',
                'title'            => 'Record Saved Successfully',
                'message_template' => 'Modifications to "{record_name}" were successfully committed.',
                'actions'          => ['view_record', 'view_details'],
                'duration_ms'      => 4500,
                'position'         => 'top-right',
                'is_active'        => true,
                'trigger_type'     => 'immediate',
                'recipient_type'   => 'record_owner',
            ],
            [
                'name'             => 'Record Deletion Notice',
                'operation'        => 'DELETE',
                'module'           => 'all',
                'type'             => 'error',
                'title'            => 'Record Removed',
                'message_template' => '"{record_name}" has been permanently removed from {module}.',
                'actions'          => ['undo', 'view_details'],
                'duration_ms'      => 7000,
                'position'         => 'top-right',
                'is_active'        => true,
                'trigger_type'     => 'immediate',
                'recipient_type'   => 'record_owner',
            ],
            [
                'name'             => 'Approval Action Required',
                'operation'        => 'APPROVE',
                'module'           => 'all',
                'type'             => 'warning',
                'title'            => 'Executive Approval Required',
                'message_template' => '"{record_name}" has been submitted for executive review and sign-off.',
                'actions'          => ['approve', 'reject', 'view_details'],
                'duration_ms'      => 8000,
                'position'         => 'top-right',
                'is_active'        => true,
                'trigger_type'     => 'immediate',
                'recipient_type'   => 'all_users',
            ],
            [
                'name'             => 'Operation Cancelled',
                'operation'        => 'CANCEL',
                'module'           => 'all',
                'type'             => 'info',
                'title'            => 'Operation Cancelled',
                'message_template' => 'Action on "{record_name}" was cancelled with no changes committed.',
                'actions'          => ['snooze', 'view_details'],
                'duration_ms'      => 5000,
                'position'         => 'top-right',
                'is_active'        => true,
                'trigger_type'     => 'immediate',
                'recipient_type'   => 'record_owner',
            ],
        ];

        foreach ($defaults as $rule) {
            NotificationRule::on('tenant')->create($rule);
        }
    }

    public function storeNotificationRuleApi(Request $request)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $validated = $request->validate([
            'name'             => 'required|string|max:150',
            'operation'        => 'required|string',
            'module'           => 'required|string',
            'type'             => 'required|string|in:success,error,warning,info',
            'title'            => 'required|string|max:150',
            'message_template' => 'required|string',
            'actions'          => 'nullable|array',
            'duration_ms'      => 'nullable|integer|min:1000|max:30000',
            'position'         => 'nullable|string|in:top-right,top-center,bottom-right,bottom-left',
            'is_active'        => 'nullable|boolean',
            'trigger_type'     => 'nullable|string|in:immediate,scheduled',
            'date_field'       => 'nullable|string',
            'offset_direction' => 'nullable|string|in:before,after',
            'offset_value'     => 'nullable|numeric',
            'offset_unit'      => 'nullable|string|in:minutes,hours,days',
            'criteria_field'   => 'nullable|string',
            'criteria_operator'=> 'nullable|string',
            'criteria_value'   => 'nullable|string',
            'recipient_type'   => 'nullable|string',
        ]);

        $validated['actions'] = $validated['actions'] ?? ['view_record', 'view_details'];
        $validated['duration_ms'] = $validated['duration_ms'] ?? 5500;
        $validated['position'] = $validated['position'] ?? 'top-right';
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;

        $rule = NotificationRule::on('tenant')->create($validated);

        return response()->json([
            'success' => true,
            'message' => "Notification rule '{$rule->name}' created successfully!",
            'data'    => $rule,
        ], 201);
    }

    public function updateNotificationRuleApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rule = NotificationRule::on('tenant')->findOrFail($id);

        $validated = $request->validate([
            'name'             => 'required|string|max:150',
            'operation'        => 'required|string',
            'module'           => 'required|string',
            'type'             => 'required|string|in:success,error,warning,info',
            'title'            => 'required|string|max:150',
            'message_template' => 'required|string',
            'actions'          => 'nullable|array',
            'duration_ms'      => 'nullable|integer|min:1000|max:30000',
            'position'         => 'nullable|string|in:top-right,top-center,bottom-right,bottom-left',
            'is_active'        => 'nullable|boolean',
            'trigger_type'     => 'nullable|string|in:immediate,scheduled',
            'date_field'       => 'nullable|string',
            'offset_direction' => 'nullable|string|in:before,after',
            'offset_value'     => 'nullable|numeric',
            'offset_unit'      => 'nullable|string|in:minutes,hours,days',
            'criteria_field'   => 'nullable|string',
            'criteria_operator'=> 'nullable|string',
            'criteria_value'   => 'nullable|string',
            'recipient_type'   => 'nullable|string',
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $rule->update($validated);

        return response()->json([
            'success' => true,
            'message' => "Notification rule '{$rule->name}' updated successfully!",
            'data'    => $rule->fresh(),
        ]);
    }

    public function deleteNotificationRuleApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rule = NotificationRule::on('tenant')->findOrFail($id);
        $name = $rule->name;
        $rule->delete();

        return response()->json([
            'success' => true,
            'message' => "Notification rule '{$name}' deleted successfully.",
        ]);
    }

    public function toggleNotificationRuleApi(Request $request, $id)
    {
        $tenant = $this->resolveTenantInstance();
        $this->ensureTenantConnection($tenant);

        $rule = NotificationRule::on('tenant')->findOrFail($id);
        $rule->is_active = !$rule->is_active;
        $rule->save();

        return response()->json([
            'success' => true,
            'message' => "Rule '{$rule->name}' is now " . ($rule->is_active ? 'Active' : 'Inactive'),
            'data'    => $rule,
        ]);
    }
}
