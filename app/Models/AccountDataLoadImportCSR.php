<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountDataLoadImportCSR extends Model
{
    use HasFactory;
    protected $table='account_data_load_import_csr';
    protected $fillable=[
        'comp_id',
        'task_id',
        'organization_name',
        'location_name',
        'acc_style_caption',  
        'record_date_start',
        'record_date_end',
        'csr_total',
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
    ];
}



