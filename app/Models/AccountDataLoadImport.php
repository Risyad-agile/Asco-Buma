<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDataLoadImport extends Model
{
    use HasFactory;
    protected $table='account_data_load_import';
    protected $fillable=[
        'comp_id',
        'task_id',
        'organization_name',
        'location_name',
        'acc_style_caption',
        'acc_number',
        'acc_reference',
        'acc_supplier',
        'record_date_start',
        'record_date_end',
        'record_quality',
        'acc_data_tot_cost',
        'record_reference',
        'record_inv_no',
        'acc_data_qty',
        'acc_data_state',
    ];
}

 