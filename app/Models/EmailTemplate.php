<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailTemplate extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'attached_file_ids'     => 'array',
            'dynamic_file_keywords' => 'array',
            'is_active'             => 'boolean',
        ];
    }

    public function rules(): HasMany
    {
        return $this->hasMany(EmailRule::class, 'email_template_id');
    }
}
