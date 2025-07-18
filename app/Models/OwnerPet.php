<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Pet;

class OwnerPet extends Model
{
    protected $table = 'owner_pets';
    protected $fillable = [
        'profile_picture',
        'title',
        'name',
        'email',
        'phone',
        'address',
    ];
    protected $casts = [
        'profile_picture' => 'string', 
        'title' => 'string',
        'name' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'address' => 'string',
    ];

    public function pets()
    {
        return $this->belongsToMany(Pet::class, 'owner_pet_id', 'pet_id');
    }
}
