<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contact extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_primary'    => 'boolean',
            'email_opt_out' => 'boolean',
            'custom_data'   => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'assigned_to');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'contact_id');
    }

    public function purchaseDeals(): HasMany
    {
        return $this->hasMany(PurchaseDeal::class, 'contact_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
