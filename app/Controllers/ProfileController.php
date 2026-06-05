<?php

namespace App\Controllers;

use App\Models\ClinicsModel;

class ProfileController extends BaseController
{
    public function index()
    {
        $clinicModel = new ClinicsModel();
        $clinicId = session()->get('clinic_id');
        $clinic = $clinicModel->find($clinicId);

        if (!$clinic) {
            return redirect()->to('/dashboard')->with('error', 'Clinic profile not found.');
        }

        return view('profile/index', ['clinic' => $clinic]);
    }

    public function update()
    {
        $clinicModel = new ClinicsModel();
        $clinicId = session()->get('clinic_id');
        $clinic = $clinicModel->find($clinicId);

        if (!$clinic) {
            return redirect()->to('/dashboard')->with('error', 'Clinic profile not found.');
        }

        // Validate clinic profile details with clear, user-friendly labels and messages
        $rules = [
            'name' => [
                'label'  => 'Clinic Name',
                'rules'  => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required'   => 'The Clinic Name field is required.',
                    'min_length' => 'The Clinic Name must be at least 3 characters long.',
                    'max_length' => 'The Clinic Name cannot exceed 255 characters.',
                ]
            ],
            'phone' => [
                'label'  => 'Contact Phone',
                'rules'  => 'required|min_length[5]|max_length[50]',
                'errors' => [
                    'required'   => 'The Contact Phone field is required.',
                    'min_length' => 'The Contact Phone must be at least 5 characters long.',
                    'max_length' => 'The Contact Phone cannot exceed 50 characters.',
                ]
            ],
            'email' => [
                'label'  => 'Contact Email',
                'rules'  => 'required|valid_email|max_length[255]',
                'errors' => [
                    'required'    => 'The Contact Email field is required.',
                    'valid_email' => 'Please provide a valid email address.',
                    'max_length'   => 'The Contact Email cannot exceed 255 characters.',
                ]
            ],
            'slug' => [
                'label'  => 'Public Page Slug',
                'rules'  => "required|alpha_dash|max_length[255]|is_unique[clinics.slug,id,{$clinicId}]",
                'errors' => [
                    'required'   => 'The Public Page Slug field is required.',
                    'alpha_dash' => 'The Public Page Slug may only contain alphanumeric characters, dashes, and underscores.',
                    'is_unique'  => 'This Public Page Slug is already taken by another clinic.',
                    'max_length' => 'The Public Page Slug cannot exceed 255 characters.',
                ]
            ],
            'address' => [
                'label'  => 'Street Address',
                'rules'  => 'permit_empty|max_length[255]',
                'errors' => [
                    'max_length' => 'The Street Address cannot exceed 255 characters.',
                ]
            ],
            'city' => [
                'label'  => 'City',
                'rules'  => 'permit_empty|max_length[100]',
                'errors' => [
                    'max_length' => 'The City cannot exceed 100 characters.',
                ]
            ],
            'province' => [
                'label'  => 'Province / Region',
                'rules'  => 'permit_empty|max_length[100]',
                'errors' => [
                    'max_length' => 'The Province / Region cannot exceed 100 characters.',
                ]
            ],
            'description' => [
                'label'  => 'Description',
                'rules'  => 'permit_empty|max_length[1000]',
                'errors' => [
                    'max_length' => 'The Description cannot exceed 1000 characters.',
                ]
            ],
            'public_visibility' => [
                'label'  => 'Public Visibility',
                'rules'  => 'permit_empty|in_list[0,1]',
            ],
            'latitude' => [
                'label'  => 'Latitude',
                'rules'  => 'permit_empty|numeric',
                'errors' => [
                    'numeric' => 'The Latitude must contain only numbers (decimals or integers).',
                ]
            ],
            'longitude' => [
                'label'  => 'Longitude',
                'rules'  => 'permit_empty|numeric',
                'errors' => [
                    'numeric' => 'The Longitude must contain only numbers (decimals or integers).',
                ]
            ],
        ];

        // Conditionally validate uploaded files to avoid validation blocks when empty
        $logo = $this->request->getFile('logo');
        $hasNewLogo = $logo && $logo->isValid() && !$logo->hasMoved();
        if ($hasNewLogo) {
            $rules['logo'] = [
                'label'  => 'Clinic Logo',
                'rules'  => 'is_image[logo]|max_size[logo,2048]|mime_in[logo,image/png,image/jpg,image/jpeg,image/webp,image/gif]',
                'errors' => [
                    'is_image' => 'The uploaded Clinic Logo is not a valid image file.',
                    'max_size' => 'The Clinic Logo size cannot exceed 2MB.',
                    'mime_in'  => 'The Clinic Logo must be a PNG, JPG, JPEG, WEBP, or GIF image.',
                ]
            ];
        }

        $banner = $this->request->getFile('banner');
        $hasNewBanner = $banner && $banner->isValid() && !$banner->hasMoved();
        if ($hasNewBanner) {
            $rules['banner'] = [
                'label'  => 'Listing Banner Image',
                'rules'  => 'is_image[banner]|max_size[banner,4096]|mime_in[banner,image/png,image/jpg,image/jpeg,image/webp,image/gif]',
                'errors' => [
                    'is_image' => 'The uploaded Listing Banner is not a valid image file.',
                    'max_size' => 'The Listing Banner size cannot exceed 4MB.',
                    'mime_in'  => 'The Listing Banner must be a PNG, JPG, JPEG, WEBP, or GIF image.',
                ]
            ];
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $clinicData = [
            'name'              => $this->request->getPost('name'),
            'phone'             => $this->request->getPost('phone'),
            'email'             => $this->request->getPost('email'),
            'slug'              => $this->request->getPost('slug'),
            'address'           => $this->request->getPost('address'),
            'city'              => $this->request->getPost('city'),
            'province'          => $this->request->getPost('province'),
            'description'       => $this->request->getPost('description'),
            'public_visibility' => $this->request->getPost('public_visibility') === '1' ? 1 : 0,
            'latitude'          => $this->request->getPost('latitude') !== '' ? $this->request->getPost('latitude') : null,
            'longitude'         => $this->request->getPost('longitude') !== '' ? $this->request->getPost('longitude') : null,
        ];

        // Ensure directories exist
        if (!is_dir(FCPATH . 'uploads/logos')) {
            mkdir(FCPATH . 'uploads/logos', 0777, true);
        }
        if (!is_dir(FCPATH . 'uploads/banners')) {
            mkdir(FCPATH . 'uploads/banners', 0777, true);
        }

        // Handle logo upload
        if ($hasNewLogo) {
            // Delete old logo file if it exists and is local
            if (!empty($clinic['logo']) && file_exists(FCPATH . $clinic['logo'])) {
                @unlink(FCPATH . $clinic['logo']);
            }
            
            $logoName = $logo->getRandomName();
            $logo->move(FCPATH . 'uploads/logos', $logoName);
            $clinicData['logo'] = 'uploads/logos/' . $logoName;
        }

        // Handle banner upload
        if ($hasNewBanner) {
            // Delete old banner file if it exists and is local
            if (!empty($clinic['banner']) && file_exists(FCPATH . $clinic['banner'])) {
                @unlink(FCPATH . $clinic['banner']);
            }

            $bannerName = $banner->getRandomName();
            $banner->move(FCPATH . 'uploads/banners', $bannerName);
            $clinicData['banner'] = 'uploads/banners/' . $bannerName;
        }

        // Save
        $clinicModel->update($clinicId, $clinicData);

        // Update session name for instant navigation feedback
        session()->set('clinic_name', $clinicData['name']);

        return redirect()->to('/profile')->with('success', 'Clinic profile updated successfully.');
    }
}
