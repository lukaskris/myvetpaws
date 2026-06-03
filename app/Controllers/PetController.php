<?php

namespace App\Controllers;

use App\Models\CustomersModel;
use App\Models\PetsModel;
use App\Models\MedicalRecordsModel;

class PetController extends BaseController
{
    public function index()
    {
        $search = $this->request->getGet('q');
        $petsModel = new PetsModel();

        $petsModel->select('pets.*, customers.name as customer_name')
                  ->join('customers', 'customers.id = pets.customer_id', 'left');

        if (!empty($search)) {
            $petsModel->groupStart()
                ->like('pets.name', $search)
                ->orLike('pets.species', $search)
                ->orLike('pets.breed', $search)
                ->orLike('customers.name', $search)
            ->groupEnd();
        }

        $pets = $petsModel->orderBy('pets.name', 'ASC')->findAll();

        return view('pets/index', [
            'pets'   => $pets,
            'search' => $search,
        ]);
    }

    public function create($customerId)
    {
        $customersModel = new CustomersModel();
        $customer = $customersModel->find($customerId);

        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }

        return view('pets/create', ['customer' => $customer]);
    }

    public function store()
    {
        $rules = [
            'customer_id'   => 'required|numeric',
            'name'          => 'required|min_length[2]|max_length[255]',
            'species'       => 'required|max_length[100]',
            'breed'         => 'permit_empty|max_length[100]',
            'gender'        => 'permit_empty|in_list[Male,Female,Neutered Male,Spayed Female,Unknown]',
            'color'         => 'permit_empty|max_length[100]',
            'birth_date'    => 'permit_empty|valid_date[Y-m-d]',
            'vaccinated_at' => 'permit_empty|valid_date[Y-m-d]',
            'notes'         => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $customerId = $this->request->getPost('customer_id');
        $customersModel = new CustomersModel();
        $customer = $customersModel->find($customerId);

        if (!$customer) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }

        $petsModel = new PetsModel();
        
        $petData = [
            'customer_id'   => $customerId,
            'name'          => $this->request->getPost('name'),
            'species'       => $this->request->getPost('species'),
            'breed'         => $this->request->getPost('breed') ?: null,
            'gender'        => $this->request->getPost('gender') ?: 'Unknown',
            'color'         => $this->request->getPost('color') ?: null,
            'birth_date'    => $this->request->getPost('birth_date') ?: null,
            'vaccinated_at' => $this->request->getPost('vaccinated_at') ? $this->request->getPost('vaccinated_at') . ' 00:00:00' : null,
            'notes'         => $this->request->getPost('notes') ?: null,
        ];

        // Handle Photo upload
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads/pets')) {
                mkdir(FCPATH . 'uploads/pets', 0777, true);
            }

            $name = $photo->getRandomName();
            $photo->move(FCPATH . 'uploads/pets', $name);
            $petData['photo'] = 'uploads/pets/' . $name;
        }

        $petsModel->insert($petData);

        return redirect()->to('/customers/show/' . $customerId)->with('success', 'Pet profile added successfully.');
    }

    public function show($id)
    {
        $petsModel = new PetsModel();
        $pet = $petsModel->select('pets.*, customers.name as customer_name, customers.phone as customer_phone, customers.email as customer_email')
                         ->join('customers', 'customers.id = pets.customer_id', 'left')
                         ->find($id);

        if (!$pet) {
            return redirect()->to('/pets')->with('error', 'Pet profile not found.');
        }

        // Fetch medical records for this pet
        $recordsModel = new MedicalRecordsModel();
        $records = $recordsModel->select('medical_records.*, users.name as doctor_name, visits.weight, visits.temperature, visits.complaints')
                                ->join('users', 'users.id = medical_records.user_id', 'left')
                                ->join('visits', 'visits.id = medical_records.visit_id', 'inner')
                                ->where('medical_records.pet_id', $id)
                                ->orderBy('medical_records.created_at', 'DESC')
                                ->findAll();

        // Fetch services for each record in a single optimized query
        $servicesRendered = [];
        if (!empty($records)) {
            $recordIds = array_column($records, 'id');
            $db = \Config\Database::connect();
            $servicesRows = $db->table('medical_record_services')
                               ->select('medical_record_services.medical_record_id, medical_record_services.quantity, services.name, services.code')
                               ->join('services', 'services.id = medical_record_services.service_id', 'inner')
                               ->whereIn('medical_record_services.medical_record_id', $recordIds)
                               ->get()
                               ->getResultArray();

            foreach ($servicesRows as $row) {
                $servicesRendered[$row['medical_record_id']][] = $row;
            }
        }

        return view('pets/show', [
            'pet'              => $pet,
            'records'          => $records,
            'servicesRendered' => $servicesRendered,
        ]);
    }

    public function edit($id)
    {
        $petsModel = new PetsModel();
        $pet = $petsModel->find($id);

        if (!$pet) {
            return redirect()->to('/pets')->with('error', 'Pet profile not found.');
        }

        $customersModel = new CustomersModel();
        $customer = $customersModel->find($pet['customer_id']);

        return view('pets/edit', [
            'pet'      => $pet,
            'customer' => $customer,
        ]);
    }

    public function update($id)
    {
        $petsModel = new PetsModel();
        $pet = $petsModel->find($id);

        if (!$pet) {
            return redirect()->to('/pets')->with('error', 'Pet profile not found.');
        }

        $rules = [
            'name'          => 'required|min_length[2]|max_length[255]',
            'species'       => 'required|max_length[100]',
            'breed'         => 'permit_empty|max_length[100]',
            'gender'        => 'permit_empty|in_list[Male,Female,Neutered Male,Spayed Female,Unknown]',
            'color'         => 'permit_empty|max_length[100]',
            'birth_date'    => 'permit_empty|valid_date[Y-m-d]',
            'vaccinated_at' => 'permit_empty|valid_date[Y-m-d]',
            'notes'         => 'permit_empty|max_length[1000]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $petData = [
            'name'          => $this->request->getPost('name'),
            'species'       => $this->request->getPost('species'),
            'breed'         => $this->request->getPost('breed') ?: null,
            'gender'        => $this->request->getPost('gender') ?: 'Unknown',
            'color'         => $this->request->getPost('color') ?: null,
            'birth_date'    => $this->request->getPost('birth_date') ?: null,
            'vaccinated_at' => $this->request->getPost('vaccinated_at') ? date('Y-m-d H:i:s', strtotime($this->request->getPost('vaccinated_at'))) : null,
            'notes'         => $this->request->getPost('notes') ?: null,
        ];

        // Handle Photo upload
        $photo = $this->request->getFile('photo');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            if (!is_dir(FCPATH . 'uploads/pets')) {
                mkdir(FCPATH . 'uploads/pets', 0777, true);
            }

            // Delete old photo if it exists locally
            if (!empty($pet['photo']) && file_exists(FCPATH . $pet['photo'])) {
                @unlink(FCPATH . $pet['photo']);
            }

            $name = $photo->getRandomName();
            $photo->move(FCPATH . 'uploads/pets', $name);
            $petData['photo'] = 'uploads/pets/' . $name;
        }

        $petsModel->update($id, $petData);

        return redirect()->to('/pets/show/' . $id)->with('success', 'Pet profile updated successfully.');
    }

    public function delete($id)
    {
        $petsModel = new PetsModel();
        $pet = $petsModel->find($id);

        if (!$pet) {
            return redirect()->to('/pets')->with('error', 'Pet profile not found.');
        }

        $customerId = $pet['customer_id'];
        $petsModel->delete($id);

        return redirect()->to('/customers/show/' . $customerId)->with('success', 'Pet profile has been soft deleted.');
    }
}
