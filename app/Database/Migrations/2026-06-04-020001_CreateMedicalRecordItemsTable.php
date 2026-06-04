<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMedicalRecordItemsTable extends Migration
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
            'item_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
            ],
            'quantity' => [
                'type'       => 'INT',
                'default'    => 1,
            ],
            'buy_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'sell_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('medical_record_id', 'medical_records', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'items', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('medical_record_items');
    }

    public function down()
    {
        $this->forge->dropTable('medical_record_items');
    }
}
