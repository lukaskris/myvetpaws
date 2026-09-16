<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCategoryDiscountsToInvoices extends Migration
{
    public function up()
    {
        $this->forge->addColumn('invoices', [
            'discount_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'rupiah',
                'null'       => false,
                'after'      => 'discount',
            ],
            'service_discount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'null'       => false,
                'after'      => 'discount_type',
            ],
            'service_discount_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'rupiah',
                'null'       => false,
                'after'      => 'service_discount',
            ],
            'item_discount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'null'       => false,
                'after'      => 'service_discount_type',
            ],
            'item_discount_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'default'    => 'rupiah',
                'null'       => false,
                'after'      => 'item_discount',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('invoices', [
            'discount_type',
            'service_discount',
            'service_discount_type',
            'item_discount',
            'item_discount_type',
        ]);
    }
}
