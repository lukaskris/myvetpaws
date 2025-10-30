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
        'detail_item_sections',
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

    protected static function booted(): void
    {
        static::creating(fn (DiagnoseDetail $detail) => $detail->name = static::ensureName($detail));
        static::updating(fn (DiagnoseDetail $detail) => $detail->name = static::ensureName($detail));
    }

    protected static function ensureName(DiagnoseDetail $detail): string
    {
        if (! empty($detail->name)) {
            return $detail->name;
        }

        if (! empty($detail->diagnosis_master_id)) {
            return optional($detail->diagnosisMaster()->first())->name ?? 'Diagnose';
        }

        return 'General';
    }
}
