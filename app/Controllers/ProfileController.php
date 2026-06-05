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
        $hasLogo = $logo && $logo->getError() !== UPLOAD_ERR_NO_FILE;

        if ($hasLogo) {
            $rules['logo'] = [
                'label'  => 'Clinic Logo',
                'rules'  => 'uploaded[logo]|is_image[logo]|max_size[logo,5120]|mime_in[logo,image/png,image/jpg,image/jpeg,image/webp,image/gif]',
                'errors' => [
                    'uploaded' => 'The Clinic Logo failed to upload. Check if the file size is within limits.',
                    'is_image' => 'The uploaded Clinic Logo is not a valid image file.',
                    'max_size' => 'The Clinic Logo size cannot exceed 5MB.',
                    'mime_in'  => 'The Clinic Logo must be a PNG, JPG, JPEG, WEBP, or GIF image.',
                ]
            ];
        }

        $banner = $this->request->getFile('banner');
        $hasBanner = $banner && $banner->getError() !== UPLOAD_ERR_NO_FILE;
        if ($hasBanner) {
            $rules['banner'] = [
                'label'  => 'Listing Banner Image',
                'rules'  => 'uploaded[banner]|is_image[banner]|max_size[banner,5120]|mime_in[banner,image/png,image/jpg,image/jpeg,image/webp,image/gif]',
                'errors' => [
                    'uploaded' => 'The Listing Banner failed to upload. Check if the file size is within limits.',
                    'is_image' => 'The uploaded Listing Banner is not a valid image file.',
                    'max_size' => 'The Listing Banner size cannot exceed 5MB.',
                    'mime_in'  => 'The Listing Banner must be a PNG, JPG, JPEG, WEBP, or GIF image.',
                ]
            ];
        }

        // Log upload status for debugging
        log_message('error', 'Profile Update Request: logo received: ' . ($logo ? 'yes' : 'no') . ', error: ' . ($logo ? $logo->getError() : 'n/a') . ', hasLogo: ' . ($hasLogo ? 'true' : 'false'));
        log_message('error', 'Profile Update Request: banner received: ' . ($banner ? 'yes' : 'no') . ', error: ' . ($banner ? $banner->getError() : 'n/a') . ', hasBanner: ' . ($hasBanner ? 'true' : 'false'));

        if (!$this->validate($rules)) {
            log_message('error', 'Profile Update Request: Validation failed: ' . json_encode($this->validator->getErrors()));
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
        if ($hasLogo && $logo->getError() === UPLOAD_ERR_OK) {
            // Delete old logo file if it exists and is local
            if (!empty($clinic['logo']) && file_exists(FCPATH . $clinic['logo'])) {
                @unlink(FCPATH . $clinic['logo']);
            }
            
            $randomName = $logo->getRandomName();
            $webpName = $this->compressAndConvertToWebp($logo->getTempName(), FCPATH . 'uploads/logos', $randomName, 80);
            
            if ($webpName !== false) {
                $clinicData['logo'] = 'uploads/logos/' . $webpName;
                log_message('error', 'Profile Update Request: Saved compressed webp logo path: ' . $clinicData['logo']);
            } else {
                // Fallback to moving the original file if GD conversion failed
                $logoName = $logo->getRandomName();
                $logo->move(FCPATH . 'uploads/logos', $logoName);
                $clinicData['logo'] = 'uploads/logos/' . $logoName;
                log_message('error', 'Profile Update Request: Saved logo path (fallback): ' . $clinicData['logo']);
            }
        }

        // Handle banner upload
        if ($hasBanner && $banner->getError() === UPLOAD_ERR_OK) {
            // Delete old banner file if it exists and is local
            if (!empty($clinic['banner']) && file_exists(FCPATH . $clinic['banner'])) {
                @unlink(FCPATH . $clinic['banner']);
            }

            $randomName = $banner->getRandomName();
            $webpName = $this->compressAndConvertToWebp($banner->getTempName(), FCPATH . 'uploads/banners', $randomName, 80);

            if ($webpName !== false) {
                $clinicData['banner'] = 'uploads/banners/' . $webpName;
                log_message('error', 'Profile Update Request: Saved compressed webp banner path: ' . $clinicData['banner']);
            } else {
                // Fallback to moving the original file if GD conversion failed
                $bannerName = $banner->getRandomName();
                $banner->move(FCPATH . 'uploads/banners', $bannerName);
                $clinicData['banner'] = 'uploads/banners/' . $bannerName;
                log_message('error', 'Profile Update Request: Saved banner path (fallback): ' . $clinicData['banner']);
            }
        }

        // Save
        log_message('error', 'Profile Update Request: Updating clinic ID ' . $clinicId . ' with data: ' . json_encode($clinicData));
        $clinicModel->update($clinicId, $clinicData);

        // Update session info for instant navigation feedback
        $updatedClinic = $clinicModel->find($clinicId);
        if ($updatedClinic) {
            session()->set('clinic_name', $updatedClinic['name']);
            session()->set('clinic_logo', $updatedClinic['logo']);
        }

        return redirect()->to('/profile')->with('success', 'Clinic profile updated successfully.');
    }

    /**
     * Helper to convert and compress uploaded images to WebP format.
     */
    private function compressAndConvertToWebp($tempPath, $targetFolder, $filename, $quality = 80)
    {
        // Get image info
        $imageInfo = @getimagesize($tempPath);
        if (!$imageInfo) {
            return false;
        }

        $mime = $imageInfo['mime'];
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = @imagecreatefromjpeg($tempPath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($tempPath);
                if ($image) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
                break;
            case 'image/gif':
                $image = @imagecreatefromgif($tempPath);
                if ($image) {
                    imagepalettetotruecolor($image);
                }
                break;
            case 'image/webp':
                $image = @imagecreatefromwebp($tempPath);
                break;
            default:
                return false;
        }

        if (!$image) {
            return false;
        }

        // Generate target filename with webp extension
        $webpFilename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        $destination = rtrim($targetFolder, '/') . '/' . $webpFilename;

        // Save image as WebP format
        $success = @imagewebp($image, $destination, $quality);
        @imagedestroy($image);

        return $success ? $webpFilename : false;
    }
}
