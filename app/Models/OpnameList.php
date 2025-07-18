<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameList extends Model
{
    protected $table = 'opname_lists';
    protected $fillable = [
        'name',
        'description',
        'price'
    ];
    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'price' => 'integer',
    ];
}
