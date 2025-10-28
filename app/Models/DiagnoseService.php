<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnoseService extends Model
{
    use HasFactory;

    protected $fillable = [
        'diagnose_detail_id',
        'service_id',
        'notes',
    ];

    public function detail(): BelongsTo
    {
        return $this->belongsTo(DiagnoseDetail::class, 'diagnose_detail_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
