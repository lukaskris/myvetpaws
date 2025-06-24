<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clinic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'location',
        'phone',
        'open_days',
        'open_time',
        'close_time',
        'service_description',
        'logo',
        'subscription_status',
        'subscription_ends_at',
        'user_id',
    ];

    protected $casts = [
        'open_days' => 'array',
        'subscription_ends_at' => 'datetime'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
