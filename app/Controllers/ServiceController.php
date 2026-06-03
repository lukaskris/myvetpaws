<?php

namespace App\Controllers;

use App\Models\ServicesModel;

class ServiceController extends BaseController
{
    public function index()
    {
        $servicesModel = new ServicesModel();
        // TenantModel auto-scopes queries by current clinic_id
        $services = $servicesModel->findAll();

        return view('services/index', ['services' => $services]);
    }

    public function create()
    {
        return view('services/create');
    }

    public function store()
    {
        $rules = [
            'code'        => 'required|min_length[2]|max_length[50]',
            'name'        => 'required|min_length[3]|max_length[255]',
            'category'    => 'required|max_length[100]',
            'price'       => 'required|numeric|greater_than_equal_to[0]',
            'description' => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $servicesModel = new ServicesModel();
        $code = $this->request->getPost('code');

        // Check unique code within the current clinic
        $existing = $servicesModel->where('code', $code)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', "Service code '{$code}' already exists in your clinic.");
        }

        $serviceData = [
            'code'        => strtoupper($code),
            'name'        => $this->request->getPost('name'),
            'category'    => $this->request->getPost('category'),
            'price'       => $this->request->getPost('price'),
            'description' => $this->request->getPost('description') ?: null,
            'status'      => 1, // Active by default
        ];

        // TenantModel auto-injects clinic_id during insert
        $servicesModel->insert($serviceData);

        return redirect()->to('/services')->with('success', 'Service added successfully.');
    }

    public function edit($id)
    {
        $servicesModel = new ServicesModel();
        $service = $servicesModel->find($id);

        if (!$service) {
            return redirect()->to('/services')->with('error', 'Service not found.');
        }

        return view('services/edit', ['service' => $service]);
    }

    public function update($id)
    {
        $servicesModel = new ServicesModel();
        $service = $servicesModel->find($id);

        if (!$service) {
            return redirect()->to('/services')->with('error', 'Service not found.');
        }

        $rules = [
            'code'        => 'required|min_length[2]|max_length[50]',
            'name'        => 'required|min_length[3]|max_length[255]',
            'category'    => 'required|max_length[100]',
            'price'       => 'required|numeric|greater_than_equal_to[0]',
            'description' => 'permit_empty|max_length[1000]',
            'status'      => 'required|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $code = $this->request->getPost('code');

        // Check unique code in the clinic excluding this service id
        $existing = $servicesModel->where('code', $code)->where('id !=', $id)->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('error', "Service code '{$code}' is already taken by another service in your clinic.");
        }

        $serviceData = [
            'code'        => strtoupper($code),
            'name'        => $this->request->getPost('name'),
            'category'    => $this->request->getPost('category'),
            'price'       => $this->request->getPost('price'),
            'description' => $this->request->getPost('description') ?: null,
            'status'      => (int)$this->request->getPost('status'),
        ];

        $servicesModel->update($id, $serviceData);

        return redirect()->to('/services')->with('success', 'Service updated successfully.');
    }

    public function delete($id)
    {
        $servicesModel = new ServicesModel();
        $service = $servicesModel->find($id);

        if (!$service) {
            return redirect()->to('/services')->with('error', 'Service not found.');
        }

        // We use soft delete configured in ServicesModel
        $servicesModel->delete($id);

        return redirect()->to('/services')->with('success', 'Service deleted successfully.');
    }
}
