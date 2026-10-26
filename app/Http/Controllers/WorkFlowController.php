<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ProcessFlow;
use App\Models\Tenant;
use App\Models\Profile;
use App\Models\ApprovalProcess;
use App\Models\ApprovalStep;


class WorkFlowController extends Controller
{
    /**
     * Ensure tenant database connection is actively configured.
     */
    protected function ensureTenantConnection()
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : Tenant::on('mysql')->first();
        if ($tenant && config('database.connections.tenant.database') !== $tenant->db_name) {
            config(['database.connections.tenant.database' => $tenant->db_name]);
            DB::purge('tenant');
        }
        return $tenant;
    }

    /**
     * Resolve the current user's security profile.
     */
    protected function resolveCurrentUserProfile(): ?Profile
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('tenant')->user();
        if ($user && !empty($user->profile_id)) {
            $prof = Profile::on('tenant')->find($user->profile_id);
            if ($prof) {
                return $prof;
            }
        }

        // Fallback for Admin / Super Admin
        return Profile::on('tenant')->where('name', 'like', '%admin%')->first()
            ?? Profile::on('tenant')->first();
    }

    /**
     * List all process flows stored in database.
     */
    public function index(Request $request)
    {
        $this->ensureTenantConnection();

        $flows = ProcessFlow::on('tenant')->latest()->get();

        return response()->json([
            'success' => true,
            'count'   => $flows->count(),
            'data'    => $flows,
        ]);
    }

    /**
     * Store or update a process flow in the database.
     */
    public function store(Request $request)
    {
        $this->ensureTenantConnection();

        $validated = $request->validate([
            'id'             => 'nullable',
            'name'           => 'nullable|string|max:150',
            'title'          => 'nullable|string|max:150',
            'description'    => 'nullable|string',
            'subtitle'       => 'nullable|string',
            'category'       => 'nullable|string|max:60',
            'categoryType'   => 'nullable|string|max:60',
            'entity_type'    => 'nullable|string|max:60',
            'entity'         => 'nullable|string|max:60',
            'trigger_type'   => 'nullable|string|max:40',
            'trigger_config' => 'nullable',
            'flow_key'       => 'nullable|string|max:100',
            'preset_key'     => 'nullable|string|max:100',
            'nodes'          => 'required|array',
            'edges'          => 'nullable|array',
            'status'         => 'nullable|string|max:30',
        ]);

        $name = $validated['name'] ?? $validated['title'] ?? 'Process Studio Flow';
        $description = $validated['description'] ?? $validated['subtitle'] ?? null;
        $category = $validated['category'] ?? $validated['categoryType'] ?? 'Automation Rule';
        $entityType = $validated['entity_type'] ?? $validated['entity'] ?? 'Opportunities';
        $status = strtolower($validated['status'] ?? 'active');
        $flowKey = $validated['flow_key'] ?? $validated['preset_key'] ?? $request->input('preset_key') ?? $request->input('trigger_config.flow_key');

        if ((empty($name) || $name === 'Process Studio Flow') && $flowKey) {
            $name = ucwords(str_replace(['-', '_'], ' ', $flowKey));
        }

        // Determine trigger_type from input or detect from nodes
        $triggerType = $validated['trigger_type'] ?? null;
        if (!$triggerType && is_array($validated['nodes'])) {
            foreach ($validated['nodes'] as $node) {
                if (($node['type'] ?? '') === 'processTrigger') {
                    $subtype = $node['data']['subtype'] ?? '';
                    if ($subtype === 'record_event') $triggerType = 'record_event';
                    elseif ($subtype === 'button_click') $triggerType = 'button_click';
                    elseif ($subtype === 'webhook') $triggerType = 'webhook';
                    elseif ($subtype === 'schedule') $triggerType = 'schedule';
                    break;
                }
            }
        }
        if (!$triggerType) {
            $triggerType = 'button_click';
        }

        // Prepare trigger_config JSON
        $triggerConfig = is_array($validated['trigger_config'] ?? null)
            ? $validated['trigger_config']
            : [];
        if ($flowKey) {
            $triggerConfig['flow_key'] = $flowKey;
            $triggerConfig['preset_key'] = $flowKey;
        }

        // Get current user identifier
        $userName = Auth::user()->name ?? Auth::guard('tenant')->user()->name ?? 'System Admin';

        // Check if an existing flow matches by id, flow_key or name
        $existing = null;
        if (!empty($validated['id']) && is_numeric($validated['id'])) {
            $existing = ProcessFlow::on('tenant')->find($validated['id']);
        }
        if (!$existing && $flowKey) {
            $existing = ProcessFlow::on('tenant')
                ->where(function ($q) use ($flowKey) {
                    $q->where('trigger_config->flow_key', $flowKey)
                      ->orWhere('trigger_config->preset_key', $flowKey);
                })
                ->first();
        }
        if (!$existing && !empty($name)) {
            $existing = ProcessFlow::on('tenant')
                ->where('name', $name)
                ->first();
        }

        if ($existing) {
            $existing->update([
                'name'           => $name,
                'description'    => $description,
                'category'       => $category,
                'entity_type'    => $entityType,
                'trigger_type'   => $triggerType,
                'trigger_config' => $triggerConfig,
                'nodes'          => $validated['nodes'],
                'edges'          => $validated['edges'] ?? [],
                'status'         => $status,
                'version'        => ($existing->version ?? 1) + 1,
                'updated_by'     => $userName,
            ]);

            $this->syncApprovalProcessIfApplicable($existing, $validated['nodes'], $status, $flowKey);

            return response()->json([
                'success' => true,
                'message' => "Workflow \"{$name}\" updated successfully in database!",
                'flow_id' => $existing->id,
                'data'    => $existing->fresh(),
            ]);
        }

        $newFlow = ProcessFlow::on('tenant')->create([
            'name'           => $name,
            'description'    => $description,
            'category'       => $category,
            'entity_type'    => $entityType,
            'trigger_type'   => $triggerType,
            'trigger_config' => $triggerConfig,
            'nodes'          => $validated['nodes'],
            'edges'          => $validated['edges'] ?? [],
            'status'         => $status,
            'version'        => 1,
            'created_by'     => $userName,
            'updated_by'     => $userName,
        ]);

        $this->syncApprovalProcessIfApplicable($newFlow, $validated['nodes'], $status, $flowKey);

        return response()->json([
            'success' => true,
            'message' => "Workflow \"{$name}\" saved and stored in database successfully!",
            'flow_id' => $newFlow->id,
            'data'    => $newFlow,
        ], 201);
    }

    /**
     * Synchronize workflow nodes & status with tenant ApprovalProcess & ApprovalStep engine.
     */
    protected function syncApprovalProcessIfApplicable(ProcessFlow $flow, array $nodes, string $status, ?string $flowKey = null): void
    {
        $entityType = strtolower($flow->entity_type ?? 'opportunities');
        $isApprovalCategory = str_contains(strtolower($flow->category ?? ''), 'approval');
        $isLeadWorkflow = ($entityType === 'leads' || $flowKey === 'lead-qualification-approval');

        if (!$isLeadWorkflow && !$isApprovalCategory) {
            return;
        }

        $module = $entityType === 'leads' ? 'leads' : $entityType;
        $isActive = (strtolower($status) === 'active');
        $hasLockNode = false;
        $approvalSteps = [];

        foreach ($nodes as $node) {
            $type = $node['type'] ?? '';
            $subtype = $node['data']['subtype'] ?? '';
            $title = strtolower($node['data']['title'] ?? '');

            if ($subtype === 'lock_record' || str_contains($title, 'lock')) {
                $hasLockNode = true;
            }

            if ($type === 'processApprovalStep' || $subtype === 'approval_step' || $subtype === 'approval') {
                $approvalSteps[] = [
                    'title'    => $node['data']['title'] ?? 'Qualification Sign-Off',
                    'approver' => $node['data']['approver'] ?? 'Direct Manager or Administrator',
                    'sla'      => $node['data']['sla'] ?? '12h SLA',
                    'badge'    => $node['data']['stepBadge'] ?? 'STEP 1: MANAGER / ADMIN',
                ];
            }
        }

        $approvalProcess = ApprovalProcess::on('tenant')->updateOrCreate(
            ['module' => $module],
            [
                'name'                      => $flow->name ?? 'Lead Intake Governance & Approval',
                'description'               => $flow->description ?? 'All newly created leads initialize in Pending status with task operations locked until authorized review by Manager or Administrator',
                'trigger_type'              => 'on_create',
                'lock_record_until_approved'=> $hasLockNode || count($approvalSteps) > 0,
                'is_active'                 => $isActive,
                'notification_channel'      => 'both',
            ]
        );

        if ($approvalProcess && count($approvalSteps) > 0) {
            ApprovalStep::on('tenant')->where('process_id', $approvalProcess->id)->delete();
            $stepNumber = 1;
            foreach ($approvalSteps as $step) {
                ApprovalStep::on('tenant')->create([
                    'process_id'    => $approvalProcess->id,
                    'step_number'   => $stepNumber++,
                    'step_name'     => $step['title'],
                    'approver_type' => 'role',
                    'approver_name' => $step['approver'],
                ]);
            }
        }
    }

    /**
     * Show a single process flow from database.
     */
    public function show($id)
    {
        $this->ensureTenantConnection();

        $flow = is_numeric($id)
            ? ProcessFlow::on('tenant')->find($id)
            : ProcessFlow::on('tenant')
                ->where('name', $id)
                ->orWhere('trigger_config->flow_key', $id)
                ->orWhere('trigger_config->preset_key', $id)
                ->first();

        if (!$flow) {
            return response()->json([
                'success' => false,
                'message' => 'Workflow process not found in database',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $flow,
        ]);
    }

    /**
     * Delete a process flow from database.
     */
    public function destroy($id)
    {
        $this->ensureTenantConnection();

        $flow = ProcessFlow::on('tenant')->find($id);
        if ($flow) {
            $flow->delete();
            return response()->json([
                'success' => true,
                'message' => 'Workflow deleted from database successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Workflow not found',
        ], 404);
    }

    public function canvas(Request $request)
{
    $this->ensureTenantConnection();

    $flow = null;
    if ($key = $request->query('flow')) {
        $flow = ProcessFlow::on('tenant')
            ->where('trigger_config->flow_key', $key)
            ->orWhere('id', $key)
            ->first();
    }

    return view('process_studio', ['flowData' => $flow]);
}
}
