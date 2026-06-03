<?php

namespace App\Controllers;

use App\Models\UsersModel;
use App\Models\ClinicsModel;

class AuthController extends BaseController
{
    public function login()
    {
        // Redirect to dashboard if already logged in
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/login');
    }

    public function attempt()
    {
        $throttler = \Config\Services::throttler();
        $email     = $this->request->getPost('email');
        $password  = $this->request->getPost('password');
        $remember  = $this->request->getPost('remember') === 'on';

        // Rate limiting: 5 attempts per minute per IP + email
        $ip = $this->request->getIPAddress();
        $throttleKey = md5($ip . $email);
        if ($throttler->check($throttleKey, 5, 60) === false) {
            return redirect()->back()->withInput()->with('error', 'Too many login attempts. Please try again in 60 seconds.');
        }

        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $usersModel = new UsersModel();
        // Disable tenant scope since we don't have a session yet
        $user = $usersModel->disableTenantScope()->where('email', $email)->first();

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        // Validate email verification status
        if ($user['email_verified_at'] === null) {
            return redirect()->back()->withInput()->with('error', 'Please verify your email address before logging in.');
        }

        // Validate user account status
        if ($user['status'] != 1) {
            return redirect()->back()->withInput()->with('error', 'Your account has been deactivated.');
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        // Initialize User Session
        $session = session();
        $session->set([
            'user_id'    => $user['id'],
            'clinic_id'  => $user['clinic_id'],
            'user_name'  => $user['name'],
            'user_role'  => $user['role'],
            'logged_in'  => true,
        ]);

        // Get Clinic details and save clinic_name in session
        $clinicsModel = new ClinicsModel();
        $clinic = $clinicsModel->find($user['clinic_id']);
        if ($clinic) {
            $session->set('clinic_name', $clinic['name']);
        }

        // Handle Remember Me Cookie
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $usersModel->disableTenantScope()->update($user['id'], ['remember_token' => $token]);
            
            helper('cookie');
            set_cookie([
                'name'     => 'remember_me',
                'value'    => $token,
                'expire'   => 30 * 86400, // 30 Days
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        return redirect()->to('/dashboard');
    }

    public function logout()
    {
        $session = session();
        $userId  = $session->get('user_id');

        if ($userId) {
            $usersModel = new UsersModel();
            // Invalidate the remember me token in the DB
            $usersModel->disableTenantScope()->update($userId, ['remember_token' => null]);
        }

        // Clear remember me cookie
        helper('cookie');
        delete_cookie('remember_me');

        // Destroy session
        $session->destroy();

        return redirect()->to('/login')->with('success', 'You have been successfully logged out.');
    }
}
