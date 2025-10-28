<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'clinic_id',
        'name',
        'alias',
        'type',
        'unit',
        'stock',
        'price',
        'expired_at'
    ];

    protected $casts = [
        'expired_at' => 'datetime'
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function medicalRecords()
    {
        return $this->belongsToMany(MedicalRecord::class, 'medical_record_medicine')
            ->withPivot('quantity');
    }

    public function diagnoseDetailMedicines(): HasMany
    {
        return $this->hasMany(DiagnoseDetailMedicine::class);
    }
}
