<?php

namespace App\Models;

use CodeIgniter\Model;

class MedicalRecordItemsModel extends Model
{
    protected $table            = 'medical_record_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'medical_record_id',
        'item_id',
        'quantity',
        'buy_price',
        'sell_price'
    ];

    protected $useTimestamps = false;
}
