<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseDeal extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount'                 => 'decimal:2',
            'expected_delivery_date' => 'date',
            'custom_data'            => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'assigned_to');
    }

    // Direct relationship with linked sales opportunities
    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'purchase_deal_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'purchase_deal_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'purchase_deal_id');
    }
}
