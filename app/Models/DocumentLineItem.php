<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLineItem extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'quantity'   => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total'      => 'decimal:2',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(GeneratedDocument::class, 'document_id');
    }
}
