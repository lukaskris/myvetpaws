<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCoordinatesToClinics extends Migration
{
    public function up()
    {
        $fields = [
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,8',
                'null'       => true,
                'after'      => 'description',
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => '11,8',
                'null'       => true,
                'after'      => 'latitude',
            ],
        ];
        $this->forge->addColumn('clinics', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('clinics', ['latitude', 'longitude']);
    }
}
