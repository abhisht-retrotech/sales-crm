<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'credit_limit'   => 'decimal:2',
            'annual_revenue' => 'decimal:2',
            'custom_data'    => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'created_by');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'account_id');
    }

    public function primaryContact()
    {
        return $this->hasOne(Contact::class, 'account_id')->where('is_primary', true);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'account_id');
    }

    public function purchaseDeals(): HasMany
    {
        return $this->hasMany(PurchaseDeal::class, 'account_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'account_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'account_id');
    }

    public function wires(): HasMany
    {
        return $this->hasMany(WireReceived::class, 'reconciled_account_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'related_entity_id')->where('related_entity_type', 'accounts');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'entity_id')->where('entity_type', 'accounts');
    }
}
