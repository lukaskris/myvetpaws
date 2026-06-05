<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Check if user is already logged in
        if ($session->get('logged_in')) {
            return;
        }

        // 2. Check for "remember me" cookie
        $rememberToken = $request->getCookie('remember_me');

        if ($rememberToken) {
            $usersModel = new \App\Models\UsersModel();
            // Disable tenant scoping since we don't have clinic_id in session yet
            $user = $usersModel->disableTenantScope()->where('remember_token', $rememberToken)->first();

            if ($user && $user['status'] == 1) {
                $clinicModel = new \App\Models\ClinicsModel();
                $clinic = $clinicModel->find($user['clinic_id']);

                // Establish session
                $session->set([
                    'user_id'     => $user['id'],
                    'clinic_id'   => $user['clinic_id'],
                    'user_name'   => $user['name'],
                    'user_role'   => $user['role'],
                    'logged_in'   => true,
                    'clinic_name' => $clinic ? $clinic['name'] : 'MyVetPaws',
                    'clinic_logo' => $clinic ? $clinic['logo'] : null,
                ]);
                return;
            } else {
                // Clear the invalid cookie
                helper('cookie');
                delete_cookie('remember_me');
            }
        }

        // 3. Not authenticated -> redirect to login page
        return redirect()->to('/login')->with('error', 'Please log in to access this page.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
