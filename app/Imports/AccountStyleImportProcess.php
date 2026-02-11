<?php

namespace App\Imports;
use App\Models\AccountStylesImport;
use Maatwebsite\Excel\Concerns\ToModel; 
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class AccountStyleImportProcess implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */

    public function model(array $row)
    {
        // dd($row);
        // echo($row['product']);
        return new AccountStylesImport([
            'acc_style_product'=> $row['product'],
            'acc_style_datatype'=> $row['data_type'],  
            'acc_style_scope'=> $row['scope'],
            'acc_style_category'=> $row['category'],
            'acc_style_name'=> $row['account_style_name'],
            'acc_style_caption'=> $row['account_style_caption'], 
            'acc_style_qty_uom'=> $row['quantity_value_uom'],
            'acc_style_cost_supported'=> $row['total_cost_supported'], 
        ]);
    }
}
