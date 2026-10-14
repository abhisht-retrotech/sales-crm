<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantCurrency extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'is_base'       => 'boolean',
            'is_active'     => 'boolean',
        ];
    }
}
