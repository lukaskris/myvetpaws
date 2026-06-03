<?php

namespace App\Controllers;

use App\Models\CustomersModel;
use App\Models\PetsModel;

class CustomerController extends BaseController
{
    public function index()
    {
        $search = $this->request->getGet('q');
        $customersModel = new CustomersModel();

        if (!empty($search)) {
            $customersModel->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->orLike('phone', $search)
                ->orLike('address', $search)
            ->groupEnd();
        }

        $customers = $customersModel->orderBy('name', 'ASC')->findAll();

        return view('customers/index', [
            'customers' => $customers,
            'search'    => $search,
        ]);
    }

    public function create()
    {
        return view('customers/create');
    }

    public function store()
    {
        $rules = [
            'name'    => 'required|min_length[3]|max_length[255]',
            'email'   => 'permit_empty|valid_email|max_length[255]',
            'phone'   => 'permit_empty|min_length[5]|max_length[50]',
            'address' => 'permit_empty|max_length[255]',
            'title'   => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $customersModel = new CustomersModel();
        
        $customerData = [
            'title'   => $this->request->getPost('title') ?: null,
            'name'    => $this->request->getPost('name'),
            'email'   => $this->request->getPost('email') ?: null,
            'phone'   => $this->request->getPost('phone') ?: null,
            'address' => $this->request->getPost('address') ?: null,
        ];

        // Handle Profile Picture upload
        $profilePic = $this->request->getFile('profile_picture');
        if ($profilePic && $profilePic->isValid() && !$profilePic->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads/customers')) {
                mkdir(FCPATH . 'uploads/customers', 0777, true);
            }

            $name = $profilePic->getRandomName();
            $profilePic->move(FCPATH . 'uploads/customers', $name);
            $customerData['profile_picture'] = 'uploads/customers/' . $name;
        }

        $customersModel->insert($customerData);

        return redirect()->to('/customers')->with('success', 'Customer registered successfully.');
    }

    public function show($id)
    {
        $customersModel = new CustomersModel();
        $customer = $customersModel->find($id);

        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }

        $petsModel = new PetsModel();
        $pets = $petsModel->where('customer_id', $id)->orderBy('name', 'ASC')->findAll();

        return view('customers/show', [
            'customer' => $customer,
            'pets'     => $pets,
        ]);
    }

    public function edit($id)
    {
        $customersModel = new CustomersModel();
        $customer = $customersModel->find($id);

        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }

        return view('customers/edit', ['customer' => $customer]);
    }

    public function update($id)
    {
        $customersModel = new CustomersModel();
        $customer = $customersModel->find($id);

        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }

        $rules = [
            'name'    => 'required|min_length[3]|max_length[255]',
            'email'   => 'permit_empty|valid_email|max_length[255]',
            'phone'   => 'permit_empty|min_length[5]|max_length[50]',
            'address' => 'permit_empty|max_length[255]',
            'title'   => 'permit_empty|max_length[50]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $customerData = [
            'title'   => $this->request->getPost('title') ?: null,
            'name'    => $this->request->getPost('name'),
            'email'   => $this->request->getPost('email') ?: null,
            'phone'   => $this->request->getPost('phone') ?: null,
            'address' => $this->request->getPost('address') ?: null,
        ];

        // Handle Profile Picture upload
        $profilePic = $this->request->getFile('profile_picture');
        if ($profilePic && $profilePic->isValid() && !$profilePic->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads/customers')) {
                mkdir(FCPATH . 'uploads/customers', 0777, true);
            }

            // Delete old picture if it exists locally
            if (!empty($customer['profile_picture']) && file_exists(FCPATH . $customer['profile_picture'])) {
                @unlink(FCPATH . $customer['profile_picture']);
            }

            $name = $profilePic->getRandomName();
            $profilePic->move(FCPATH . 'uploads/customers', $name);
            $customerData['profile_picture'] = 'uploads/customers/' . $name;
        }

        $customersModel->update($id, $customerData);

        return redirect()->to('/customers/show/' . $id)->with('success', 'Customer updated successfully.');
    }

    public function delete($id)
    {
        $customersModel = new CustomersModel();
        $customer = $customersModel->find($id);

        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }

        $customersModel->delete($id);

        return redirect()->to('/customers')->with('success', 'Customer profile has been soft deleted.');
    }
}
