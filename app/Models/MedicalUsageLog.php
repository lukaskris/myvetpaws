<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_record_id',
        'medicine_id',
        'service_id',
        'quantity',
        'note'
    ];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
