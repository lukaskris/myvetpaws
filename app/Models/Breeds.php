<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Breeds extends Model
{
    protected $table = 'breeds';
    protected $fillable = [
        'name',
    ];
}
