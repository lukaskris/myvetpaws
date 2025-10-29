<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Diagnose;
use App\Models\Pet;
use App\Models\Customer;

class OpnameList extends Model
{
    protected $table = 'opname_lists';
    protected $fillable = [
        'name',
        'description',
        'discount',
        'date',
        'customer_id',
        'medical_notes',
    ];
    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'discount' => 'decimal:2',
        'date' => 'date',
    ];
    
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function diagnoses(): HasMany
    {
        return $this->hasMany(Diagnose::class, 'opname_list_id');
    }
    
    public function pets(): BelongsToMany
    {
        return $this->belongsToMany(Pet::class, 'opname_list_pet')
            ->withPivot('medical_notes', 'duration_days', 'is_done')
            ->withTimestamps();
    }
    
    public function prescriptions()
    {
        // Jika prescription berelasi via detail_transaction, tambahkan relasi di sini
        // return $this->hasManyThrough(Prescription::class, DetailTransaction::class);
        // Jika prescription langsung ke opname_list, gunakan relasi berikut:
        return $this->hasMany(Prescription::class, 'opname_list_id');
    }
}
