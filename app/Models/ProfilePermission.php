<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilePermission extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected $casts = [
        'can_create'   => 'boolean',
        'can_read'     => 'boolean',
        'can_update'   => 'boolean',
        'can_delete'   => 'boolean',
        'can_transfer' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }
}
