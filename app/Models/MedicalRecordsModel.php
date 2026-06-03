<?php

namespace App\Models;

class MedicalRecordsModel extends TenantModel
{
    protected $table            = 'medical_records';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'clinic_id',
        'visit_id',
        'pet_id',
        'user_id',
        'diagnosis',
        'treatment_plan',
        'next_visit_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
