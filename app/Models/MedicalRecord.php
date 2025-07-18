<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pet_id',
        'user_id',
        'weight',
        'temperature',
        'length',
        'notes',
        'diagnosis',
        'treatment_plan',
        'next_visit_at',
        'status',
        'category',
        'clinic_id'
    ];

    protected $casts = [
        'next_visit_at' => 'datetime'
    ];

    public function pet()
    {
        return $this->belongsTo(Pet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'medical_record_service');
    }

    public function medicines()
    {
        return $this->belongsToMany(Medicine::class, 'medical_record_medicine')
            ->withPivot('quantity');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}
