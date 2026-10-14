<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WireReceived extends Model
{
    protected $connection = 'tenant';
    protected $table = 'wires_received';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'date_received' => 'date',
            'reconciled_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'reconciled_account_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'reconciled_payment_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class, 'reconciled_opportunity_id');
    }

    public function reconciledUser(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'reconciled_by');
    }
}
