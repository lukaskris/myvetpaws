<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'clinic_id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'category' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
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
            'stock' => [
                'type'       => 'INT',
                'default'    => 0,
            ],
            'min_stock' => [
                'type'       => 'INT',
                'default'    => 0,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => '1',
                'default'    => 1, // 0 = Inactive, 1 = Active
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        // Scoped unique index: item code must be unique within the same clinic
        $this->forge->addUniqueKey(['clinic_id', 'code']);
        $this->forge->addForeignKey('clinic_id', 'clinics', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('items');
    }

    public function down()
    {
        $this->forge->dropTable('items');
    }
}
