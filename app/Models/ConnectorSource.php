<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectorSource extends Model
{
    use HasFactory;

    protected $table = 'connector_source';

    protected $fillable = [
        'comp_id',
        'conn_target_id',
        'conn_source_name',
        'conn_source_type',
        'config_json',
        'local_table',
        'is_active',
    ];

    protected $casts = [
        'config_json' => 'array',
        'is_active' => 'boolean',
    ];

    // 🔁 Relasi ke Company
    public function company()
    {
        return $this->belongsTo(Companies::class, 'comp_id');
    }

    // 🔁 Relasi ke ConnectorTarget
    public function target()
    {
        return $this->belongsTo(ConnectorTarget::class, 'conn_target_id');
    }
}
