<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TenantUser extends Authenticatable
{
    use Notifiable;

    protected $connection = 'tenant';
    protected $table = 'users';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'            => 'hashed',
            'surprise_me_enabled' => 'boolean',
        ];
    }

    protected $appends = [
        'full_name',
        'role_name',
        'profile_name',
        'manager_name',
    ];

    public function getFullNameAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function getRoleNameAttribute(): ?string
    {
        return $this->role?->name;
    }

    public function getProfileNameAttribute(): ?string
    {
        return $this->profile?->name;
    }

    public function getManagerNameAttribute(): ?string
    {
        return $this->manager?->name;
    }

    public function isAdmin(): bool
    {
        $roleName = strtolower($this->role?->name ?? '');
        $profileName = strtolower($this->profile?->name ?? '');

        return in_array($roleName, ['system administrator', 'administrator', 'admin', 'super administrator'])
            || in_array($profileName, ['system administrator', 'administrator', 'admin', 'super administrator'])
            || !empty($this->attributes['is_admin']);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(TenantUser::class, 'manager_id');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class, 'owner_id');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'assigned_to');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class, 'user_id');
    }

    /**
     * Get all user IDs in this user's team / hierarchy:
     * 1. The user themselves ($this->id)
     * 2. Direct reports (manager_id = $this->id)
     * 3. Users belonging to subordinate roles under this user's role
     */
    public function getTeamUserIds(): array
    {
        $userIds = [(int)$this->id];

        // Direct reports
        $reportIds = TenantUser::on('tenant')
            ->where('manager_id', $this->id)
            ->pluck('id')
            ->map(fn($id) => (int)$id)
            ->all();
        $userIds = array_merge($userIds, $reportIds);

        // Subordinate roles in role hierarchy
        if (!empty($this->role_id)) {
            $role = $this->role ?: Role::on('tenant')->find($this->role_id);
            if ($role) {
                $subRoleIds = $role->getAllSubordinateRoleIds();
                if (!empty($subRoleIds)) {
                    $subUserIds = TenantUser::on('tenant')
                        ->whereIn('role_id', $subRoleIds)
                        ->pluck('id')
                        ->map(fn($id) => (int)$id)
                        ->all();
                    $userIds = array_merge($userIds, $subUserIds);
                }
            }
        }

        return array_values(array_unique(array_filter($userIds)));
    }

    protected ?array $cachedModulePermissions = null;

    /**
     * Get module permissions map for this user's profile.
     */
    public function getModulePermissionsMap(): array
    {
        if ($this->cachedModulePermissions !== null) {
            return $this->cachedModulePermissions;
        }

        if ($this->isAdmin()) {
            return $this->cachedModulePermissions = ['*admin*' => true];
        }

        $profile = $this->profile;
        if (!$profile || $profile->is_admin || str_contains(strtolower($profile->name), 'admin')) {
            return $this->cachedModulePermissions = ['*admin*' => true];
        }

        $perms = ProfilePermission::on('tenant')
            ->where('profile_id', $profile->id)
            ->get();

        $map = [];
        foreach ($perms as $p) {
            $map[$p->module] = [
                'can_create'   => (bool)$p->can_create,
                'can_read'     => (bool)$p->can_read,
                'can_update'   => (bool)$p->can_update,
                'can_delete'   => (bool)$p->can_delete,
                'can_transfer' => (bool)$p->can_transfer,
                'record_scope' => $p->record_scope ?: 'Team',
            ];
        }

        return $this->cachedModulePermissions = $map;
    }

    /**
     * Determine if this user can read / view a specific module.
     */
    public function canReadModule(string $module): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $map = $this->getModulePermissionsMap();
        if (isset($map['*admin*'])) {
            return true;
        }

        $aliases = [
            'shipping'       => ['shipments', 'shipping'],
            'shipments'      => ['shipments', 'shipping'],
            'wires'          => ['wires_received', 'wires'],
            'wires_received' => ['wires_received', 'wires'],
            'accounts'       => ['accounts', 'account'],
            'account'        => ['accounts', 'account'],
            'leads'          => ['leads', 'lead'],
            'lead'           => ['leads', 'lead'],
            'contacts'       => ['contacts', 'contact'],
            'contact'        => ['contacts', 'contact'],
        ];

        $modulesToCheck = $aliases[$module] ?? [$module];

        foreach ($modulesToCheck as $mod) {
            if (isset($map[$mod])) {
                return (bool)($map[$mod]['can_read'] ?? true);
            }
        }

        return true;
    }

    /**
     * Determine if this user can create records in a specific module.
     */
    public function canCreateModule(string $module): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $map = $this->getModulePermissionsMap();
        if (isset($map['*admin*'])) {
            return true;
        }

        $aliases = [
            'shipping'       => ['shipments', 'shipping'],
            'shipments'      => ['shipments', 'shipping'],
            'wires'          => ['wires_received', 'wires'],
            'wires_received' => ['wires_received', 'wires'],
            'accounts'       => ['accounts', 'account'],
            'account'        => ['accounts', 'account'],
            'leads'          => ['leads', 'lead'],
            'lead'           => ['leads', 'lead'],
            'contacts'       => ['contacts', 'contact'],
            'contact'        => ['contacts', 'contact'],
        ];

        $modulesToCheck = $aliases[$module] ?? [$module];

        foreach ($modulesToCheck as $mod) {
            if (isset($map[$mod])) {
                return (bool)($map[$mod]['can_create'] ?? true);
            }
        }

        return true;
    }

    /**
     * Determine if this user can update records in a specific module.
     */
    public function canUpdateModule(string $module): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $map = $this->getModulePermissionsMap();
        if (isset($map['*admin*'])) {
            return true;
        }

        $aliases = [
            'shipping'       => ['shipments', 'shipping'],
            'shipments'      => ['shipments', 'shipping'],
            'wires'          => ['wires_received', 'wires'],
            'wires_received' => ['wires_received', 'wires'],
            'accounts'       => ['accounts', 'account'],
            'account'        => ['accounts', 'account'],
            'leads'          => ['leads', 'lead'],
            'lead'           => ['leads', 'lead'],
            'contacts'       => ['contacts', 'contact'],
            'contact'        => ['contacts', 'contact'],
        ];

        $modulesToCheck = $aliases[$module] ?? [$module];

        foreach ($modulesToCheck as $mod) {
            if (isset($map[$mod])) {
                return (bool)($map[$mod]['can_update'] ?? true);
            }
        }

        return true;
    }

    /**
     * Determine if this user can delete records in a specific module.
     */
    public function canDeleteModule(string $module): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $map = $this->getModulePermissionsMap();
        if (isset($map['*admin*'])) {
            return true;
        }

        $aliases = [
            'shipping'       => ['shipments', 'shipping'],
            'shipments'      => ['shipments', 'shipping'],
            'wires'          => ['wires_received', 'wires'],
            'wires_received' => ['wires_received', 'wires'],
            'accounts'       => ['accounts', 'account'],
            'account'        => ['accounts', 'account'],
            'leads'          => ['leads', 'lead'],
            'lead'           => ['leads', 'lead'],
            'contacts'       => ['contacts', 'contact'],
            'contact'        => ['contacts', 'contact'],
        ];

        $modulesToCheck = $aliases[$module] ?? [$module];

        foreach ($modulesToCheck as $mod) {
            if (isset($map[$mod])) {
                return (bool)($map[$mod]['can_delete'] ?? true);
            }
        }

        return true;
    }

    /**
     * Determine if this user can transfer records in a specific module.
     */
    public function canTransferModule(string $module): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $map = $this->getModulePermissionsMap();
        if (isset($map['*admin*'])) {
            return true;
        }

        $aliases = [
            'shipping'       => ['shipments', 'shipping'],
            'shipments'      => ['shipments', 'shipping'],
            'wires'          => ['wires_received', 'wires'],
            'wires_received' => ['wires_received', 'wires'],
            'accounts'       => ['accounts', 'account'],
            'account'        => ['accounts', 'account'],
            'leads'          => ['leads', 'lead'],
            'lead'           => ['leads', 'lead'],
            'contacts'       => ['contacts', 'contact'],
            'contact'        => ['contacts', 'contact'],
        ];

        $modulesToCheck = $aliases[$module] ?? [$module];

        foreach ($modulesToCheck as $mod) {
            if (isset($map[$mod])) {
                return (bool)($map[$mod]['can_transfer'] ?? true);
            }
        }

        return true;
    }
}
