<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataEnv extends Model
{
    use HasFactory;

    protected $table = 'data_env';

    protected $fillable = [
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
    ];

    protected $casts = [
        'record_start' => 'datetime',
        'record_end' => 'datetime',
        'created_utc_date' => 'datetime',
        'modified_utc_date' => 'datetime',
    ];
}
