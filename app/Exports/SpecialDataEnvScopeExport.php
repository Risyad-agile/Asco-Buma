<?php

namespace App\Exports;

use App\Models\AccountStyles;
use App\Models\DataEnv;
use App\Models\Organization; // <-- adjust if your model name differs
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Support\Collection;


class SpecialDataEnvScopeExport implements
    FromQuery,
    WithHeadings,
    WithTitle,
    WithMapping,
    WithColumnFormatting
{
    protected array $ids;

    /** @var array<string,string> organization_name => org_link */
    protected array $orgLinks = [];

    /** @var array<string,AccountStyles> caption => AccountStyles */
    protected $stylesByCaption;

    protected string $qtyHeader = 'Quantity';

    public function __construct(array $ids)
    {
        $this->ids = $ids;

        // Preload distinct org names + captions for this export batch
        $meta = DataEnv::query()
            ->whereIn('id', $this->ids)
            ->select(['organization', 'account_style_caption'])
            ->get();

        $orgNames = $meta->pluck('organization')->filter()->unique()->values()->all();
        $captions = $meta->pluck('account_style_caption')->filter()->unique()->values()->all();

        // 1) preload org_link from Organizations table (by organization name)
        if (!empty($orgNames)) {
            // Adjust column names if different:
            // - organization name field in organizations table
            // - org_link field in organizations table
            $this->orgLinks = Organization::query()
                ->whereIn('org_name', $orgNames)   // <-- if your column is "org_name", change here
                ->pluck('org_link', 'org_name')    // <-- key=organization name
                ->toArray();
        }

        // 2) preload account styles by caption
        if (!empty($captions)) {
            $this->stylesByCaption = AccountStyles::query()
                ->whereIn('acc_style_caption', $captions)
                ->where('acc_style_state', 1)
                ->get()
                ->keyBy('acc_style_caption');
        }

        // 3) determine quantity header from the "first" style in this export
        $firstCaption = $meta->pluck('account_style_caption')->filter()->first();
        if ($firstCaption) {
            $firstStyle = $this->stylesByCaption->get($firstCaption);
            $hdr = $firstStyle?->acc_style_xls_header;
            if ($hdr) $this->qtyHeader = $hdr;
        }
        
    }

    public function query()
    {
        return DataEnv::query()
            ->whereIn('id', $this->ids)
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'Organization Link',                // 1  ✅ from Organizations.org_link
            'Organization',                     // 2  ✅ from data_env.organization 
            'Location',                         // 3
            'Location Ref',                     // 4
            'Account Style Link',               // 5  ✅ from AccountStyles.acc_style_link
            'Account Style Caption',            // 6
            'Account Subtype',                  // 7  ✅ fixed "Default"
            'Account Number',                   // 8
            'Account Reference',                // 9
            'Account Supplier',                 // 10
            'Account Reader',                   // 11
            'Record Start YYYY-MM-DD',          // 12
            'Record End YYYY-MM-DD',            // 13
            'Record Data Quality',              // 14 ✅ fixed "Actual"
            'Record Billing Type',              // 15 ✅ fixed "Standard"
            'Record Subtype',                   // 16 ✅ fixed "Default"
            'Record Entry Method',              // 17 ✅ fixed "Overwrite"
            'Record Reference',                 // 18
            'Record Invoice Number',            // 19
            $this->qtyHeader,                   // 20 ✅ from AccountStyles.acc_style_xls_header
            'Total Cost',                       // 21
        ];
    }

    public function map($row): array
    {
        $caption = (string) ($row->account_style_caption ?? '');
        $style = $this->stylesByCaption->get($caption);

        $orgName = (string) ($row->organization ?? '');
        $orgLink = $this->orgLinks[$orgName] ?? null;

        $start = $row->record_start
            ? ExcelDate::dateTimeToExcel($row->record_start->copy()->startOfDay())
            : null;

        $end = $row->record_end
            ? ExcelDate::dateTimeToExcel($row->record_end->copy()->startOfDay())
            : null;

        return [
            $orgLink,                        // 1 ✅ organization link
            // $row->organization,              // 2
            'PT Anagile Kharisma Utama_POC',  //development/testing fixed value for Organization
            $row->location,                  // 3
            null,                            // 4 location ref (not available)
            $style?->acc_style_link,         // 5 ✅ account style link
            $row->account_style_caption,     // 6
            'Default',                       // 7 ✅ fixed
            $row->account_number,            // 8
            $row->account_reference,         // 9
            $row->account_supplier,          // 10
            $style?->acc_style_reader,       // 11 (optional; from account styles if you want)
            $start,                          // 12
            $end,                            // 13
            'Actual',                        // 14 ✅ fixed
            'Standard',                      // 15 ✅ fixed
            'Default',                       // 16 ✅ fixed
            'Overwrite',                     // 17 ✅ fixed
            $row->record_reference,          // 18
            $row->record_invoice_number,     // 19
            $row->quantity,                  // 20
            $row->total_cost_incl_tax_local_currency, // 21
        ];
    }

    public function columnFormats(): array
    {
        return [
            'L' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // 12 Record Start
            'M' => NumberFormat::FORMAT_DATE_YYYYMMDD2, // 13 Record End
            'T' => '#,##0.0000',                        // 20 Quantity
            'U' => '#,##0.00',                          // 21 Total Cost
        ];
    }

    public function title(): string
    {
        return 'Records to load';
    }
}
