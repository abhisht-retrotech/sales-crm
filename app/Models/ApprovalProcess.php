<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalProcess extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'approval_actions'          => 'array',
            'rejection_actions'         => 'array',
            'expiry_actions'            => 'array',
            'enable_auto_expiry'        => 'boolean',
            'lock_record_until_approved'=> 'boolean',
            'is_active'                 => 'boolean',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ApprovalStep::class, 'process_id')->orderBy('step_number');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'process_id');
    }
}
