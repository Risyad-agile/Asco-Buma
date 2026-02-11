<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CustomFactorMultiSheetExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new CustomFactorExport() // otomatis nama sheet "Custom Factors"
        ];
    }
}
