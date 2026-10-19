<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use SoftDeletes;

    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'score'         => 'integer',
            'is_converted'  => 'boolean',
            'email_opt_out' => 'boolean',
            'custom_data'   => 'array',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(TenantUser::class, 'assigned_to');
    }

    public function convertedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'converted_account_id');
    }

    public function convertedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'converted_contact_id');
    }

    public function convertedOpportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class, 'converted_opportunity_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'related_entity_id')->where('related_entity_type', 'leads');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'entity_id')->where('entity_type', 'leads');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}
