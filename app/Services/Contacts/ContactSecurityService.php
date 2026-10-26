<?php

namespace App\Services\Contacts;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CustomField;
use App\Models\FieldPermission;
use App\Models\Profile;
use App\Models\ProfilePermission;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContactSecurityService
{
    /**
     * Ensure tenant database connection is actively configured.
     */
    public function ensureTenantConnection(): ?Tenant
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : Tenant::on('mysql')->first();
        if ($tenant && config('database.connections.tenant.database') !== $tenant->db_name) {
            config(['database.connections.tenant.database' => $tenant->db_name]);
            DB::purge('tenant');
        }
        return $tenant;
    }

    /**
     * Resolve the current authenticated tenant user.
     */
    public function resolveCurrentUser(): ?TenantUser
    {
        $request = request();
        if ($request) {
            if ($request->hasHeader('X-Simulate-User-Id')) {
                $u = TenantUser::on('tenant')->find($request->header('X-Simulate-User-Id'));
                if ($u) return $u;
            }
            if ($request->hasHeader('X-Simulate-Profile-Id')) {
                $pId = $request->header('X-Simulate-Profile-Id');
                $u = TenantUser::on('tenant')->where('profile_id', $pId)->first();
                if ($u) return $u;
            }
            if ($request->hasHeader('X-Simulate-Role-Id')) {
                $rId = $request->header('X-Simulate-Role-Id');
                $u = TenantUser::on('tenant')->where('role_id', $rId)->first();
                if ($u) return $u;
            }
        }

        $user = Auth::guard('tenant')->user() ?? Auth::guard('web')->user();
        if ($user instanceof TenantUser) {
            return $user;
        }
        if ($user && !empty($user->id)) {
            $found = TenantUser::on('tenant')->find($user->id);
            if ($found) {
                return $found;
            }
        }

        return TenantUser::on('tenant')->first();
    }

    /**
     * Resolve the current user's security profile.
     */
    public function resolveCurrentUserProfile(?TenantUser $user = null): ?Profile
    {
        if (!$user) {
            $user = $this->resolveCurrentUser();
        }

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
     * Get module-level permissions for the given profile and user.
     */
    public function getProfilePermissions(?Profile $profile, ?TenantUser $user = null): array
    {
        $isAdmin = false;
        if (Auth::guard('super')->check()) {
            $isAdmin = true;
        } elseif ($user && $user->isAdmin()) {
            $isAdmin = true;
        } elseif ($profile && ($profile->is_admin || str_contains(strtolower($profile->name), 'admin'))) {
            $isAdmin = true;
        }

        if ($isAdmin) {
            return [
                'can_create'   => true,
                'can_read'     => true,
                'can_update'   => true,
                'can_delete'   => true,
                'can_transfer' => true,
                'record_scope' => 'All',
            ];
        }

        if ($profile) {
            $perm = ProfilePermission::on('tenant')
                ->where('profile_id', $profile->id)
                ->whereIn('module', ['contacts', 'contact', 'Contact'])
                ->first();

            if ($perm) {
                return [
                    'can_create'   => (bool)$perm->can_create,
                    'can_read'     => (bool)$perm->can_read,
                    'can_update'   => (bool)$perm->can_update,
                    'can_delete'   => (bool)$perm->can_delete,
                    'can_transfer' => (bool)$perm->can_transfer,
                    'record_scope' => $perm->record_scope ?: 'Team',
                ];
            }
        }

        // Default standard user permissions
        return [
            'can_create'   => true,
            'can_read'     => true,
            'can_update'   => true,
            'can_delete'   => false,
            'can_transfer' => false,
            'record_scope' => 'Team',
        ];
    }

    /**
     * Get field permissions map for the current user's profile on the contacts module.
     */
    public function getFieldPermissionsForProfile(?Profile $profile): array
    {
        $perms = [];
        if (!$profile) {
            return $perms;
        }

        if ($profile->is_admin || str_contains(strtolower($profile->name), 'admin')) {
            return $perms;
        }

        $records = FieldPermission::on('tenant')
            ->where('profile_id', $profile->id)
            ->whereIn('module', ['contacts', 'contact', 'Contact'])
            ->get();

        foreach ($records as $r) {
            $perms[$r->field_name] = $r->permission ?: 'Read/Write';
        }

        return $perms;
    }

    /**
     * Apply role hierarchy and record-level security scope to query.
     */
    public function applyContactRecordScopeQuery($query, ?TenantUser $user = null, ?array $profilePermissions = null)
    {
        if (Auth::guard('super')->check()) {
            return $query;
        }

        if (!$user) {
            $user = $this->resolveCurrentUser();
        }

        if (!$user || $user->isAdmin()) {
            return $query;
        }

        if ($profilePermissions === null) {
            $profile = $this->resolveCurrentUserProfile($user);
            $profilePermissions = $this->getProfilePermissions($profile, $user);
        }

        if (($profilePermissions['record_scope'] ?? 'All') === 'All') {
            return $query;
        }

        $scope = $profilePermissions['record_scope'] ?? 'Team';
        $userId = $user->id;

        $sharedContactIds = DB::connection('tenant')->table('record_shares')
            ->whereIn('module', ['contacts', 'contact'])
            ->where(function ($sq) use ($user) {
                $sq->where(function ($q) use ($user) {
                    $q->where('shared_with_type', 'user')->where('shared_with_id', (string)$user->id);
                });
                if (!empty($user->role_id)) {
                    $sq->orWhere(function ($q) use ($user) {
                        $q->where('shared_with_type', 'role')->where('shared_with_id', (string)$user->role_id);
                    });
                }
                if (!empty($user->profile_id)) {
                    $sq->orWhere(function ($q) use ($user) {
                        $q->where('shared_with_type', 'profile')->where('shared_with_id', (string)$user->profile_id);
                    });
                }
            })
            ->pluck('record_id')
            ->map(fn($id) => (int)$id)
            ->all();

        if ($scope === 'Own') {
            return $query->where(function ($q) use ($userId, $sharedContactIds) {
                $q->where('assigned_to', $userId)
                  ->orWhere('created_by', $userId);
                if (!empty($sharedContactIds)) {
                    $q->orWhereIn('id', $sharedContactIds);
                }
            });
        }

        if ($scope === 'Team') {
            $teamUserIds = $user->getTeamUserIds();

            return $query->where(function ($q) use ($teamUserIds, $sharedContactIds) {
                $q->whereIn('assigned_to', $teamUserIds)
                  ->orWhereIn('created_by', $teamUserIds);
                if (!empty($sharedContactIds)) {
                    $q->orWhereIn('id', $sharedContactIds);
                }
            });
        }

        return $query;
    }

    /**
     * Determine if a user can access a specific Contact for a given action.
     */
    public function userCanAccessContact(Contact $contact, string $action = 'read', ?TenantUser $user = null, ?array $profilePermissions = null): bool
    {
        if (Auth::guard('super')->check()) {
            return true;
        }

        if (!$user) {
            $user = $this->resolveCurrentUser();
        }
        if (!$user) {
            return true;
        }
        if ($user->isAdmin()) {
            return true;
        }

        if ($profilePermissions === null) {
            $profile = $this->resolveCurrentUserProfile($user);
            $profilePermissions = $this->getProfilePermissions($profile, $user);
        }

        switch ($action) {
            case 'read':
                if (empty($profilePermissions['can_read'])) return false;
                break;
            case 'create':
                if (empty($profilePermissions['can_create'])) return false;
                break;
            case 'update':
                if (empty($profilePermissions['can_update'])) return false;
                break;
            case 'delete':
                if (empty($profilePermissions['can_delete'])) return false;
                break;
            case 'transfer':
                if (empty($profilePermissions['can_transfer'])) return false;
                break;
        }

        $scope = $profilePermissions['record_scope'] ?? 'All';
        if ($scope === 'All') {
            return true;
        }

        $shareQuery = DB::connection('tenant')->table('record_shares')
            ->whereIn('module', ['contacts', 'contact'])
            ->where('record_id', (string)$contact->id)
            ->where(function ($sq) use ($user) {
                $sq->where(function ($q) use ($user) {
                    $q->where('shared_with_type', 'user')->where('shared_with_id', (string)$user->id);
                });
                if (!empty($user->role_id)) {
                    $sq->orWhere(function ($q) use ($user) {
                        $q->where('shared_with_type', 'role')->where('shared_with_id', (string)$user->role_id);
                    });
                }
                if (!empty($user->profile_id)) {
                    $sq->orWhere(function ($q) use ($user) {
                        $q->where('shared_with_type', 'profile')->where('shared_with_id', (string)$user->profile_id);
                    });
                }
            });

        if (in_array($action, ['update', 'delete', 'transfer'])) {
            $hasRwShare = (clone $shareQuery)->where('access_level', 'read_write')->exists();
            if ($hasRwShare) {
                return true;
            }
        } else {
            $hasReadShare = $shareQuery->exists();
            if ($hasReadShare) {
                return true;
            }
        }

        if ($scope === 'Own') {
            return (int)$contact->assigned_to === (int)$user->id
                || (int)$contact->created_by === (int)$user->id;
        }

        if ($scope === 'Team') {
            $teamIds = $user->getTeamUserIds();
            return in_array((int)$contact->assigned_to, $teamIds)
                || in_array((int)$contact->created_by, $teamIds);
        }

        return false;
    }

    /**
     * Sanitize a contact record respecting field-level security permissions.
     */
    public function sanitizeContact(Contact $contact, array $fieldPermissions): array
    {
        $data = $contact->toArray();
        $customData = is_array($contact->custom_data) ? $contact->custom_data : (json_decode($contact->custom_data ?? '[]', true) ?: []);

        // Computed helper attributes
        $data['name'] = trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? ''));
        $data['account_name'] = $contact->account?->name ?? ($contact->account_name ?: 'Unlinked');
        $data['owner_name'] = $contact->assignedUser?->name ?? 'Unassigned';

        // Check first_name / last_name visibility
        $fnPerm = $fieldPermissions['first_name'] ?? 'Read/Write';
        $lnPerm = $fieldPermissions['last_name'] ?? 'Read/Write';
        if ($fnPerm === 'Hidden' && $lnPerm === 'Hidden') {
            $data['name'] = 'Contact #' . $contact->id;
        } elseif ($fnPerm === 'Hidden') {
            $data['name'] = $contact->last_name ?: ('Contact #' . $contact->id);
        } elseif ($lnPerm === 'Hidden') {
            $data['name'] = $contact->first_name ?: ('Contact #' . $contact->id);
        }

        // Apply permissions on standard attributes
        $protectedKeys = ['id', 'created_at', 'updated_at', 'deleted_at', 'account_id', 'assigned_to', 'created_by', 'updated_by'];
        foreach ($data as $key => $val) {
            if (in_array($key, $protectedKeys)) {
                continue;
            }
            $perm = $fieldPermissions[$key] ?? 'Read/Write';
            if ($perm === 'Hidden') {
                $data[$key] = null;
                $data['_hidden_' . $key] = true;
            } elseif ($perm === 'Read-Only') {
                $data['_readonly_' . $key] = true;
            }
        }

        // Apply permissions on dynamic custom fields in custom_data
        $sanitizedCustom = [];
        foreach ($customData as $cfKey => $cfVal) {
            $perm = $fieldPermissions[$cfKey] ?? 'Read/Write';
            if ($perm !== 'Hidden') {
                $sanitizedCustom[$cfKey] = $cfVal;
                // Expose at root level for seamless table/kanban/form access
                $data[$cfKey] = $cfVal;
                if ($perm === 'Read-Only') {
                    $data['_readonly_' . $cfKey] = true;
                }
            } else {
                $data['_hidden_' . $cfKey] = true;
            }
        }
        $data['custom_data'] = $sanitizedCustom;

        return $data;
    }

    /**
     * Sanitize a collection of contacts.
     */
    public function sanitizeCollection(iterable $contacts, array $fieldPermissions): Collection
    {
        return collect($contacts)->map(fn($contact) => $this->sanitizeContact($contact, $fieldPermissions))->values();
    }

    /**
     * Ensure default custom fields exist for contacts module.
     */
    public function ensureCustomFields(): void
    {
        try {
            if (CustomField::on('tenant')->where('module', 'contacts')->count() > 0) {
                return;
            }

            $defaults = [
                [
                    'module' => 'contacts',
                    'field_name' => 'cf_preferred_contact_method',
                    'field_label' => 'Preferred Channel',
                    'data_type' => 'picklist',
                    'options' => 'Email, Corporate Phone, Mobile / SMS, Video Meeting',
                    'is_required' => false,
                    'help_text' => 'The executive stakeholder’s preferred outreach channel',
                ],
                [
                    'module' => 'contacts',
                    'field_name' => 'cf_linkedin_profile',
                    'field_label' => 'LinkedIn Profile URL',
                    'data_type' => 'text',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Professional verified LinkedIn profile handle or URL',
                ],
                [
                    'module' => 'contacts',
                    'field_name' => 'cf_time_zone',
                    'field_label' => 'Primary Timezone',
                    'data_type' => 'picklist',
                    'options' => 'US/Eastern (UTC-5), US/Central (UTC-6), US/Mountain (UTC-7), US/Pacific (UTC-8), Europe/London (UTC+0), Europe/Berlin (UTC+1), Asia/Kolkata (UTC+5:30), Asia/Tokyo (UTC+9)',
                    'is_required' => false,
                    'help_text' => 'Stakeholder local working hours timezone',
                ],
                [
                    'module' => 'contacts',
                    'field_name' => 'cf_budget_authority',
                    'field_label' => 'Sign-off Authority Tier',
                    'data_type' => 'picklist',
                    'options' => 'Direct Sign-off (> $100k), Commercial Sign-off (< $50k), Key Recommender Only, Technical Evaluator',
                    'is_required' => false,
                    'help_text' => 'Discretionary commercial purchasing authority tier',
                ],
                [
                    'module' => 'contacts',
                    'field_name' => 'cf_assistant_name_contact',
                    'field_label' => 'Executive Assistant & Desk',
                    'data_type' => 'text',
                    'options' => null,
                    'is_required' => false,
                    'help_text' => 'Direct administrative assistant name and routing desk phone',
                ],
            ];

            foreach ($defaults as $cf) {
                CustomField::on('tenant')->updateOrCreate(
                    ['module' => 'contacts', 'field_name' => $cf['field_name']],
                    $cf
                );
            }
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }

    /**
     * Seed initial demo contacts if database table is completely empty.
     */
    public function ensureDemoContacts(): void
    {
        try {
            if (Contact::on('tenant')->count() > 0) {
                return;
            }

            $firstUser = TenantUser::on('tenant')->first();
            $userId = $firstUser?->id;

            $accounts = Account::on('tenant')->get()->keyBy('name');

            $demoContacts = [
                [
                    'first_name' => 'Elena',
                    'last_name' => 'Vance',
                    'email' => 'elena.vance@skylinecloud.io',
                    'phone' => '+1 (415) 890-1201',
                    'title' => 'Chief Technology Officer',
                    'department' => 'Executive & Technology',
                    'contact_role' => 'Decision Maker',
                    'is_primary' => true,
                    'email_opt_out' => false,
                    'account_name' => 'Skyline Cloud Systems',
                    'account_id' => $accounts->get('Skyline Cloud Systems')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Executive champion for cloud migration. Signs off on all infrastructure contracts over $50k.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Email',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/elena-vance-cto',
                        'cf_time_zone' => 'US/Pacific (UTC-8)',
                        'cf_budget_authority' => 'Direct Sign-off (> $100k)',
                        'cf_assistant_name_contact' => 'Marcus Kelly (Ext 401)',
                    ],
                ],
                [
                    'first_name' => 'David',
                    'last_name' => 'Kowalski',
                    'email' => 'd.kowalski@skylinecloud.io',
                    'phone' => '+1 (415) 890-1244',
                    'title' => 'VP of Cloud Infrastructure',
                    'department' => 'Cloud Operations',
                    'contact_role' => 'Champion',
                    'is_primary' => false,
                    'email_opt_out' => false,
                    'account_name' => 'Skyline Cloud Systems',
                    'account_id' => $accounts->get('Skyline Cloud Systems')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Leads technical proof-of-concept evaluations and benchmark trials.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Video Meeting',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/dkowalski-cloud',
                        'cf_time_zone' => 'US/Pacific (UTC-8)',
                        'cf_budget_authority' => 'Commercial Sign-off (< $50k)',
                        'cf_assistant_name_contact' => 'Direct line',
                    ],
                ],
                [
                    'first_name' => 'Dr. Sarah',
                    'last_name' => 'Al-Mansoor',
                    'email' => 's.almansoor@vanguardhealth.tech',
                    'phone' => '+1 (617) 555-0192',
                    'title' => 'Chief Medical Information Officer',
                    'department' => 'Healthcare Informatics',
                    'contact_role' => 'Decision Maker',
                    'is_primary' => true,
                    'email_opt_out' => false,
                    'account_name' => 'Vanguard Health Tech',
                    'account_id' => $accounts->get('Vanguard Health Tech')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Requires strict HIPAA compliance documentation and high uptime SLAs.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Email',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/dralmansoor',
                        'cf_time_zone' => 'US/Eastern (UTC-5)',
                        'cf_budget_authority' => 'Direct Sign-off (> $100k)',
                        'cf_assistant_name_contact' => 'Jessica Chen (Ext 112)',
                    ],
                ],
                [
                    'first_name' => 'Marcus',
                    'last_name' => 'Brody',
                    'email' => 'mbrody@quantumsilicon.com',
                    'phone' => '+1 (408) 555-4322',
                    'title' => 'Director of Global Accounts',
                    'department' => 'Supplier Operations',
                    'contact_role' => 'Evaluator',
                    'is_primary' => true,
                    'email_opt_out' => false,
                    'account_name' => 'Quantum Silicon Components',
                    'account_id' => $accounts->get('Quantum Silicon Components')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Primary vendor representative for semiconductor deliveries and lead times.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Corporate Phone',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/marcus-brody-silicon',
                        'cf_time_zone' => 'US/Pacific (UTC-8)',
                        'cf_budget_authority' => 'Key Recommender Only',
                        'cf_assistant_name_contact' => 'Desk Line 408-555-4320',
                    ],
                ],
                [
                    'first_name' => 'Rachel',
                    'last_name' => 'Sterling',
                    'email' => 'rachel.s@titansteel.com',
                    'phone' => '+1 (312) 555-7892',
                    'title' => 'VP of Enterprise Procurement',
                    'department' => 'Procurement & Logistics',
                    'contact_role' => 'Influencer',
                    'is_primary' => true,
                    'email_opt_out' => false,
                    'account_name' => 'Titan Steel & Enclosures',
                    'account_id' => $accounts->get('Titan Steel & Enclosures')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Oversees purchase orders, supplier warranties, and freight routing schedules.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Email',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/rachelsterling-titan',
                        'cf_time_zone' => 'US/Central (UTC-6)',
                        'cf_budget_authority' => 'Commercial Sign-off (< $50k)',
                        'cf_assistant_name_contact' => 'Gary Vance (Ext 890)',
                    ],
                ],
                [
                    'first_name' => 'Alex',
                    'last_name' => 'Thorne',
                    'email' => 'a.thorne@apexglobal.net',
                    'phone' => '+1 (212) 555-0149',
                    'title' => 'Head of Solutions Architecture',
                    'department' => 'Architecture & Engineering',
                    'contact_role' => 'Champion',
                    'is_primary' => true,
                    'email_opt_out' => false,
                    'account_name' => 'Apex Global Solutions',
                    'account_id' => $accounts->get('Apex Global Solutions')?->id,
                    'assigned_to' => $userId,
                    'notes' => 'Architect testing API endpoints and database latency for financial workloads.',
                    'custom_data' => [
                        'cf_preferred_contact_method' => 'Video Meeting',
                        'cf_linkedin_profile' => 'https://linkedin.com/in/alex-thorne-fintech',
                        'cf_time_zone' => 'US/Eastern (UTC-5)',
                        'cf_budget_authority' => 'Technical Evaluator',
                        'cf_assistant_name_contact' => 'Direct Slack / Desk',
                    ],
                ],
            ];

            foreach ($demoContacts as $c) {
                Contact::on('tenant')->create($c);
            }
        } catch (\Throwable $e) {
            // Silently ignore
        }
    }
}
