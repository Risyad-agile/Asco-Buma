<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDataLoadCSR extends Model
{
    use HasFactory;
    protected $table='account_data_load_csr';
    protected $fillable=[
        'reff_id',
        'org_id',
        'loc_id',
        'acc_style_id',
        'process_id',
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
        'acc_data_qty',
        'acc_data_tot_cost', 
        'record_date_start', 
        'record_date_end',
        'record_quality',
        'record_billing_type',
        'record_subtype',
        'record_entry_method',
        'record_reference', 
        'record_inv_no',
        'csr_total',
        // 'csr_management', jadi nama tipe
        // 'csr_non_management', jadi nama tipe
        // 'csr_permanent', jadi nama tipe
        // 'csr_contract',jadi nama tipe
        // 'csr_direct', jadi nama tipe
        // 'csr_indirect', jadi nama tipe
        'csr_male',
        'csr_female',
        'csr_less_30',
        'csr_between_30_50',
        'csr_more_50',
        'csr_phd',
        'csr_post_graduate',
        'csr_bachelor_degree',
        'csr_high_school',
        'csr_junior_high_school',
        'csr_elementary_school',
        'csr_eduction_other',
        'csr_islam',
        'csr_budha',
        'csr_hindu',
        'csr_katolik',
        'csr_kristen',
        'csr_religion_other',
        'csr_state', //0=non active 1=active 2=poceesed
    ];  
}
 




