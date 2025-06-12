<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'species',
        'breed',
        'gender',
        'birth_date',
        'color',
        'vaccinated_at',
        'customer_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'vaccinated_at' => 'datetime'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }
}
