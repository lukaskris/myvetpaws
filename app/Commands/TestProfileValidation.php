<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestProfileValidation extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'test:profilevalidation';
    protected $description = 'Runs automated testing for profile validation.';

    public function run(array $params)
    {
        CLI::write("=== Starting Profile Validation Tests ===", 'cyan');

        $clinicModel = new \App\Models\ClinicsModel();
        $clinic = $clinicModel->find(1);

        CLI::write("Current Clinic details loaded successfully. Name: {$clinic['name']}, Phone: {$clinic['phone']}, Email: {$clinic['email']}, Slug: {$clinic['slug']}", 'green');

        $validation = \Config\Services::validation();

        $rules = [
            'name'              => 'required|min_length[3]|max_length[255]',
            'phone'             => 'required|min_length[5]|max_length[50]',
            'email'             => 'required|valid_email|max_length[255]',
            'slug'              => "required|alpha_dash|max_length[255]|is_unique[clinics.slug,id,1]",
            'address'           => 'permit_empty|max_length[255]',
            'city'              => 'permit_empty|max_length[100]',
            'province'          => 'permit_empty|max_length[100]',
            'description'       => 'permit_empty|max_length[1000]',
            'public_visibility' => 'permit_empty|in_list[0,1]',
            'latitude'          => 'permit_empty|decimal',
            'longitude'         => 'permit_empty|decimal',
        ];

        $validation->setRules($rules);

        // Test case 1: Validate with existing values
        $data1 = [
            'name'              => $clinic['name'],
            'phone'             => $clinic['phone'],
            'email'             => $clinic['email'],
            'slug'              => $clinic['slug'],
            'address'           => $clinic['address'] ?? '',
            'city'              => $clinic['city'] ?? '',
            'province'          => $clinic['province'] ?? '',
            'description'       => $clinic['description'] ?? '',
            'public_visibility' => $clinic['public_visibility'],
            'latitude'          => $clinic['latitude'] ?? '',
            'longitude'         => $clinic['longitude'] ?? '',
        ];

        CLI::write("\n--- Test Case 1: Validating Existing Values ---", 'yellow');
        if ($validation->run($data1)) {
            CLI::write("SUCCESS: Existing values are valid!", 'green');
        } else {
            CLI::error("FAIL: Existing values are invalid!");
            print_r($validation->getErrors());
        }

        // Test case 2: Let's test with lat/long containing whole number
        $data2 = $data1;
        $data2['latitude'] = '-6';
        $data2['longitude'] = '106';
        CLI::write("\n--- Test Case 2: Validating Coordinates with Whole Numbers (-6, 106) ---", 'yellow');
        $validation->reset();
        $validation->setRules($rules);
        if ($validation->run($data2)) {
            CLI::write("SUCCESS: Whole number coordinates are valid!", 'green');
        } else {
            CLI::error("FAIL: Whole number coordinates are invalid!");
            print_r($validation->getErrors());
        }

        // Test case 3: Let's test with invalid slug
        $data3 = $data1;
        $data3['slug'] = 'clinic with spaces';
        CLI::write("\n--- Test Case 3: Validating Slug with Spaces ---", 'yellow');
        $validation->reset();
        $validation->setRules($rules);
        if ($validation->run($data3)) {
            CLI::write("SUCCESS: Slug with spaces is valid!", 'green');
        } else {
            CLI::error("FAIL: Slug with spaces is invalid!");
            print_r($validation->getErrors());
        }
    }
}
