<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplate extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'show_signatory' => 'boolean',
            'show_shipping_address' => 'boolean',
            'allow_direct_email' => 'boolean',
            'requires_approval' => 'boolean',
            'columns_config' => 'array',
            'dynamic_fields_config' => 'array',
            'watermark_config' => 'array',
            'agreement_pages' => 'array',
        ];
    }

    public function generatedDocuments(): HasMany
    {
        return $this->hasMany(GeneratedDocument::class, 'template_id');
    }
}
