<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SharingRule extends Model
{
    protected $connection = 'tenant';
    protected $table = 'sharing_rules';
    protected $guarded = [];

    protected $casts = [
        'criteria_json' => 'array',
        'is_active'     => 'boolean',
    ];

    public function ownershipRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'ownership_role_id');
    }

    public function ownershipProfile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'ownership_profile_id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(RecordShare::class, 'rule_id');
    }
}
