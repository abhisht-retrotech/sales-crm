<?php

namespace App\Services\Leads;

use App\Models\ApprovalProcess;
use App\Models\ApprovalRequest;
use App\Models\Lead;
use App\Models\TenantUser;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LeadDocumentService
{
    /**
     * Format a file record for UI display.
     */
    public function formatFilePayload($file, int|string $leadId): array
    {
        $name = $file->name ?: ($file->original_filename ?? 'Unnamed File');
        $mime = strtolower($file->mime_type ?? '');
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        $iconType = 'default';
        $typeLabel = 'FILE';

        if (in_array($ext, ['xlsx', 'xls', 'csv']) || str_contains($mime, 'spreadsheet') || str_contains($mime, 'excel') || str_contains($mime, 'sheet')) {
            $iconType = 'excel';
            $typeLabel = 'VND.OPENXMLFORMATS-OF...';
        } elseif ($ext === 'pdf' || str_contains($mime, 'pdf')) {
            $iconType = 'pdf';
            $typeLabel = 'PDF';
        } elseif (in_array($ext, ['jpg', 'jpeg'])) {
            $iconType = 'image';
            $typeLabel = 'JPEG';
        } elseif ($ext === 'png') {
            $iconType = 'image';
            $typeLabel = 'PNG';
        } elseif (in_array($ext, ['gif', 'webp', 'svg'])) {
            $iconType = 'image';
            $typeLabel = strtoupper($ext);
        } elseif (in_array($ext, ['doc', 'docx']) || str_contains($mime, 'word')) {
            $iconType = 'word';
            $typeLabel = 'DOCX';
        } else {
            $typeLabel = $ext ? strtoupper($ext) : 'FILE';
        }

        $sizeBytes = $file->size_bytes ?? 0;
        if ($sizeBytes >= 1048576) {
            $sizeFormatted = round($sizeBytes / 1048576, 1) . ' MB';
        } elseif ($sizeBytes >= 1024) {
            $sizeFormatted = round($sizeBytes / 1024, 0) . ' KB';
        } else {
            $sizeFormatted = $sizeBytes > 0 ? $sizeBytes . ' B' : '';
        }

        $createdAtFormatted = '';
        if (!empty($file->created_at)) {
            try {
                $createdAtFormatted = Carbon::parse($file->created_at)->format('d-m-Y H:i');
            } catch (\Exception $e) {
                $createdAtFormatted = (string)$file->created_at;
            }
        }

        return [
            'id' => (int)$file->id,
            'name' => $name,
            'mime_type' => $file->mime_type,
            'type_label' => $typeLabel,
            'icon_type' => $iconType,
            'size_bytes' => (int)$sizeBytes,
            'size_formatted' => $sizeFormatted,
            'download_url' => url("/api/leads/{$leadId}/files/{$file->id}/download"),
            'created_at_formatted' => $createdAtFormatted,
            'created_by_name' => $file->created_by_name ?? 'System Admin',
        ];
    }

    /**
     * Get files linked to a lead.
     */
    public function getFiles(int|string $leadId): Collection
    {
        return DB::connection('tenant')->table('file_links')
            ->join('files', 'file_links.file_id', '=', 'files.id')
            ->leftJoin('file_versions', 'files.latest_version_id', '=', 'file_versions.id')
            ->where('file_links.entity_type', 'leads')
            ->where('file_links.entity_id', $leadId)
            ->select(
                'files.id',
                'files.name',
                'files.mime_type',
                'files.created_at',
                'files.created_by_name',
                'file_versions.original_filename',
                'file_versions.size_bytes',
                'file_versions.storage_key'
            )
            ->orderBy('files.id', 'desc')
            ->get()
            ->map(fn($f) => $this->formatFilePayload($f, $leadId));
    }

    /**
     * Upload and link files to a lead.
     */
    public function uploadFiles(Lead $lead, array $uploadedFiles, ?TenantUser $user = null, ?string $category = null, ?string $description = null): array
    {
        $userId = $user?->id ?? (Auth::guard('tenant')->id() ?? Auth::guard('web')->id());
        $validUserId = null;
        if ($userId && DB::connection('tenant')->table('users')->where('id', $userId)->exists()) {
            $validUserId = $userId;
        }

        $userName = $user?->name ?? 'System Admin';
        if (!$user && $userId) {
            $tenantUser = TenantUser::on('tenant')->find($userId);
            if ($tenantUser) {
                $userName = $tenantUser->name;
            }
        }

        $savedFiles = [];

        foreach ($uploadedFiles as $uploadedFile) {
            if (!$uploadedFile->isValid()) {
                continue;
            }

            $originalName = $uploadedFile->getClientOriginalName();
            $mimeType = $uploadedFile->getClientMimeType() ?: $uploadedFile->getMimeType();
            $sizeBytes = $uploadedFile->getSize();
            $extension = $uploadedFile->getClientOriginalExtension();
            $checksum = @md5_file($uploadedFile->getRealPath());

            // Store file in public storage
            $storageDir = "leads/{$lead->id}/files";
            $storedFilename = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . ($extension ?: 'dat');
            $storedKey = $uploadedFile->storeAs($storageDir, $storedFilename, 'public');

            // 1. Insert into files table
            $fileId = DB::connection('tenant')->table('files')->insertGetId([
                'name' => $originalName,
                'description' => $description,
                'mime_type' => substr($mimeType, 0, 100),
                'created_by_id' => $validUserId,
                'created_by_name' => $userName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Insert version
            $versionId = DB::connection('tenant')->table('file_versions')->insertGetId([
                'file_id' => $fileId,
                'version_number' => 1,
                'storage_key' => $storedKey,
                'original_filename' => $originalName,
                'mime_type' => substr($mimeType, 0, 100),
                'size_bytes' => $sizeBytes,
                'checksum' => $checksum,
                'uploaded_by_id' => $validUserId,
                'uploaded_by_name' => $userName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Update latest_version_id
            DB::connection('tenant')->table('files')->where('id', $fileId)->update([
                'latest_version_id' => $versionId,
            ]);

            // 4. Link file to lead
            DB::connection('tenant')->table('file_links')->insert([
                'file_id' => $fileId,
                'entity_type' => 'leads',
                'entity_id' => (string)$lead->id,
                'visibility' => 'all',
                'created_by_id' => $validUserId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $savedFiles[] = $this->formatFilePayload((object)[
                'id' => $fileId,
                'name' => $originalName,
                'mime_type' => $mimeType,
                'created_at' => now(),
                'created_by_name' => $userName,
                'original_filename' => $originalName,
                'size_bytes' => $sizeBytes,
                'storage_key' => $storedKey,
            ], $lead->id);
        }

        $totalCount = DB::connection('tenant')->table('file_links')
            ->where('entity_type', 'leads')
            ->where('entity_id', (string)$lead->id)
            ->count();

        return [
            'success' => true,
            'message' => count($savedFiles) . ' file(s) uploaded successfully.',
            'files' => $savedFiles,
            'count' => $totalCount,
        ];
    }

    /**
     * Delete file linked to lead.
     */
    public function deleteFile(Lead $lead, int|string $fileId, ?TenantUser $user = null): ?int
    {
        $link = DB::connection('tenant')->table('file_links')
            ->where('file_id', $fileId)
            ->where('entity_type', 'leads')
            ->where('entity_id', (string)$lead->id)
            ->first();

        if (!$link) {
            return null;
        }

        $versions = DB::connection('tenant')->table('file_versions')->where('file_id', $fileId)->get();
        foreach ($versions as $v) {
            if (!empty($v->storage_key) && Storage::disk('public')->exists($v->storage_key)) {
                Storage::disk('public')->delete($v->storage_key);
            }
        }

        DB::connection('tenant')->table('file_links')
            ->where('file_id', $fileId)
            ->where('entity_type', 'leads')
            ->where('entity_id', (string)$lead->id)
            ->delete();
        DB::connection('tenant')->table('file_versions')->where('file_id', $fileId)->delete();
        DB::connection('tenant')->table('files')->where('id', $fileId)->delete();

        return DB::connection('tenant')->table('file_links')
            ->where('entity_type', 'leads')
            ->where('entity_id', (string)$lead->id)
            ->count();
    }

    /**
     * Download binary file linked to lead.
     */
    public function downloadFile(Lead $lead, int|string $fileId)
    {
        $link = DB::connection('tenant')->table('file_links')
            ->where('file_id', $fileId)
            ->where('entity_type', 'leads')
            ->where('entity_id', $lead->id)
            ->first();

        if (!$link) {
            abort(404, 'File not found');
        }

        $version = DB::connection('tenant')->table('file_versions')
            ->where('file_id', $fileId)
            ->orderBy('version_number', 'desc')
            ->first();

        if (!$version || !Storage::disk('public')->exists($version->storage_key)) {
            abort(404, 'File binary not found on disk');
        }

        return Storage::disk('public')->download($version->storage_key, $version->original_filename);
    }

    /**
     * Format an approval request for UI display.
     */
    public function formatApprovalPayload($req): array
    {
        $process = $req->process;
        $latestAction = $req->actions()->latest()->first();
        $step = $process ? $process->steps()->where('step_number', $req->current_step)->first() : null;

        $stepName = $step?->step_name ?? ('Step ' . $req->current_step . ': ' . ($req->status === 'pending' ? 'Pending Approval' : ucfirst($req->status)));
        $assignedTo = $step?->approver_name ?? 'System Administrator';
        $actualApprover = ($latestAction && in_array($latestAction->action, ['approved', 'rejected'])) 
            ? $latestAction->actor_name 
            : '—';
        $comments = $latestAction?->comments ?: ($req->submission_notes ?: '—');

        return [
            'id' => (int)$req->id,
            'process_name' => $process?->name ?? 'Lead Qualification & Discount Approval',
            'step_name' => $stepName,
            'date_formatted' => $req->created_at ? $req->created_at->format('M d, Y, h:i A') : now()->format('M d, Y, h:i A'),
            'status' => ucfirst($req->status),
            'status_raw' => strtolower($req->status),
            'assigned_to' => $assignedTo,
            'actual_approver' => $actualApprover,
            'comments' => $comments,
            'submitter_name' => $req->submitter_name,
        ];
    }

    /**
     * Get approval requests for lead.
     */
    public function getApprovals(int|string $leadId): Collection
    {
        return ApprovalRequest::on('tenant')
            ->where('module', 'leads')
            ->where('record_id', (string)$leadId)
            ->with(['actions', 'process.steps'])
            ->latest()
            ->get()
            ->map(fn($r) => $this->formatApprovalPayload($r));
    }

    /**
     * Submit lead record for approval.
     */
    public function submitApproval(Lead $lead, ?string $notes = null, ?TenantUser $user = null): array
    {
        $userId = $user?->id;
        $validUserId = null;
        if ($userId && DB::connection('tenant')->table('users')->where('id', $userId)->exists()) {
            $validUserId = $userId;
        }

        $userName = $user?->name ?? 'System Administrator';

        $process = ApprovalProcess::on('tenant')->where('module', 'leads')->where('is_active', true)->first();
        if (!$process) {
            $process = ApprovalProcess::on('tenant')->create([
                'name' => 'Lead Qualification & Discount Approval',
                'module' => 'leads',
                'is_active' => true,
                'description' => 'Multi-step verification process for qualified enterprise prospects.',
                'created_by' => $validUserId,
            ]);

            $process->steps()->createMany([
                [
                    'step_number' => 1,
                    'step_name' => 'Technical Scope Review',
                    'approver_type' => 'role',
                    'approver_name' => 'Sales Engineering Lead',
                ],
                [
                    'step_number' => 2,
                    'step_name' => 'Commercial Sign-off & Discount Terms',
                    'approver_type' => 'role',
                    'approver_name' => 'VP of Global Sales',
                ],
            ]);
        }

        $req = ApprovalRequest::on('tenant')->create([
            'process_id' => $process->id,
            'module' => 'leads',
            'record_id' => (string)$lead->id,
            'current_step' => 1,
            'status' => 'pending',
            'submitter_id' => $validUserId,
            'submitter_name' => $userName,
            'submission_notes' => $notes,
        ]);

        return [
            'success' => true,
            'message' => 'Lead record successfully submitted for qualification approval.',
            'approval' => $this->formatApprovalPayload($req->fresh()),
        ];
    }

    /**
     * Take approval action (approved / rejected).
     */
    public function actionApproval(Lead $lead, int|string $requestId, string $action, ?string $comments = null, ?TenantUser $user = null): array
    {
        $req = ApprovalRequest::on('tenant')
            ->where('module', 'leads')
            ->where('record_id', (string)$lead->id)
            ->findOrFail($requestId);

        $userId = $user?->id;
        $validUserId = null;
        if ($userId && DB::connection('tenant')->table('users')->where('id', $userId)->exists()) {
            $validUserId = $userId;
        }

        $userName = $user?->name ?? 'System Administrator';

        $req->actions()->create([
            'step_number' => $req->current_step,
            'actor_id' => $validUserId,
            'actor_name' => $userName,
            'action' => $action,
            'comments' => $comments,
        ]);

        $status = $action === 'approved' ? 'approved' : 'rejected';
        $req->update([
            'status' => $status,
            'comments' => $comments,
        ]);

        return [
            'success' => true,
            'message' => 'Approval status updated to ' . ucfirst($action) . '.',
            'approval' => $this->formatApprovalPayload($req->fresh()),
        ];
    }
}
