<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Disable foreign key checks for clean seeding
        $db->simpleQuery('SET FOREIGN_KEY_CHECKS=0');

        // 1. Clear existing data
        $db->table('payments')->truncate();
        $db->table('invoices')->truncate();
        $db->table('medical_record_services')->truncate();
        $db->table('medical_records')->truncate();
        $db->table('visits')->truncate();
        $db->table('pets')->truncate();
        $db->table('customers')->truncate();
        $db->table('services')->truncate();
        $db->table('users')->truncate();
        $db->table('clinics')->truncate();

        // 2. Insert Clinic
        $clinicData = [
            'id'         => 1,
            'name'       => 'Klinik Hewan Sehat',
            'slug'       => 'klinik-hewan-sehat',
            'email'      => 'contact@klinikhewansehat.com',
            'phone'      => '+62 812-3456-7890',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $db->table('clinics')->insert($clinicData);

        // 3. Insert User (Role: Owner, Email: admin@clinic.com, Pass: password)
        $userData = [
            'id'                => 1,
            'clinic_id'         => 1,
            'name'              => 'Dr. Hermawan, DVM',
            'email'             => 'admin@clinic.com',
            'password'          => password_hash('password', PASSWORD_BCRYPT),
            'role'              => 'owner',
            'status'            => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];
        $db->table('users')->insert($userData);

        // 4. Insert Services
        $services = [
            [
                'id'         => 1,
                'clinic_id'  => 1,
                'name'       => 'Konsultasi & Pemeriksaan Umum',
                'code'       => 'KONSUL',
                'price'      => 150000.00,
                'status'     => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'         => 2,
                'clinic_id'  => 1,
                'name'       => 'Pembersihan Telinga & Grooming',
                'code'       => 'GROOM',
                'price'      => 25000.00,
                'status'     => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id'         => 3,
                'clinic_id'  => 1,
                'name'       => 'Vaksinasi Rabies Tahunan',
                'code'       => 'VAKSIN',
                'price'      => 250000.00,
                'status'     => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $db->table('services')->insertBatch($services);

        // 5. Insert Customer (Budi Santoso)
        $customerData = [
            'id'         => 1,
            'clinic_id'  => 1,
            'name'       => 'Budi Santoso',
            'email'      => 'budi.santoso@email.com',
            'phone'      => '+62 811-2222-3333',
            'address'    => 'Jl. Kemang Raya No. 12, Jakarta Selatan',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $db->table('customers')->insert($customerData);

        // 6. Insert 2 Pets for Customer Budi Santoso (Luna & Rocky)
        $pets = [
            [
                'id'          => 1,
                'clinic_id'   => 1,
                'customer_id' => 1,
                'name'        => 'Luna',
                'species'     => 'Cat',
                'breed'       => 'Domestic Short Hair',
                'gender'      => 'Female',
                'birth_date'  => '2024-05-10',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'id'          => 2,
                'clinic_id'   => 1,
                'customer_id' => 1,
                'name'        => 'Rocky',
                'species'     => 'Dog',
                'breed'       => 'Golden Retriever',
                'gender'      => 'Male',
                'birth_date'  => '2023-11-22',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]
        ];
        $db->table('pets')->insertBatch($pets);

        // ----------------------------------------------------
        // CASE 1: Luna (Cat) - Needs General Consult (150.000) & Grooming (25.000)
        // ----------------------------------------------------

        // Insert Visit 1
        $visit1 = [
            'id'           => 1,
            'clinic_id'    => 1,
            'customer_id'  => 1,
            'pet_id'       => 1,
            'user_id'      => 1,
            'checkin_time' => date('Y-m-d H:i:s', strtotime('-1 hour')),
            'status'       => 3, // Completed
            'complaints'   => 'Luna lemas dan telinga kotor',
            'weight'       => 3.5,
            'temperature'  => 38.5,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        $db->table('visits')->insert($visit1);

        // Insert Medical Record 1
        $record1 = [
            'id'             => 1,
            'clinic_id'      => 1,
            'visit_id'       => 1,
            'pet_id'         => 1,
            'user_id'        => 1,
            'diagnosis'      => 'Otitis Externa (Infeksi Telinga Luar) & Dehidrasi Ringan',
            'treatment_plan' => 'Pembersihan telinga dan terapi cairan infus subkutan',
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];
        $db->table('medical_records')->insert($record1);

        // Insert Medical Record Services 1
        $record1Services = [
            [
                'medical_record_id' => 1,
                'service_id'        => 1, // KONSUL - 150.000
                'quantity'          => 1,
            ],
            [
                'medical_record_id' => 1,
                'service_id'        => 2, // GROOM - 25.000
                'quantity'          => 1,
            ]
        ];
        $db->table('medical_record_services')->insertBatch($record1Services);

        // Generate Invoice 1 (Total: 175.000)
        $invoice1 = [
            'id'                => 1,
            'clinic_id'         => 1,
            'customer_id'       => 1,
            'medical_record_id' => 1,
            'invoice_number'    => 'INV-' . date('Ymd') . '-00001',
            'total_amount'      => 175000.00,
            'status'            => 1, // Unpaid
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];
        $db->table('invoices')->insert($invoice1);

        // ----------------------------------------------------
        // CASE 2: Rocky (Dog) - Needs Grooming (25.000) & Vaksinasi Rabies (250.000)
        // ----------------------------------------------------

        // Insert Visit 2
        $visit2 = [
            'id'           => 2,
            'clinic_id'    => 1,
            'customer_id'  => 1,
            'pet_id'       => 2,
            'user_id'      => 1,
            'checkin_time' => date('Y-m-d H:i:s', strtotime('-45 mins')),
            'status'       => 3, // Completed
            'complaints'   => 'Rocky butuh vaksin rabies tahunan dan mandi sehat',
            'weight'       => 28.0,
            'temperature'  => 38.8,
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        $db->table('visits')->insert($visit2);

        // Insert Medical Record 2
        $record2 = [
            'id'             => 2,
            'clinic_id'      => 1,
            'visit_id'       => 2,
            'pet_id'         => 2,
            'user_id'        => 1,
            'diagnosis'      => 'Sehat secara klinis, siap divaksinasi',
            'treatment_plan' => 'Pemberian vaksin rabies tahunan dan grooming mandi sehat',
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];
        $db->table('medical_records')->insert($record2);

        // Insert Medical Record Services 2
        $record2Services = [
            [
                'medical_record_id' => 2,
                'service_id'        => 2, // GROOM - 25.000
                'quantity'          => 1,
            ],
            [
                'medical_record_id' => 2,
                'service_id'        => 3, // VAKSIN - 250.000
                'quantity'          => 1,
            ]
        ];
        $db->table('medical_record_services')->insertBatch($record2Services);

        // Generate Invoice 2 (Total: 275.000)
        $invoice2 = [
            'id'                => 2,
            'clinic_id'         => 1,
            'customer_id'       => 1,
            'medical_record_id' => 2,
            'invoice_number'    => 'INV-' . date('Ymd') . '-00002',
            'total_amount'      => 275000.00,
            'status'            => 1, // Unpaid
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];
        $db->table('invoices')->insert($invoice2);

        // Re-enable foreign key checks
        $db->simpleQuery('SET FOREIGN_KEY_CHECKS=1');
    }
}
