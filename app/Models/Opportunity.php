<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opportunity extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount'              => 'decimal:2',
            'probability'         => 'integer',
            'expected_close_date' => 'date',
            'custom_data'         => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // Direct link to procurement purchase deal
    public function purchaseDeal(): BelongsTo
    {
        return $this->belongsTo(PurchaseDeal::class, 'purchase_deal_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'assigned_to');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'opportunity_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'opportunity_id');
    }

    public function wires(): HasMany
    {
        return $this->hasMany(WireReceived::class, 'reconciled_opportunity_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'related_entity_id')->where('related_entity_type', 'opportunities');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'entity_id')->where('entity_type', 'opportunities');
    }
}
