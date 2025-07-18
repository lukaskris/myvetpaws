<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'products';
    protected $fillable = [
        'name',
        'alias',
        'description',
        'categori_product_id',
        'price',
        'bought_price',
        'stock',
        'volume',
        'unit',
    ];
    protected $casts = [
        'categori_product_id' => 'integer',
        'price' => 'decimal:2',
        'bought_price' => 'decimal:2',
        'stock' => 'integer',
        'volume' => 'integer',
        'unit' => 'string',
    ];
    public function categoriProduct()
    {
        return $this->belongsTo(CategoriProduct::class, 'categori_product_id');    
    }
}
