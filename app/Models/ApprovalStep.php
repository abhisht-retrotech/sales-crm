<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStep extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    public function process(): BelongsTo
    {
        return $this->belongsTo(ApprovalProcess::class, 'process_id');
    }
}
