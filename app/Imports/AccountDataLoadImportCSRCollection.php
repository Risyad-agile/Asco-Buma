<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow; 
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AccountDataLoadImportCSRCollection implements WithMultipleSheets
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
         
    }
}