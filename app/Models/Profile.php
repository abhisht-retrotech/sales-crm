<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected $casts = [
        'surprise_me_enabled' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(TenantUser::class, 'profile_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(ProfilePermission::class, 'profile_id');
    }

    public function fieldPermissions(): HasMany
    {
        return $this->hasMany(FieldPermission::class, 'profile_id');
    }

    public function dashboardLayout(): HasOne
    {
        return $this->hasOne(DashboardLayout::class, 'profile_id');
    }

    public function layoutAssignments(): HasMany
    {
        return $this->hasMany(ProfileLayoutAssignment::class, 'profile_id');
    }
}
