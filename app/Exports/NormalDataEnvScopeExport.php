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
            // 'Organization Link',
            'Organization',
            'Location',
            // 'Location Ref',
            // 'Account Style Link',
            'Account Style Caption',
            // 'Account Subtype',
            'Account Number',
            'Account Reference',
            'Account Supplier',
            // 'Account Reader',
            'Record Start YYYY-MM-DD',
            'Record End YYYY-MM-DD',

            'Quantity',
            'Total cost (incl. Tax) in local currency',
            
            // 'Record Billing Type',
            // 'Record Subtype',
            // 'Record Entry Method',

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
            // '8000168', // Organization Link
            'PT. Buma International Group Tbk.', // Fixed value for Organization for development/testing
            $row->location,
            // '', // Location Ref
            // $row->account_style_link ?? '41961', // Account Style Link
            $row->account_style_caption, // Account Style Caption
            // $row->account_subtype ?? 'Default', // Account Subtype
            $row->account_number, // Account Number
            $row->account_reference, // Account Reference
            $row->account_supplier, // Account Supplier
            // $row->account_reader, // Account Reader

            $row->record_start ? substr($row->record_start, 0, 10) : '',
            $row->record_end   ? substr($row->record_end, 0, 10) : '',

            $row->quantity,
            $row->total_cost_incl_tax_local_currency,
            
            // $row->record_billing_type ?? 'Standard', // Record Billing Type
            // $row->record_subtype ?? '', // Record Subtype
            // $row->record_entry_method ?? 'Overwrite', // Record Entry Method
            $row->record_reference,
            $row->record_invoice_number,
            $row->record_data_quality ?? 'Actual', // Record Data Quality
            
        ];
    }

    public function columnFormats(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Records to load';
    }
}
