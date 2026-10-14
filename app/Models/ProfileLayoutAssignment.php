<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileLayoutAssignment extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function layout(): BelongsTo
    {
        return $this->belongsTo(PageLayout::class, 'layout_id');
    }
}
