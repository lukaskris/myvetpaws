<?php

namespace App\Models;

use CodeIgniter\Model;

class ClinicsModel extends Model
{
    protected $table            = 'clinics';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'name',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'description',
        'logo',
        'banner',
        'status',
        'slug',
        'public_visibility',
        'latitude',
        'longitude',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
