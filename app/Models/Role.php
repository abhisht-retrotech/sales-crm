<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'reports_to_role_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(Role::class, 'reports_to_role_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(TenantUser::class, 'role_id');
    }

    /**
     * Recursively retrieve all descendant subordinate role IDs under this role.
     */
    public function getAllSubordinateRoleIds(): array
    {
        $subordinateIds = [];
        $directSubs = Role::on('tenant')->where('reports_to_role_id', $this->id)->get();
        foreach ($directSubs as $sub) {
            $subordinateIds[] = (int)$sub->id;
            $subordinateIds = array_merge($subordinateIds, $sub->getAllSubordinateRoleIds());
        }
        return array_values(array_unique($subordinateIds));
    }

    /**
     * Alias for getAllSubordinateRoleIds.
     */
    public function getDescendantRoleIds(): array
    {
        return $this->getAllSubordinateRoleIds();
    }
}
