<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'clinic_id'
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function pets()
    {
        return $this->hasMany(Pet::class);
    }
}
