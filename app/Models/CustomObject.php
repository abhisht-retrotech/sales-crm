<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomObject extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    public function records(): HasMany
    {
        return $this->hasMany(CustomObjectRecord::class, 'custom_object_id');
    }
}
