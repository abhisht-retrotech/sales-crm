<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessFlow extends Model
{
    protected $connection = 'tenant';
    protected $table = 'process_flows';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'trigger_config' => 'array',
            'nodes'          => 'array',
            'edges'          => 'array',
            'version'        => 'integer',
        ];
    }
}
