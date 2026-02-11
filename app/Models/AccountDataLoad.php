<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDataLoad extends Model
{
    use HasFactory;
    protected $table='account_data_load';
    protected $fillable=[
        'org_id',
        'loc_id',
        'process_id',
        'acc_style_id',
        'task_id',
        'organization_link',
        'organization_name',
        'location_name',
        'location_ref',
        'acc_style_link',
        'acc_style_caption',
        'acc_subtype', 
        'acc_number',
        'acc_reference',
        'acc_supplier',
        'acc_reader',
        'record_date_start',
        'record_date_end',
        'record_quality',
        'record_billing_type',
        'record_subtype',
        'record_entry_method',
        'record_reference', 
        'record_inv_no',
        'acc_data_qty',
        'acc_data_tot_cost',
        'acc_data_state',
      ];
}
  
 