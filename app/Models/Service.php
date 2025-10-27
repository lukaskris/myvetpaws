<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration',
        'duration_type',
        'price',
        'is_active',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function medicalRecords()
    {
        return $this->belongsToMany(MedicalRecord::class, 'medical_record_service');
    }
}
