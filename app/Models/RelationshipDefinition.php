<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RelationshipDefinition extends Model
{
    protected $connection = 'tenant';
    protected $guarded = [];
}
