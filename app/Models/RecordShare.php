<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordShare extends Model
{
    protected $connection = 'tenant';
    protected $table = 'record_shares';
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(SharingRule::class, 'rule_id');
    }
}
