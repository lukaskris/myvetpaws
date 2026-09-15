<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDiscountToInvoices extends Migration
{
    public function up()
    {
        $this->forge->addColumn('invoices', [
            'discount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
                'null'       => false,
                'after'      => 'total_amount',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('invoices', 'discount');
    }
}
