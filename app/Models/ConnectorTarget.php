<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectorTarget extends Model
{
    use HasFactory;

    protected $table = 'connector_target';

    protected $fillable = [
        'conn_target_name',
        'conn_target_type',
        'conn_target_folder',
        'config_json',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'config_json' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // 🔁 Relasi ke ConnectorSource (1 target bisa punya banyak source)
    public function sources()
    {
        return $this->hasMany(ConnectorSource::class, 'conn_target_id');
    }
}
