<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAppointmentTypeToVisits extends Migration
{
    public function up()
    {
        $this->forge->addColumn('visits', [
            'appointment_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '30',
                'default'    => 'vet_checkup', // vet_checkup, home_visit, grooming
                'null'       => false,
                'after'      => 'checkin_time',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('visits', 'appointment_type');
    }
}
