<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecordService extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'medical_record_id',
        'service_id',
        'quantity'
    ];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
