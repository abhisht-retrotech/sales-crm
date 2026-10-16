<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomButton extends Model
{
    protected $connection = 'tenant';
    protected $table = 'custom_buttons';

    protected $fillable = [
        'entity_type',
        'name',
        'label',
        'icon',
        'style_variant',
        'action_type',
        'action_config',
        'placement',
        'visibility_conditions',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'action_config'         => 'array',
        'visibility_conditions' => 'array',
        'is_active'             => 'boolean',
        'display_order'         => 'integer',
    ];
}
