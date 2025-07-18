<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

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
