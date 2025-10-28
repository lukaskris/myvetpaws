<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Diagnose extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'prognose',
        'type',
        'opname_list_id',
        'pet_id',
        'duration_days',
    ];

    public function opnameList(): BelongsTo
    {
        return $this->belongsTo(OpnameList::class);
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DiagnoseDetail::class);
    }
}
