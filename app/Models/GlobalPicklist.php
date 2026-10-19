<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GlobalPicklist extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'values_json' => 'array',
        ];
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(CustomField::class, 'global_picklist_id');
    }
}
