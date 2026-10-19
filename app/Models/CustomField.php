<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomField extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_required'        => 'boolean',
            'is_multiselect'     => 'boolean',
            'rollup_filter_json' => 'array',
        ];
    }

    public function globalPicklist(): BelongsTo
    {
        return $this->belongsTo(GlobalPicklist::class, 'global_picklist_id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class, 'custom_field_id');
    }
}
