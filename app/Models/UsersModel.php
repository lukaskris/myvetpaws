<?php

namespace App\Models;

class UsersModel extends TenantModel
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'clinic_id',
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'remember_token',
        'email_verified_at',
        'verification_token'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Hash password before inserting or updating
    public function __construct()
    {
        // Add password hashing to the insert/update hooks
        $this->beforeInsert[] = 'hashPassword';
        $this->beforeUpdate[] = 'hashPassword';

        parent::__construct();
    }

    /**
     * Hash user password before database operations.
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            // Check if it's already a hash to avoid double-hashing
            $info = password_get_info($data['data']['password']);
            if ($info['algo'] === null || $info['algo'] === 0 || $info['algoName'] === 'unknown') {
                $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            }
        }
        return $data;
    }
}
