<?php

namespace App\Controllers;

use App\Models\MedicalRecordsModel;
use App\Models\VisitsModel;

class MedicalRecordController extends BaseController
{
    public function index()
    {
        $recordsModel = new MedicalRecordsModel();
        
        $recordsModel->select('medical_records.*, pets.name as pet_name, pets.species as pet_species, pets.breed as pet_breed, customers.name as customer_name, users.name as doctor_name, visits.checkin_time, visits.weight, visits.temperature')
                     ->join('pets', 'pets.id = medical_records.pet_id', 'inner')
                     ->join('customers', 'customers.id = pets.customer_id', 'inner')
                     ->join('visits', 'visits.id = medical_records.visit_id', 'inner')
                     ->join('users', 'users.id = medical_records.user_id', 'left');

        $search = $this->request->getGet('q');
        if (!empty($search)) {
            $recordsModel->groupStart()
                ->like('medical_records.diagnosis', $search)
                ->orLike('medical_records.treatment_plan', $search)
                ->orLike('pets.name', $search)
                ->orLike('customers.name', $search)
            ->groupEnd();
        }

        $records = $recordsModel->orderBy('medical_records.created_at', 'DESC')->findAll();

        return view('records/index', [
            'records' => $records,
            'search'  => $search,
        ]);
    }

    public function show($id)
    {
        $recordsModel = new MedicalRecordsModel();

        $record = $recordsModel->select('medical_records.*, pets.name as pet_name, pets.species as pet_species, pets.breed as pet_breed, pets.gender as pet_gender, pets.birth_date as pet_birth_date, customers.name as customer_name, customers.id as customer_id, users.name as doctor_name, visits.checkin_time, visits.weight, visits.temperature, visits.complaints')
                              ->join('pets', 'pets.id = medical_records.pet_id', 'inner')
                              ->join('customers', 'customers.id = pets.customer_id', 'inner')
                              ->join('visits', 'visits.id = medical_records.visit_id', 'inner')
                              ->join('users', 'users.id = medical_records.user_id', 'left')
                              ->find($id);

        if (!$record) {
            return redirect()->to('/records')->with('error', 'Medical record not found.');
        }

        // Fetch services rendered for this medical record
        $db = \Config\Database::connect();
        $services = $db->table('medical_record_services')
                       ->select('medical_record_services.quantity, services.name, services.code, services.price')
                       ->join('services', 'services.id = medical_record_services.service_id', 'inner')
                       ->where('medical_record_services.medical_record_id', $id)
                       ->get()
                       ->getResultArray();

        return view('records/show', [
            'record'   => $record,
            'services' => $services,
        ]);
    }
}
