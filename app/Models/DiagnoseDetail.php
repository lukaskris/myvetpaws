<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnoseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'diagnose_id',
        'name',
        'type',
        'prognose',
    ];

    public function diagnose(): BelongsTo
    {
        return $this->belongsTo(Diagnose::class);
    }
}

