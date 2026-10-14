<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomObjectRecord extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'attributes_json' => 'array',
        ];
    }

    public function customObject(): BelongsTo
    {
        return $this->belongsTo(CustomObject::class, 'custom_object_id');
    }
}
