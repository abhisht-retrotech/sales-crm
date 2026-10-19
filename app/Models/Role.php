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
}
