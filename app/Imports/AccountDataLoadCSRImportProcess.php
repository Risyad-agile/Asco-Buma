<?php

namespace App\Imports;

use App\Models\AccountDataLoadCSR;
use Maatwebsite\Excel\Concerns\ToModel;

class AccountDataLoadCSRImportProcess implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new AccountDataLoadCSRImport([
            //
        ]);
    }
}
