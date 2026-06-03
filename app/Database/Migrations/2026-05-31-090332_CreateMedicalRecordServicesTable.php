<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMedicalRecordServicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'medical_record_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
            ],
            'service_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
            ],
            'quantity' => [
                'type'       => 'INT',
                'default'    => 1,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('medical_record_id', 'medical_records', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('medical_record_services');
    }

    public function down()
    {
        $this->forge->dropTable('medical_record_services');
    }
}
