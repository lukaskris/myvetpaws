<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Ensure user is authenticated
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please log in to access this page.');
        }

        // 2. Check if roles are specified in the filter argument
        if (empty($arguments)) {
            return;
        }

        $userRole = $session->get('user_role');
        
        // The owner role has a global bypass to perform any operational activity
        if ($userRole === 'owner') {
            return;
        }

        // Check if the current user's role is in the allowed list
        if (!in_array($userRole, $arguments, true)) {
            return redirect()->to('/dashboard')->with('error', 'You do not have permission to access this module.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
