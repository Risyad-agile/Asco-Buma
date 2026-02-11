<?php

namespace App\Exports;

use App\Models\AccountDataLoadCSR;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AccountDataLoadCSRExport implements FromQuery, WithHeadings, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array
    {
        return [
           ['Organization Link','Organization', 'Location','Location Ref','Account Style Link',
           'Account Style Caption','Account Subtype','Account Number','Account Reference','Account Supplier','Account Reader',
           'Record Start YYYY-MM-DD','Record End YYYY-MM-DD','Record Data Quality','Record Billing Type','Record Subtype',
           'Record Entry Method','Record Reference','Record Invoice Number','Employee Total','Male','Female',
           'Age <30','Age 30 - 50','Age > 50','Education PHD','Education Post Graduate','Education Baachelor Degree',
           'Education High School','Education Junior High School','Education Elementary School','Education Others',
           'Religius Buddha','Religius Hindu','Religius Moslem','Religius Catholic','Religius Christian','Religius Others']
        ];
    }
    public function query(){
        return AccountDataLoadCSR::query()->select('organization_link','organization_name','location_name','location_ref',
                'acc_style_link','acc_style_caption','acc_subtype', 'acc_number','acc_reference','acc_supplier','acc_reader',
                'record_date_start','record_date_end','record_quality','record_billing_type','record_subtype','record_entry_method',
                'record_reference','record_inv_no','acc_reader','csr_total','csr_male','csr_female','csr_less_30','csr_between_30_50',
                'csr_more_50','csr_phd','csr_post_graduate','csr_bachelor_degree','csr_high_school','csr_junior_high_school',
                'csr_elementary_school','csr_eduction_other','csr_budha','csr_hindu','csr_islam','csr_katolik',
                'csr_kristen','csr_religion_other',)->where('csr_state','1');
    }
    public function title(): string
    {
        return 'Records to load';
    }
}








