<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow; 
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AccountDataLoadImportCollection implements WithMultipleSheets
{
    /**
    * @param Collection $collection
    */
    public function sheets(): array
    {
        return [
            'Records to load' => new RecordToLoad(),
        ];
    }

}
class RecordToLoad implements ToCollection,WithHeadingRow,WithCalculatedFormulas
{
    public function collection(Collection $row)
    {

        return $row;
        // $arrayAccStyle=array();

        // return new AccountDataLoadImport([
        //     'organization_name'=> $row['organization'],
        //     'location_name'=> $row['location'],  
        //     'acc_style_caption'=> $row['account_style_caption'],
        //     'acc_number'=> $row['account_number'],
        //     'acc_reference'=> $row['account_reference'],
        //     'acc_supplier'=> $row['account_supplier'], 
        //     'record_date_start'=> \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['record_start_yyyy_mm_dd']),
        //     'record_date_end'=>\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['record_end_yyyy_mm_dd']),
        //     'record_quality'=> $row['quantity'],
        //     'acc_data_tot_cost'=> $row['total_cost_incl_tax_in_local_currency'],  
        //     'record_reference'=> $row['record_reference'],
        //     'record_inv_no'=> $row['record_invoice_number'],
        //     'record_quality'=> $row['record_data_quality'], 
        // ]);
    }
}