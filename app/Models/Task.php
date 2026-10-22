<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'due_date'      => 'date',
            'is_recurring'  => 'boolean',
            'custom_fields' => 'array',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'created_by');
    }
}
