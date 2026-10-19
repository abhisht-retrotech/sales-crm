<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'due_date'      => 'date',
            'start_date'    => 'datetime',
            'end_date'      => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'performed_by');
    }
}
