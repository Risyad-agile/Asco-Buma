<?php
namespace App\Exports;

use App\Models\DataEnv;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class NormalDataEnvScopeExport implements FromQuery, WithHeadings, WithTitle, WithMapping, WithColumnFormatting
{
    protected array $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }

    public function headings(): array
    {
        return [
            'Organization',              
            'Location',
            'Account Style Caption',
            'Account Number',
            'Account Reference',
            'Account Supplier',
            'Record Start YYYY-MM-DD',
            'Record End YYYY-MM-DD',
            'Quantity',
            'Total cost (incl. Tax) in local currency',
            'Record Reference',
            'Record Invoice Number',
            'Record Data Quality',
        ];
    }

    public function query()
    {
        return DataEnv::query()
            ->whereIn('id', $this->ids)
            ->orderBy('id');
    }

    // ✅ This forces Excel to treat dates as real dates (not text)
    public function map($row): array
    {
        return [
            // $row->organization,
            "PT Anagile Kharisma Utama_POC", // Fixed value for Organization for development/testing
            $row->location,
            $row->account_style_caption,
            $row->account_number,
            $row->account_reference,
            $row->account_supplier,

            $row->record_start ? ExcelDate::dateTimeToExcel($row->record_start->startOfDay()) : null,
            $row->record_end   ? ExcelDate::dateTimeToExcel($row->record_end->startOfDay()) : null,

            $row->quantity,
            $row->total_cost_incl_tax_local_currency,
            $row->record_reference,
            $row->record_invoice_number,
            $row->record_data_quality,
        ];
    }

    // ✅ Force the display format exactly like your screenshot
    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Record Start
            'H' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // Record End
        ];
    }

    public function title(): string
    {
        return 'Records to load';
    }
}
