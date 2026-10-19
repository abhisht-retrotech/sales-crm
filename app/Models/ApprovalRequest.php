<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalRequest extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(ApprovalProcess::class, 'process_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'submitted_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class, 'request_id');
    }
}
