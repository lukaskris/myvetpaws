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

        // Validate slug uniqueness (exclude current clinic)
        $rules = [
            'name'              => 'required|min_length[3]|max_length[255]',
            'phone'             => 'required|min_length[5]|max_length[50]',
            'email'             => 'required|valid_email|max_length[255]',
            'slug'              => "required|alpha_dash|max_length[255]|is_unique[clinics.slug,id,{$clinicId}]",
            'address'           => 'permit_empty|max_length[255]',
            'city'              => 'permit_empty|max_length[100]',
            'province'          => 'permit_empty|max_length[100]',
            'description'       => 'permit_empty|max_length[1000]',
            'public_visibility' => 'permit_empty|in_list[0,1]',
            'latitude'          => 'permit_empty|decimal',
            'longitude'         => 'permit_empty|decimal',
        ];

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
            'latitude'          => $this->request->getPost('latitude') ?: null,
            'longitude'         => $this->request->getPost('longitude') ?: null,
        ];

        // Ensure directories exist
        if (!is_dir(FCPATH . 'uploads/logos')) {
            mkdir(FCPATH . 'uploads/logos', 0777, true);
        }
        if (!is_dir(FCPATH . 'uploads/banners')) {
            mkdir(FCPATH . 'uploads/banners', 0777, true);
        }

        // Handle logo upload
        $logo = $this->request->getFile('logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            // Delete old logo file if it exists and is local
            if (!empty($clinic['logo']) && file_exists(FCPATH . $clinic['logo'])) {
                @unlink(FCPATH . $clinic['logo']);
            }
            
            $logoName = $logo->getRandomName();
            $logo->move(FCPATH . 'uploads/logos', $logoName);
            $clinicData['logo'] = 'uploads/logos/' . $logoName;
        }

        // Handle banner upload
        $banner = $this->request->getFile('banner');
        if ($banner && $banner->isValid() && !$banner->hasMoved()) {
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
