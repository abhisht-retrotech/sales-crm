<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldHistoryConfig extends Model
{
    protected $connection = 'tenant';
    protected $table = 'field_history_configs';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tracked_fields' => 'array',
            'enabled'        => 'boolean',
        ];
    }
}
