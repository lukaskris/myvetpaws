<?php

namespace App\Models;

class PetsModel extends TenantModel
{
    protected $table            = 'pets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'customer_id',
        'clinic_id',
        'name',
        'species',
        'breed',
        'gender',
        'color',
        'birth_date',
        'vaccinated_at',
        'notes',
        'photo',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
