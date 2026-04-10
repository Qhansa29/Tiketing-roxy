<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_date',
        'sequence_no',
        'queue_number',
        'customer_name',
        'customer_phone',
        'device_type',
        'complaint',
        'status',
        'called_at',
        'service_started_at',
        'service_finished_at',
        'service_estimate_minutes',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'queue_date' => 'date',
            'called_at' => 'datetime',
            'service_started_at' => 'datetime',
            'service_finished_at' => 'datetime',
            'service_estimate_minutes' => 'integer',
        ];
    }
}
