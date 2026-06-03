<?php

namespace App\Models;

use CodeIgniter\Model;

class TenantModel extends Model
{
    /**
     * Flag to enable/disable tenant scoping on queries and inserts.
     */
    protected $useTenantScope = true;

    public function __construct()
    {
        parent::__construct();

        // Register event callbacks dynamically
        if ($this->useTenantScope) {
            $this->beforeInsert[] = 'injectTenantId';
            $this->beforeUpdate[] = 'scopeTenantQuery';
            $this->beforeDelete[] = 'scopeTenantQuery';
            $this->beforeFind[]   = 'scopeTenantQuery';
        }
    }

    /**
     * Disable tenant scoping temporarily for this model instance.
     */
    public function disableTenantScope()
    {
        $this->useTenantScope = false;
        return $this;
    }

    /**
     * Enable tenant scoping for this model instance.
     */
    public function enableTenantScope()
    {
        $this->useTenantScope = true;
        return $this;
    }

    /**
     * Check if tenant scoping should be active.
     */
    protected function shouldScope(): bool
    {
        if (!$this->useTenantScope) {
            return false;
        }

        // Session must be active and have a valid clinic_id
        if (function_exists('session')) {
            try {
                $session = session();
                if ($session && $session->has('clinic_id')) {
                    return true;
                }
            } catch (\Throwable $e) {
                // Session not initialized or available (e.g., in CLI, PHPUnit)
            }
        }

        return false;
    }

    /**
     * Automatically inject the clinic_id during insert operations.
     */
    protected function injectTenantId(array $data)
    {
        if ($this->shouldScope()) {
            $clinicId = session()->get('clinic_id');
            if ($clinicId) {
                $data['data']['clinic_id'] = $clinicId;
            }
        }
        return $data;
    }

    /**
     * Automatically apply clinic_id filter to select, update, and delete queries.
     */
    protected function scopeTenantQuery(array $data)
    {
        if ($this->shouldScope()) {
            $clinicId = session()->get('clinic_id');
            if ($clinicId) {
                $this->builder()->where($this->table . '.clinic_id', $clinicId);
            }
        }
        return $data;
    }
}
