<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentGovernanceRight extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected $casts = [
        'can_generate_from_opportunity' => 'boolean',
        'can_generate_from_deal' => 'boolean',
        'can_direct_create_in_ledger' => 'boolean',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }
}
