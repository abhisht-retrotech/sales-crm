<?php

namespace App\Services\Leads;

use App\Models\Account;
use App\Models\Contact;
use App\Models\FieldHistory;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadConversionService
{
    /**
     * Atomic 3-way decoupled conversion of Lead into Account, Contact, and optional Opportunity.
     */
    public function convert(Lead $lead, Request $request, ?TenantUser $user = null): array
    {
        return DB::connection('tenant')->transaction(function () use ($lead, $request) {
            // 1. Create or link Account
            $account = Account::on('tenant')->create([
                'name' => $lead->company ?: trim($lead->first_name . ' ' . $lead->last_name),
                'type' => 'Customer',
                'phone' => $lead->phone,
                'notes' => $lead->notes,
                'assigned_to' => $lead->assignedUser?->name ?? 'Unassigned',
                'owner_id' => $lead->assigned_to,
            ]);

            // 2. Create Contact
            $contact = Contact::on('tenant')->create([
                'account_id' => $account->id,
                'account_name' => $account->name,
                'first_name' => $lead->first_name,
                'last_name' => $lead->last_name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'title' => $lead->title,
                'is_primary' => true,
                'contact_role' => 'Decision Maker',
                'assigned_to' => $lead->assigned_to,
                'notes' => $lead->notes,
            ]);

            // 3. Optional Opportunity
            $opportunityId = null;
            if ($request->boolean('create_opportunity') || $request->filled('opportunity_name')) {
                $oppName = $request->input('opportunity_name') ?: ($account->name . ' — New Business');
                $opp = Opportunity::on('tenant')->create([
                    'account_id' => $account->id,
                    'contact_id' => $contact->id,
                    'name' => $oppName,
                    'stage' => 'Qualification',
                    'amount' => $request->input('opportunity_amount', 50000.00),
                    'probability' => 20,
                    'close_date' => now()->addDays(45)->toDateString(),
                    'assigned_to' => $lead->assigned_to,
                ]);
                $opportunityId = $opp->id;
            }

            // 4. Update Lead Record as converted
            $lead->update([
                'is_converted' => true,
                'status' => 'Qualified',
                'converted_account_id' => $account->id,
                'converted_contact_id' => $contact->id,
                'converted_opportunity_id' => $opportunityId,
            ]);

            return [
                'accountId' => (string)$account->id,
                'contactId' => (string)$contact->id,
                'opportunityId' => $opportunityId ? (string)$opportunityId : null,
                'accountName' => $account->name,
                'contactName' => "{$contact->first_name} {$contact->last_name}",
            ];
        });
    }

    /**
     * Transfer lead ownership to another user with field history tracking.
     */
    public function transfer(Lead $lead, int $targetUserId, ?TenantUser $user = null): Lead
    {
        $targetUser = TenantUser::on('tenant')->findOrFail($targetUserId);

        $oldOwner = $lead->assignedUser ? $lead->assignedUser->name : ($lead->assigned_to ? "User #{$lead->assigned_to}" : 'Unassigned');
        $newOwner = $targetUser->name;

        $lead->update([
            'assigned_to' => $targetUser->id,
            'updated_by' => $user?->id,
        ]);

        $userId = $user?->id;
        $validUserId = null;
        if ($userId && DB::connection('tenant')->table('users')->where('id', $userId)->exists()) {
            $validUserId = $userId;
        }

        $userName = $user?->name ?? 'System Administrator';

        FieldHistory::on('tenant')->create([
            'entity_type' => 'leads',
            'entity_id' => (string)$lead->id,
            'field_name' => 'assigned_to',
            'field_label' => 'Lead Owner / Assigned User',
            'old_value' => $oldOwner,
            'new_value' => $newOwner,
            'changed_by_id' => $validUserId,
            'changed_by_name' => $userName,
            'created_at' => now(),
        ]);

        return $lead->fresh('assignedUser');
    }
}
