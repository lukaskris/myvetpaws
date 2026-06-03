<?php

namespace App\Models;

class VisitsModel extends TenantModel
{
    protected $table            = 'visits';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'clinic_id',
        'pet_id',
        'customer_id',
        'user_id',
        'checkin_time',
        'status',
        'complaints',
        'weight',
        'temperature',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
