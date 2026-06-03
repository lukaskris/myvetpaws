<?php

namespace App\Models;

use CodeIgniter\Model;

class MedicalRecordServicesModel extends Model
{
    protected $table            = 'medical_record_services';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'medical_record_id',
        'service_id',
        'quantity',
    ];

    protected $useTimestamps = false;
}
