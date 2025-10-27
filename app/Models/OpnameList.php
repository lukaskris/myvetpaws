<?php

namespace App\Models;

use App\Models\Customer;
use App\Models\Diagnose;
use App\Models\Pet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OpnameList extends Model
{
    protected $table = 'opname_lists';
    protected $fillable = [
        'name',
        'description',
        'price',
        'date',
        'customer_id',
        'medical_notes',
    ];
    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'price' => 'integer',
        'date' => 'date',
        'medical_notes' => 'string',
        'customer_id' => 'integer',
    ];

    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnose::class, 'opname_list_id');
    }

    public function pets(): BelongsToMany
    {
        return $this->belongsToMany(Pet::class, 'opname_list_pet')
            ->withPivot(['medical_notes'])
            ->withTimestamps();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
