<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PageLayout extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected $appends = ['layout'];

    protected function casts(): array
    {
        return [
            'is_default'  => 'boolean',
            'layout_json' => 'array',
        ];
    }

    public function getLayoutAttribute()
    {
        return $this->layout_json;
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ProfileLayoutAssignment::class, 'layout_id');
    }
}
