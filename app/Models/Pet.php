<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pets';

    protected $fillable = [
        'name',
        'species_id',
        'breed',
        'breed_id',
        'gender',
        'color',
        'birth_date',
        'vaccinated_at',
        'customer_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'vaccinated_at' => 'datetime'
    ];

    protected static function booted()
    {
        static::saving(function ($pet) {
            if ($pet->species_id) {
                $species = $pet->species()->first();
                $pet->species = $species ? $species->name : null;
            }
            if ($pet->breed_id) {
                $breed = $pet->breed()->first();
                $pet->breed = $breed ? $breed->name : null;
            }
        });
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Removed owner() relationship
    public function species()
    {
        return $this->belongsTo(Species::class, 'species_id');
    }

    public function breed()
    {
        return $this->belongsTo(Breeds::class, 'breed_id');
    }
}
