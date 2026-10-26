<?php

namespace App\Services\Leads;

use App\Models\Activity;
use App\Models\FieldHistory;
use App\Models\Lead;
use App\Models\Task;
use App\Models\TenantUser;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeadActivityService
{
    /**
     * Get activities formatted for a lead.
     */
    public function getActivities(int|string $leadId): Collection
    {
        return Activity::on('tenant')
            ->where('entity_type', 'leads')
            ->where('entity_id', $leadId)
            ->with('user')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn($act) => $this->formatActivityPayload($act));
    }

    /**
     * Format an activity model for frontend display.
     */
    public function formatActivityPayload(Activity $act): array
    {
        if ($act->start_date && $act->end_date) {
            $callTime = Carbon::parse($act->start_date)->format('M d, Y, h:i A') . ' - ' . Carbon::parse($act->end_date)->format('h:i A');
        } elseif ($act->start_date) {
            $callTime = Carbon::parse($act->start_date)->format('M d, Y, h:i A');
        } elseif ($act->activity_date) {
            $callTime = Carbon::parse($act->activity_date)->format('M d, Y');
        } else {
            $callTime = null;
        }
        $createdAt = $act->created_at ? Carbon::parse($act->created_at)->format('m/d/y, h:i A') : now()->format('m/d/y, h:i A');
        $userName = $act->user?->name ?? 'Alex Morgan';

        return [
            'id' => $act->id,
            'type' => $act->type ?: 'Call',
            'subject' => $act->subject,
            'details' => $act->details,
            'call_time' => $callTime,
            'created_at_formatted' => $createdAt,
            'user_name' => $userName,
        ];
    }

    /**
     * Store activity (Call, Meeting/Event, Note) for a lead.
     */
    public function storeActivity(Lead $lead, array $data, ?TenantUser $user = null): array
    {
        $type = $data['type'] ?? 'Call';
        $subject = trim($data['subject'] ?? '');
        $details = $data['details'] ?? null;

        $startDate = null;
        $endDate = null;
        $activityDate = now()->toDateString();

        $startInput = !empty($data['start_date']) ? $data['start_date'] : (!empty($data['call_datetime']) ? $data['call_datetime'] : null);
        if (!empty($startInput)) {
            try {
                $startDate = Carbon::parse($startInput);
                $activityDate = $startDate->toDateString();
            } catch (\Exception $e) {
                $startDate = now();
            }
        } else {
            $startDate = now();
        }

        if (!empty($data['end_date'])) {
            try {
                $endDate = Carbon::parse($data['end_date']);
            } catch (\Exception $e) {
                $endDate = $startDate ? $startDate->copy()->addHour() : now()->addHour();
            }
        }

        $userId = $user?->id;
        if (!$userId) {
            $firstUser = TenantUser::on('tenant')->where('name', 'like', '%Morgan%')->first()
                      ?? TenantUser::on('tenant')->first();
            $userId = $firstUser?->id;
        }

        $activity = Activity::on('tenant')->create([
            'entity_type' => 'leads',
            'entity_id' => $lead->id,
            'type' => $type,
            'subject' => $subject,
            'details' => $details,
            'performed_by' => $userId,
            'activity_date' => $activityDate,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $activity->load('user');

        $totalCount = Activity::on('tenant')->where('entity_type', 'leads')->where('entity_id', $lead->id)->count();
        $msgType = (in_array(strtolower($type), ['meeting', 'event'])) ? 'Meeting' : $type;
        $actionVerb = strtolower($type) === 'note' ? 'saved' : 'logged';

        return [
            'success' => true,
            'message' => "{$msgType} {$actionVerb} successfully.",
            'activity' => $this->formatActivityPayload($activity),
            'total_count' => $totalCount,
        ];
    }

    /**
     * Store task for a lead.
     */
    public function storeTask(Lead $lead, array $data, ?TenantUser $user = null): array
    {
        $subject = trim($data['subject'] ?? '');
        $priority = $data['priority'] ?? 'Normal';
        $status = $data['status'] ?? 'Open';
        $dueDate = null;
        if (!empty($data['due_date'])) {
            try {
                $dueDate = Carbon::parse($data['due_date'])->toDateString();
            } catch (\Exception $e) {
                $dueDate = now()->addDays(2)->toDateString();
            }
        }

        $userId = $user?->id;
        $assigneeId = $data['assigned_to'] ?? $userId;

        $assignee = $assigneeId ? TenantUser::on('tenant')->find($assigneeId) : null;
        $assigneeName = $assignee ? $assignee->name : 'Alex Morgan';

        $creator = $userId ? TenantUser::on('tenant')->find($userId) : null;
        $creatorName = $creator ? $creator->name : 'System Admin';

        $leadName = trim(($lead->first_name ?? '') . ' ' . ($lead->last_name ?? ''));
        if (empty($leadName)) {
            $leadName = $lead->company ?? ('Lead #' . $lead->id);
        }

        // 1. Create in Task model
        $task = Task::on('tenant')->create([
            'subject' => $subject,
            'priority' => $priority,
            'status' => $status,
            'assigned_to' => $assigneeId,
            'assigned_to_name' => $assigneeName,
            'due_date' => $dueDate,
            'comments' => $data['comments'] ?? null,
            'related_entity_type' => 'leads',
            'related_entity_id' => $lead->id,
            'related_entity_name' => $leadName,
            'reminder_set' => $data['reminder_set'] ?? 'No reminder is set',
            'is_recurring' => (bool)($data['is_recurring'] ?? false),
            'created_by' => $userId,
            'created_by_name' => $creatorName,
        ]);

        // 2. Also log to activities table for timeline
        $activity = Activity::on('tenant')->create([
            'entity_type' => 'leads',
            'entity_id' => $lead->id,
            'type' => 'Task',
            'subject' => $subject,
            'details' => ($data['comments'] ?? null) ?: "Priority: {$priority} | Status: {$status}",
            'performed_by' => $assigneeId,
            'activity_date' => now()->toDateString(),
            'due_date' => $dueDate,
            'start_date' => $dueDate ? Carbon::parse($dueDate) : now(),
        ]);

        $callTime = $dueDate ? Carbon::parse($dueDate)->format('M d, Y') : now()->format('M d, Y');
        $createdAt = now()->format('m/d/y, h:i A');
        $totalCount = Activity::on('tenant')->where('entity_type', 'leads')->where('entity_id', $lead->id)->count();

        return [
            'success' => true,
            'message' => 'Task created and scheduled successfully.',
            'task' => $task,
            'activity' => [
                'id' => $activity->id,
                'type' => 'Task',
                'subject' => $activity->subject,
                'details' => $activity->details,
                'call_time' => $callTime,
                'created_at_formatted' => $createdAt,
                'user_name' => $assigneeName,
            ],
            'total_count' => $totalCount,
        ];
    }

    /**
     * Get field history records for a lead.
     */
    public function getFieldHistory(int|string $leadId): Collection
    {
        return FieldHistory::on('tenant')
            ->where('entity_type', 'leads')
            ->where('entity_id', (string)$leadId)
            ->latest('id')
            ->get()
            ->map(fn($h) => $this->formatHistoryPayload($h));
    }

    /**
     * Format a field history record for UI display.
     */
    public function formatHistoryPayload($h): array
    {
        return [
            'id' => (int)$h->id,
            'date_formatted' => $h->created_at ? $h->created_at->format('M d, Y, h:i A') : now()->format('M d, Y, h:i A'),
            'field_name' => $h->field_label ?: $this->getFieldLabel($h->field_name),
            'user' => $h->changed_by_name ?: ($h->user?->name ?? 'System Administrator'),
            'original_value' => $h->old_value !== null && $h->old_value !== '' ? $h->old_value : '—',
            'new_value' => $h->new_value !== null && $h->new_value !== '' ? $h->new_value : '—',
        ];
    }

    /**
     * Get human-friendly field label.
     */
    public function getFieldLabel(string $fieldName): string
    {
        $labels = [
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'company' => 'Company',
            'email' => 'Email',
            'phone' => 'Phone',
            'title' => 'Title / Job Position',
            'source' => 'Lead Source',
            'status' => 'Lead Status',
            'score' => 'Lead Score',
            'lead_value' => 'Lead Value',
            'rating' => 'Rating',
            'notes' => 'Description / Notes',
            'assigned_to' => 'Lead Owner / Assigned User',
            'email_opt_out' => 'Email Opt Out',
        ];

        return $labels[$fieldName] ?? ucwords(str_replace(['_', '-'], ' ', $fieldName));
    }

    /**
     * Insert field change entries into history.
     */
    public function recordFieldChanges(array $entries): void
    {
        if (!empty($entries)) {
            FieldHistory::on('tenant')->insert($entries);
        }
    }
}
