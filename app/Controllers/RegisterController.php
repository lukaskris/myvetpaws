<?php

namespace App\Controllers;

use App\Models\ClinicsModel;
use App\Models\UsersModel;

class RegisterController extends BaseController
{
    public function index()
    {
        // Redirect to dashboard if already logged in
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }
        return view('auth/register');
    }

    public function store()
    {
        $rules = [
            'clinic_name' => 'required|min_length[3]|max_length[255]',
            'owner_name'  => 'required|min_length[3]|max_length[255]',
            'email'       => 'required|valid_email|is_unique[users.email]',
            'phone'       => 'required|min_length[6]|max_length[50]',
            'password'    => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $clinicModel = new ClinicsModel();
        $userModel = new UsersModel();

        // Start database transaction
        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Create Clinic Entity
        $clinicData = [
            'name'   => $this->request->getPost('clinic_name'),
            'phone'  => $this->request->getPost('phone'),
            'email'  => $this->request->getPost('email'),
            'status' => 1, // Trial
            'slug'   => url_title($this->request->getPost('clinic_name'), '-', true) . '-' . rand(1000, 9999),
        ];
        $clinicModel->insert($clinicData);
        $clinicId = $clinicModel->getInsertID();

        // 2. Create Owner User
        $verificationToken = bin2hex(random_bytes(16));
        $userData = [
            'clinic_id'          => $clinicId,
            'name'               => $this->request->getPost('owner_name'),
            'email'              => $this->request->getPost('email'),
            'password'           => $this->request->getPost('password'), // Will be hashed automatically by UsersModel hook
            'phone'              => $this->request->getPost('phone'),
            'role'               => 'owner',
            'status'             => 1, // Active, but requires verification to log in (or verified via simulated link)
            'verification_token' => $verificationToken,
        ];
        
        // We temporarily disable tenant scope since the owner doesn't have an active session yet
        $userModel->disableTenantScope()->insert($userData);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Registration failed. Please try again.');
        }

        // Pass the token and email in flashdata for the mock email verification display
        session()->setFlashdata('registered_email', $userData['email']);
        session()->setFlashdata('verification_token', $verificationToken);

        return redirect()->to('/register/verify-notice');
    }

    public function verifyNotice()
    {
        $email = session()->getFlashdata('registered_email') ?? $this->request->getGet('email');
        $token = session()->getFlashdata('verification_token') ?? $this->request->getGet('token');

        if (!$email) {
            return redirect()->to('/login');
        }

        return view('auth/verify_notice', [
            'email' => $email,
            'token' => $token
        ]);
    }

    public function verify()
    {
        $token = $this->request->getGet('token');
        if (!$token) {
            return redirect()->to('/login')->with('error', 'Invalid verification link.');
        }

        $userModel = new UsersModel();
        // Disable tenant scope since user is not logged in yet
        $user = $userModel->disableTenantScope()->where('verification_token', $token)->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', 'Invalid or expired verification token.');
        }

        // Set email_verified_at and clear token
        $userModel->disableTenantScope()->update($user['id'], [
            'email_verified_at'  => date('Y-m-d H:i:s'),
            'verification_token' => null,
        ]);

        return redirect()->to('/login')->with('success', 'Email verified successfully! You can now log in.');
    }
}
