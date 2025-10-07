<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diagnose extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'prognose',
        'type',
        'opname_list_id',
    ];

    public function opnameList(): BelongsTo
    {
        return $this->belongsTo(OpnameList::class);
    }
}