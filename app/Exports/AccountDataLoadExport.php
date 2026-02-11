<?php

namespace App\Exports;

use App\Models\AccountDataLoadImport;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AccountDataLoadExport implements FromQuery, WithHeadings, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function headings(): array
    {
        return [
           ['Organization', 'Location','Account Style Caption','Account Number',
           'Account Reference','Account Supplier','Record Start YYYY-MM-DD',
           'Record End YYYY-MM-DD','Quantity','Total cost (incl. Tax) in local currency',
           'Record Reference','Record Invoice Number','Record Data Quality']
        ];
    }
    public function query(){
        return AccountDataLoadImport::query()->select('organization_name','location_name','acc_style_caption',
                'acc_number','acc_reference','acc_supplier','record_date_start','record_date_end','acc_data_qty',
                'acc_data_tot_cost','record_reference','record_inv_no','record_quality',)
                ->where('acc_data_state','1') ;
    }
    public function title(): string
    {
        return 'Records to load';
    }
}



