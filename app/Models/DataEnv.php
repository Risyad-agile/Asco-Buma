<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DataEnv extends Model
{
    protected $table = 'data_env';

    protected $fillable = [
        'scope',
        'remote_id',
        'sync_status',
        'last_synced_at',

        'organization',
        'location',
        'account_style_caption',
        'account_number',
        'account_reference',
        'account_supplier',

        'record_start',
        'record_end',

        'quantity',
        'total_cost_incl_tax_local_currency',

        'record_reference',
        'record_invoice_number',
        'record_data_quality',

        'total',
        'created_utc_date',
        'modified_utc_date',

        'export_status',
        'exported_at',
    ];

    protected $casts = [
        'record_start' => 'datetime',
        'record_end' => 'datetime',
        'created_utc_date' => 'datetime',
        'modified_utc_date' => 'datetime',
        'last_synced_at' => 'datetime',
        'exported_at' => 'datetime',
        'quantity' => 'decimal:4',
        'total_cost_incl_tax_local_currency' => 'decimal:4',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes (Cleaner Query Usage)
    |--------------------------------------------------------------------------
    */

    public function scopePendingExport(Builder $query): Builder
    {
        return $query->where('export_status', 'pending');
    }

    public function scopeByScope(Builder $query, int $scope): Builder
    {
        return $query->where('scope', $scope);
    }
}
