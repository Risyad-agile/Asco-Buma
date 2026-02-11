<?php

namespace App\Exports;

use App\Models\CustomFactor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class CustomFactorExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    public function collection()
    {
        return CustomFactor::select('custom_factors.*')
            ->join('organizations', 'custom_factors.organization_id', '=', 'organizations.id')
            ->with(['organization', 'factorSet'])
            ->orderBy('organizations.org_link', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            "Action", "Organization", "Associate_Link", "Factor_Link", "Country", "State", "City",
            "Factor Set", "Scope", "Data Type", "Sub Type", "Name","Description","Total CO2e(kgCO2e/unit)",
            "CO2(kgCO2e/unit)", "CH4(kgCO2e/unit)", "N2O(kgCO2e/unit)", "BioCO2", "Indirect CO2e(kgCO2e/unit)",
            "Energy(GJ)/unit","Mass(Kg)/unit", "Volumn(L)/unit", "Area(m2)/unit","Distance(m)/unit",
            "Effective_From","Effective_To", "Published_From", "Published_To", "Review_On",
            "Factor_Source", "Notes"
        ];
    }

    public function map($cf): array
    {
        return [
            // Action bisa disesuaikan dari database, default kosong kalau null
            $cf->action ?? "",

            // Teks kosong -> string kosong
            optional($cf->organization)->org_name ?? "",
            optional($cf->organization)->org_link ?? "",
            $cf->factor_link ?? "",
            $cf->country ?? "",
            $cf->state ?? "",
            $cf->city ?? "",
            optional($cf->factorSet)->name ?? "",
            $cf->scope ?? "",
            $cf->data_type ?? "",
            $cf->sub_type ?? "", 
            $cf->name ?? "",
            $cf->description ?? "",

            // Angka kosong -> null (biar Excel kosong)
            $cf->factor_value ?? null,
            $cf->co2 ?? null,
            $cf->ch4 ?? null,
            $cf->n2o ?? null,
            $cf->biogenic ?? null,
            $cf->co2e ?? null,
            $cf->energy ?? null,
            $cf->mass ?? null,
            $cf->volume ?? null,
            $cf->area ?? null,
            $cf->distance ?? null,

            // Format tanggal dd/mm/yyyy tanpa jam
            $cf->effective_date ? ExcelDate::PHPToExcel(date_create($cf->effective_date)) : null,
            $cf->effective_to ? ExcelDate::PHPToExcel(date_create($cf->effective_to)) : null,
            $cf->published_date ? ExcelDate::PHPToExcel(date_create($cf->published_date)) : null,
            $cf->published_to ? ExcelDate::PHPToExcel(date_create($cf->published_to)) : null,
            $cf->review_on ? ExcelDate::PHPToExcel(date_create($cf->review_on)) : null,

            // Kolom teks terakhir
            $cf->factor_source ?? "",
            $cf->notes ?? ""
        ];
    }

    public function columnFormats(): array
    {
        return [
            'Y' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Effective_From
            'Z' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Effective_To
            'AA' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Published_From
            'AB' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Published_To
            'AC' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Review_On
        ];
    }

    public function title(): string
    {
        return 'Setup_Custom_Factors';
    }
}
