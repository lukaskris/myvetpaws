<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiagnosisMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'notes',
    ];

    public function diagnoseDetails(): HasMany
    {
        return $this->hasMany(DiagnoseDetail::class, 'diagnosis_master_id');
    }
}
