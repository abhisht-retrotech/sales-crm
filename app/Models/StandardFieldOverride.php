<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StandardFieldOverride extends Model
{
    protected $connection = 'tenant';
    protected $table = 'standard_field_overrides';
    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'updated_at'  => 'datetime',
        ];
    }
}
