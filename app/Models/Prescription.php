<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'opname_list_id',
        'product_id',
        'amount',
        'frequency',
        'duration',
        'total_amount',
    ];

    public function opnameList()
    {
        return $this->belongsTo(OpnameList::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}