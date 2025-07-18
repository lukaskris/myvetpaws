<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class CategoriProduct extends Model
{
    use HasFactory;
    protected $table = 'categori_products';
    protected $fillable = ['name', 'action'];
    protected $casts = [
        'action' => 'boolean',
    ];
    
    public function products()
    {
        return $this->hasMany(Product::class, 'categori_product_id');
    }
}
