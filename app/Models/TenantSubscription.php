<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantSubscription extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tenant_subscriptions';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'seat_count'           => 'integer',
            'unit_price_monthly'   => 'float',
            'auto_renew'           => 'boolean',
            'current_period_start' => 'date',
            'current_period_end'   => 'date',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(TenantInvoice::class, 'subscription_id');
    }
}
