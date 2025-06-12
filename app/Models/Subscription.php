<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'start_date',
        'end_date',
        'is_trial',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
