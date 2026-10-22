<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldHistory extends Model
{
    protected $connection = 'tenant';
    protected $table = 'field_history';
    public $timestamps = false;
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'changed_by_id');
    }
}
