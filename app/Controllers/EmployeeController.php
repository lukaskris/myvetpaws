<?php

namespace App\Controllers;

use App\Models\UsersModel;

class EmployeeController extends BaseController
{
    public function index()
    {
        $usersModel = new UsersModel();
        // Active TenantModel automatically filters users by current clinic_id
        $employees = $usersModel->findAll();

        return view('employees/index', ['employees' => $employees]);
    }

    public function create()
    {
        return view('employees/create');
    }

    public function store()
    {
        $rules = [
            'name'     => 'required|min_length[3]|max_length[255]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'phone'    => 'permit_empty|min_length[5]|max_length[50]',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[doctor,receptionist,finance]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $usersModel = new UsersModel();
        $clinicId = session()->get('clinic_id');

        $employeeData = [
            'clinic_id'         => $clinicId,
            'name'              => $this->request->getPost('name'),
            'email'             => $this->request->getPost('email'),
            'password'          => $this->request->getPost('password'), // Automatically hashed by UsersModel hook
            'phone'             => $this->request->getPost('phone') ?: null,
            'role'              => $this->request->getPost('role'),
            'status'            => 1, // Active by default
            'email_verified_at' => date('Y-m-d H:i:s'), // Auto-verified when added by admin
        ];

        $usersModel->insert($employeeData);

        return redirect()->to('/employees')->with('success', 'Employee account created successfully.');
    }

    public function edit($id)
    {
        $usersModel = new UsersModel();
        $employee = $usersModel->find($id);

        if (!$employee) {
            return redirect()->to('/employees')->with('error', 'Employee not found.');
        }

        return view('employees/edit', ['employee' => $employee]);
    }

    public function update($id)
    {
        $usersModel = new UsersModel();
        $employee = $usersModel->find($id);

        if (!$employee) {
            return redirect()->to('/employees')->with('error', 'Employee not found.');
        }

        $rules = [
            'name'     => 'required|min_length[3]|max_length[255]',
            'email'    => "required|valid_email|is_unique[users.email,id,{$id}]",
            'phone'    => 'permit_empty|min_length[5]|max_length[50]',
            'role'     => 'required|in_list[doctor,receptionist,finance,owner]',
            'password' => 'permit_empty|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $employeeData = [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone') ?: null,
            'role'  => $this->request->getPost('role'),
        ];

        // Only update password if provided
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            $employeeData['password'] = $password; // Will be hashed automatically by UsersModel hook
        }

        $usersModel->update($id, $employeeData);

        return redirect()->to('/employees')->with('success', 'Employee details updated successfully.');
    }

    public function toggleStatus($id)
    {
        $usersModel = new UsersModel();
        $employee = $usersModel->find($id);

        if (!$employee) {
            return redirect()->to('/employees')->with('error', 'Employee not found.');
        }

        // Safety: Do not deactivate your own owner account
        if ($employee['role'] === 'owner') {
            return redirect()->to('/employees')->with('error', 'You cannot deactivate the primary clinic owner account.');
        }

        $newStatus = $employee['status'] == 1 ? 0 : 1;
        $usersModel->update($id, ['status' => $newStatus]);

        $statusStr = $newStatus == 1 ? 'activated' : 'deactivated';
        return redirect()->to('/employees')->with('success', "Employee account has been successfully {$statusStr}.");
    }
}
