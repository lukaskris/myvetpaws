<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnoseDetailMedicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'diagnose_detail_id',
        'medicine_id',
        'dosage',
        'notes',
    ];

    public function diagnoseDetail(): BelongsTo
    {
        return $this->belongsTo(DiagnoseDetail::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
