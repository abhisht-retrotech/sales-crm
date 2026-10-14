<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRule extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'actions'               => 'array',
            'recipient_ids'         => 'array',
            'recipient_profile_ids' => 'array',
            'is_active'             => 'boolean',
        ];
    }
}
