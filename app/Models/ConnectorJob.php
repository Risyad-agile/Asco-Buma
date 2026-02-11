<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectorJob extends Model
{
    use HasFactory;

    protected $table = 'connector_jobs';

    protected $fillable = [
        'connector_source_id',
        'job_name',
        'schedule_type',
        'schedule_time',
        'days_of_week',
        'is_active',
        'last_run_at',
        'next_run_at',
        'remarks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    // 🔁 Relasi ke ConnectorSource
    public function source()
    {
        return $this->belongsTo(ConnectorSource::class, 'connector_source_id');
    }
}
