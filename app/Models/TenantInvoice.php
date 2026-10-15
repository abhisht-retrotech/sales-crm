<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantInvoice extends Model
{
    protected $connection = 'tenant';
    protected $table = 'tenant_invoices';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount'           => 'float',
            'seats_purchased'  => 'integer',
            'seat_count_after' => 'integer',
            'paid_at'          => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'subscription_id');
    }
}
