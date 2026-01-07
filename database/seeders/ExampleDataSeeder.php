<?php

namespace Database\Seeders;

use App\Models\Breeds;
use App\Models\Customer;
use App\Models\Diagnose;
use App\Models\DiagnoseDetail;
use App\Models\DiagnoseDetailMedicine;
use App\Models\DiagnoseService;
use App\Models\DiagnosisMaster;
use App\Models\Medicine;
use App\Models\OpnameList;
use App\Models\Pet;
use App\Models\Service;
use App\Models\Species;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $speciesCat = Species::firstOrCreate(['name' => 'Kucing']);
        $speciesDog = Species::firstOrCreate(['name' => 'Anjing']);
        $speciesRabbit = Species::firstOrCreate(['name' => 'Kelinci']);

        $breedPersian = Breeds::firstOrCreate(['name' => 'Persian']);
        $breedShihTzu = Breeds::firstOrCreate(['name' => 'Shih Tzu']);
        $breedMix = Breeds::firstOrCreate(['name' => 'Mix']);

        $customer = Customer::firstOrCreate(
            ['name' => 'Budi Santoso'],
            [
                'title' => 'Mr.',
                'phone' => '0812-3456-7890',
                'email' => 'budi@example.com',
                'address' => 'Jl. Melati No. 12, Jakarta',
            ]
        );
        $customerSari = Customer::firstOrCreate(
            ['name' => 'Sari Wulandari'],
            [
                'title' => 'Mrs.',
                'phone' => '0813-5555-2222',
                'email' => 'sari@example.com',
                'address' => 'Jl. Kenanga No. 8, Bandung',
            ]
        );
        $customerAndi = Customer::firstOrCreate(
            ['name' => 'Andi Pratama'],
            [
                'title' => 'Mr.',
                'phone' => '0812-0000-1111',
                'email' => 'andi@example.com',
                'address' => 'Jl. Mawar No. 21, Surabaya',
            ]
        );

        $petDesi = Pet::firstOrCreate(
            ['name' => 'Desi', 'customer_id' => $customer->id],
            [
                'species_id' => $speciesCat->id,
                'breed_id' => $breedPersian->id,
                'gender' => 'Female',
                'birth_date' => Carbon::now()->subYears(3)->toDateString(),
                'vaccinated_at' => Carbon::now()->subMonths(6)->toDateTimeString(),
            ]
        );

        $petMilo = Pet::firstOrCreate(
            ['name' => 'Milo', 'customer_id' => $customer->id],
            [
                'species_id' => $speciesDog->id,
                'breed_id' => $breedShihTzu->id,
                'gender' => 'Male',
                'birth_date' => Carbon::now()->subYears(2)->toDateString(),
                'vaccinated_at' => Carbon::now()->subMonths(4)->toDateTimeString(),
            ]
        );

        $petLuna = Pet::firstOrCreate(
            ['name' => 'Luna', 'customer_id' => $customerSari->id],
            [
                'species_id' => $speciesCat->id,
                'breed_id' => $breedMix->id,
                'gender' => 'Female',
                'birth_date' => Carbon::now()->subYears(4)->toDateString(),
                'vaccinated_at' => Carbon::now()->subMonths(9)->toDateTimeString(),
            ]
        );

        $petBuddy = Pet::firstOrCreate(
            ['name' => 'Buddy', 'customer_id' => $customerAndi->id],
            [
                'species_id' => $speciesDog->id,
                'breed_id' => $breedMix->id,
                'gender' => 'Male',
                'birth_date' => Carbon::now()->subYears(5)->toDateString(),
                'vaccinated_at' => Carbon::now()->subMonths(12)->toDateTimeString(),
            ]
        );

        $petSnowy = Pet::firstOrCreate(
            ['name' => 'Snowy', 'customer_id' => $customerAndi->id],
            [
                'species_id' => $speciesRabbit->id,
                'breed_id' => $breedMix->id,
                'gender' => 'Female',
                'birth_date' => Carbon::now()->subYears(1)->toDateString(),
                'vaccinated_at' => Carbon::now()->subMonths(3)->toDateTimeString(),
            ]
        );

        $medicine = Medicine::firstOrCreate(
            ['name' => 'Amoxicillin'],
            [
                'unit' => 'tablet',
                'stock' => 120,
                'price' => 15000,
                'alias' => 'Amox',
            ]
        );
        $medicineVitamin = Medicine::firstOrCreate(
            ['name' => 'Vitamin B Complex'],
            [
                'unit' => 'ml',
                'stock' => 50,
                'price' => 25000,
                'alias' => 'B-Comp',
            ]
        );
        $medicineAntiFlea = Medicine::firstOrCreate(
            ['name' => 'Anti Flea Shampoo'],
            [
                'unit' => 'bottle',
                'stock' => 20,
                'price' => 60000,
                'alias' => 'FleaClean',
            ]
        );

        $service = Service::firstOrCreate(
            ['name' => 'General Checkup'],
            [
                'price' => 50000,
                'duration' => 30,
                'duration_type' => 'minutes',
                'is_active' => 1,
            ]
        );
        $serviceGrooming = Service::firstOrCreate(
            ['name' => 'Grooming'],
            [
                'price' => 80000,
                'duration' => 60,
                'duration_type' => 'minutes',
                'is_active' => 1,
            ]
        );
        $serviceVaccine = Service::firstOrCreate(
            ['name' => 'Vaccine'],
            [
                'price' => 120000,
                'duration' => 20,
                'duration_type' => 'minutes',
                'is_active' => 1,
            ]
        );

        $diagnosisMaster = DiagnosisMaster::firstOrCreate(
            ['name' => 'Gastritis'],
            ['notes' => 'Peradangan lambung ringan.']
        );
        $diagnosisMasterFever = DiagnosisMaster::firstOrCreate(
            ['name' => 'Fever'],
            ['notes' => 'Demam ringan.']
        );
        $diagnosisMasterSkin = DiagnosisMaster::firstOrCreate(
            ['name' => 'Dermatitis'],
            ['notes' => 'Iritasi kulit.']
        );

        $appointment = OpnameList::firstOrCreate(
            ['name' => 'Appointment Desi'],
            [
                'description' => 'Pemeriksaan rutin dan keluhan mual.',
                'discount' => 0,
                'date' => Carbon::now()->toDateString(),
                'customer_id' => $customer->id,
                'medical_notes' => 'Pet terlihat lemas sejak 2 hari terakhir.',
            ]
        );

        $diagnoseDesi = Diagnose::firstOrCreate(
            [
                'opname_list_id' => $appointment->id,
                'pet_id' => $petDesi->id,
            ],
            [
                'name' => 'Gastritis',
                'type' => 'Primary',
                'prognose' => 'Fausta',
                'duration_days' => 2,
            ]
        );

        $diagnoseMilo = Diagnose::firstOrCreate(
            [
                'opname_list_id' => $appointment->id,
                'pet_id' => $petMilo->id,
            ],
            [
                'name' => 'Checkup',
                'type' => 'Primary',
                'prognose' => 'Fausta',
                'duration_days' => 1,
            ]
        );

        DiagnoseDetail::firstOrCreate(
            [
                'diagnose_id' => $diagnoseDesi->id,
                'detail_item_sections' => 'diagnose',
                'diagnosis_master_id' => $diagnosisMaster->id,
            ],
            [
                'name' => $diagnosisMaster->name,
                'type' => 'Primary',
                'prognose' => 'Fausta',
                'notes' => 'Mual dan nafsu makan menurun.',
            ]
        );

        $medicineDetail = DiagnoseDetail::firstOrCreate(
            [
                'diagnose_id' => $diagnoseDesi->id,
                'detail_item_sections' => 'medicine',
                'name' => 'Medicine Detail',
            ],
            [
                'type' => 'Primary',
                'prognose' => 'Fausta',
                'notes' => 'Obat diberikan setelah makan.',
            ]
        );

        DiagnoseDetailMedicine::firstOrCreate(
            [
                'diagnose_detail_id' => $medicineDetail->id,
                'medicine_id' => $medicine->id,
            ],
            [
                'dosage' => '1 tablet / hari',
                'notes' => '3 hari',
            ]
        );

        $serviceDetail = DiagnoseDetail::firstOrCreate(
            [
                'diagnose_id' => $diagnoseDesi->id,
                'detail_item_sections' => 'service',
                'name' => 'Service Detail',
            ],
            [
                'type' => 'Primary',
                'prognose' => 'Fausta',
                'notes' => 'Pemeriksaan fisik lengkap.',
            ]
        );

        DiagnoseService::firstOrCreate(
            [
                'diagnose_detail_id' => $serviceDetail->id,
                'service_id' => $service->id,
            ],
            [
                'notes' => 'Termasuk konsultasi dokter.',
            ]
        );

        DiagnoseDetail::firstOrCreate(
            [
                'diagnose_id' => $diagnoseMilo->id,
                'detail_item_sections' => 'diagnose',
            ],
            [
                'name' => 'Checkup',
                'type' => 'Primary',
                'prognose' => 'Fausta',
                'notes' => 'Tidak ada keluhan khusus.',
            ]
        );

        $appointmentLuna = OpnameList::firstOrCreate(
            ['name' => 'Appointment Luna'],
            [
                'description' => 'Keluhan gatal dan grooming.',
                'discount' => 10000,
                'date' => Carbon::now()->subDays(3)->toDateString(),
                'customer_id' => $customerSari->id,
                'medical_notes' => 'Kulit kering dan rontok.',
            ]
        );

        $diagnoseLuna = Diagnose::firstOrCreate(
            [
                'opname_list_id' => $appointmentLuna->id,
                'pet_id' => $petLuna->id,
            ],
            [
                'name' => 'Dermatitis',
                'type' => 'Primary',
                'prognose' => 'Fausta',
                'duration_days' => 3,
            ]
        );

        DiagnoseDetail::firstOrCreate(
            [
                'diagnose_id' => $diagnoseLuna->id,
                'detail_item_sections' => 'diagnose',
                'diagnosis_master_id' => $diagnosisMasterSkin->id,
            ],
            [
                'name' => $diagnosisMasterSkin->name,
                'type' => 'Primary',
                'prognose' => 'Fausta',
                'notes' => 'Perlu perawatan kulit.',
            ]
        );

        $detailLunaService = DiagnoseDetail::firstOrCreate(
            [
                'diagnose_id' => $diagnoseLuna->id,
                'detail_item_sections' => 'service',
                'name' => 'Service Detail',
            ],
            [
                'type' => 'Primary',
                'prognose' => 'Fausta',
                'notes' => 'Grooming rutin.',
            ]
        );

        DiagnoseService::firstOrCreate(
            [
                'diagnose_detail_id' => $detailLunaService->id,
                'service_id' => $serviceGrooming->id,
            ],
            [
                'notes' => 'Shampoo anti kutu.',
            ]
        );

        $appointmentBuddy = OpnameList::firstOrCreate(
            ['name' => 'Appointment Buddy'],
            [
                'description' => 'Demam dan vaksin.',
                'discount' => 0,
                'date' => Carbon::now()->subDays(10)->toDateString(),
                'customer_id' => $customerAndi->id,
                'medical_notes' => 'Suhu tubuh meningkat.',
            ]
        );

        $diagnoseBuddy = Diagnose::firstOrCreate(
            [
                'opname_list_id' => $appointmentBuddy->id,
                'pet_id' => $petBuddy->id,
            ],
            [
                'name' => 'Fever',
                'type' => 'Primary',
                'prognose' => 'Dubius',
                'duration_days' => 2,
            ]
        );

        DiagnoseDetail::firstOrCreate(
            [
                'diagnose_id' => $diagnoseBuddy->id,
                'detail_item_sections' => 'diagnose',
                'diagnosis_master_id' => $diagnosisMasterFever->id,
            ],
            [
                'name' => $diagnosisMasterFever->name,
                'type' => 'Primary',
                'prognose' => 'Dubius',
                'notes' => 'Pantau suhu setiap hari.',
            ]
        );

        $detailBuddyMedicine = DiagnoseDetail::firstOrCreate(
            [
                'diagnose_id' => $diagnoseBuddy->id,
                'detail_item_sections' => 'medicine',
                'name' => 'Medicine Detail',
            ],
            [
                'type' => 'Primary',
                'prognose' => 'Dubius',
                'notes' => 'Vitamin untuk membantu pemulihan.',
            ]
        );

        DiagnoseDetailMedicine::firstOrCreate(
            [
                'diagnose_detail_id' => $detailBuddyMedicine->id,
                'medicine_id' => $medicineVitamin->id,
            ],
            [
                'dosage' => '2 ml / hari',
                'notes' => '5 hari',
            ]
        );

        $detailBuddyService = DiagnoseDetail::firstOrCreate(
            [
                'diagnose_id' => $diagnoseBuddy->id,
                'detail_item_sections' => 'service',
                'name' => 'Service Detail',
            ],
            [
                'type' => 'Primary',
                'prognose' => 'Dubius',
                'notes' => 'Vaksinasi ulang.',
            ]
        );

        DiagnoseService::firstOrCreate(
            [
                'diagnose_detail_id' => $detailBuddyService->id,
                'service_id' => $serviceVaccine->id,
            ],
            [
                'notes' => 'Dosis lengkap.',
            ]
        );

        $appointmentSnowy = OpnameList::firstOrCreate(
            ['name' => 'Appointment Snowy'],
            [
                'description' => 'Checkup kelinci.',
                'discount' => 5000,
                'date' => Carbon::now()->subDays(1)->toDateString(),
                'customer_id' => $customerAndi->id,
                'medical_notes' => 'Nafsu makan menurun.',
            ]
        );

        $diagnoseSnowy = Diagnose::firstOrCreate(
            [
                'opname_list_id' => $appointmentSnowy->id,
                'pet_id' => $petSnowy->id,
            ],
            [
                'name' => 'Checkup',
                'type' => 'Primary',
                'prognose' => 'Fausta',
                'duration_days' => 1,
            ]
        );

        $detailSnowyMedicine = DiagnoseDetail::firstOrCreate(
            [
                'diagnose_id' => $diagnoseSnowy->id,
                'detail_item_sections' => 'medicine',
                'name' => 'Medicine Detail',
            ],
            [
                'type' => 'Primary',
                'prognose' => 'Fausta',
                'notes' => 'Obat nafsu makan.',
            ]
        );

        DiagnoseDetailMedicine::firstOrCreate(
            [
                'diagnose_detail_id' => $detailSnowyMedicine->id,
                'medicine_id' => $medicineAntiFlea->id,
            ],
            [
                'dosage' => 'Sesuai kebutuhan',
                'notes' => 'Untuk kebersihan.',
            ]
        );
    }
}
