<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\DiagnosisMaster;

class DiagnoseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'diagnose_id',
        'diagnosis_master_id',
        'name',
        'type',
        'prognose',
        'notes',
    ];

    public function diagnose(): BelongsTo
    {
        return $this->belongsTo(Diagnose::class);
    }

    public function diagnosisMaster(): BelongsTo
    {
        return $this->belongsTo(DiagnosisMaster::class);
    }

    public function medicineDetails(): HasMany
    {
        return $this->hasMany(DiagnoseDetailMedicine::class);
    }

    public function serviceDetails(): HasMany
    {
        return $this->hasMany(DiagnoseService::class, 'diagnose_detail_id');
    }
}
