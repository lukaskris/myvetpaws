<?php

namespace CodeIgniter\HTTP\Files {
    function is_uploaded_file(string $filename): bool
    {
        return true;
    }

    function move_uploaded_file(string $filename, string $destination): bool
    {
        return copy($filename, $destination);
    }
}

namespace {
    use CodeIgniter\Test\CIUnitTestCase;
    use CodeIgniter\Test\FeatureTestTrait;
    use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class ProfileControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use DatabaseTestTrait;

    protected $migrate   = true;
    protected $namespace = 'App';
    protected $seed      = 'App\Database\Seeds\SampleDataSeeder';

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function testProfileUpdateSuccess(): void
    {
        // Setup session data for the logged-in owner user
        $sessionData = [
            'user_id'     => 1,
            'clinic_id'   => 1,
            'user_name'   => 'Dr. Hermawan, DVM',
            'user_role'   => 'owner',
            'logged_in'   => true,
            'clinic_name' => 'Klinik Hewan Sehat'
        ];

        // Perform mock POST request to the /profile endpoint
        $result = $this->withSession($sessionData)
                       ->post('profile', [
                           'name'              => 'Updated Clinic Name',
                           'phone'             => '+62 812-9999-8888',
                           'email'             => 'updated@clinic.com',
                           'slug'              => 'updated-clinic-slug',
                           'address'           => 'Updated Address 123',
                           'city'              => 'Jakarta',
                           'province'          => 'DKI Jakarta',
                           'description'       => 'Best care for your pet.',
                           'public_visibility' => '1',
                           'latitude'          => '-6', // whole number coordinate
                           'longitude'         => '106.8166' // decimal coordinate
                       ]);

        // Assert redirect to profile with success
        $result->assertStatus(302);
        $result->assertRedirectTo(base_url('profile'));

        // Verify the database record was updated correctly
        $db = \Config\Database::connect();
        $updatedClinic = $db->table('clinics')->where('id', 1)->get()->getRowArray();

        $this->assertEquals('Updated Clinic Name', $updatedClinic['name']);
        $this->assertEquals('+62 812-9999-8888', $updatedClinic['phone']);
        $this->assertEquals('updated@clinic.com', $updatedClinic['email']);
        $this->assertEquals('updated-clinic-slug', $updatedClinic['slug']);
        $this->assertEquals('Updated Address 123', $updatedClinic['address']);
        $this->assertEquals('Jakarta', $updatedClinic['city']);
        $this->assertEquals('DKI Jakarta', $updatedClinic['province']);
        $this->assertEquals('Best care for your pet.', $updatedClinic['description']);
        $this->assertEquals(1, $updatedClinic['public_visibility']);
        $this->assertEquals(-6.0, (float)$updatedClinic['latitude']);
        $this->assertEquals(106.8166, (float)$updatedClinic['longitude']);
    }

    public function testProfileUpdateValidationError(): void
    {
        $sessionData = [
            'user_id'     => 1,
            'clinic_id'   => 1,
            'user_name'   => 'Dr. Hermawan, DVM',
            'user_role'   => 'owner',
            'logged_in'   => true,
            'clinic_name' => 'Klinik Hewan Sehat'
        ];

        // Send request with validation errors (e.g. invalid email, too short phone, empty name)
        $result = $this->withSession($sessionData)
                       ->post('profile', [
                           'name'              => '', // empty name (fails required)
                           'phone'             => '12', // too short (fails min_length)
                           'email'             => 'invalid-email', // invalid email
                           'slug'              => 'invalid slug with spaces', // invalid slug
                           'latitude'          => 'not-a-number', // invalid latitude
                           'longitude'         => '106.8166'
                       ]);

        // Assert redirect back (which returns 302 to the previous URL or back)
        $result->assertStatus(302);

        // Retrieve errors from session flashdata
        $errors = session()->getFlashdata('errors');
        $this->assertNotNull($errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('phone', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('slug', $errors);
        $this->assertArrayHasKey('latitude', $errors);
    }

    public function testProfileUpdateWithFiles(): void
    {
        $sessionData = [
            'user_id'     => 1,
            'clinic_id'   => 1,
            'user_name'   => 'Dr. Hermawan, DVM',
            'user_role'   => 'owner',
            'logged_in'   => true,
            'clinic_name' => 'Klinik Hewan Sehat'
        ];

        // Create a dummy image file for testing
        $dummyLogoPath = WRITEPATH . 'test_logo.png';
        $im = imagecreatetruecolor(10, 10);
        imagepng($im, $dummyLogoPath);
        imagedestroy($im);

        $dummyBannerPath = WRITEPATH . 'test_banner.png';
        $im2 = imagecreatetruecolor(10, 10);
        imagepng($im2, $dummyBannerPath);
        imagedestroy($im2);

        // Mock $_FILES
        $_FILES['logo'] = [
            'name'     => 'test_logo.png',
            'type'     => 'image/png',
            'tmp_name' => $dummyLogoPath,
            'error'    => UPLOAD_ERR_OK,
            'size'     => filesize($dummyLogoPath),
        ];

        $_FILES['banner'] = [
            'name'     => 'test_banner.png',
            'type'     => 'image/png',
            'tmp_name' => $dummyBannerPath,
            'error'    => UPLOAD_ERR_OK,
            'size'     => filesize($dummyBannerPath),
        ];

        // Sync with CodeIgniter Superglobals service
        service('superglobals')->setFilesArray($_FILES);

        // Perform request
        $result = $this->withSession($sessionData)
                       ->post('profile', [
                           'name'              => 'Updated Clinic Name',
                           'phone'             => '+62 812-9999-8888',
                           'email'             => 'updated@clinic.com',
                           'slug'              => 'updated-clinic-slug',
                           'address'           => 'Updated Address 123',
                           'city'              => 'Jakarta',
                           'province'          => 'DKI Jakarta',
                           'description'       => 'Best care for your pet.',
                           'public_visibility' => '1',
                           'latitude'          => '-6',
                           'longitude'         => '106.8166'
                       ]);

        // Clean up dummy files
        @unlink($dummyLogoPath);
        @unlink($dummyBannerPath);

        // Assert redirect to profile with success
        $result->assertStatus(302);
        
        $errors = session()->getFlashdata('errors');
        if ($errors) {
            print_r($errors);
        }
        $this->assertNull($errors);
        
        $result->assertRedirectTo(base_url('profile'));

        // Check if database was updated with the files
        $db = \Config\Database::connect();
        $updatedClinic = $db->table('clinics')->where('id', 1)->get()->getRowArray();
        $this->assertNotEmpty($updatedClinic['logo']);
        $this->assertNotEmpty($updatedClinic['banner']);

        // Check if the uploaded files exist in the public directory
        $this->assertTrue(file_exists(FCPATH . $updatedClinic['logo']));
        $this->assertTrue(file_exists(FCPATH . $updatedClinic['banner']));

        // Clean up uploaded files
        @unlink(FCPATH . $updatedClinic['logo']);
        @unlink(FCPATH . $updatedClinic['banner']);
    }
}
}

