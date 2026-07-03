<?php

namespace App\Exports;

use App\Models\DataCsr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseCSRExportFormatTRNT implements FromCollection, WithHeadings
{
    protected int $companyId;
    protected string $accountStyle;
    protected $db;          // dynamic DB connection
    protected string $orgLink;
    protected string $compName;

    public function __construct(int $companyId, string $accountStyle)
    {
        $this->companyId = $companyId;
        $this->accountStyle = $accountStyle;

        // dynamic DB connection
        $this->db = DB::connection('dynamic');

        // ambil org_link & comp_name dari default DB
        $company = DB::table('companies')
            ->where('id', $companyId)
            ->first(['org_link', 'comp_name']);

        $this->orgLink = $company->org_link ?? '';
        $this->compName = $company->comp_name ?? '';
    }

    public function collection()
    {
        $sub = $this->db->table('data_trn as trn')
            ->select([
                'location',
                DB::raw("MIN(start_date) as start_date"),
                DB::raw("MAX(end_date) as end_date"),
                DB::raw("'CSR Training Total' as account_style"),
            ])
            ->selectRaw("SUM(IF(gender = 'Pria', 1, 0)) as total_pria")
            ->selectRaw("SUM(IF(gender = 'Wanita', 1, 0)) as total_wanita")
            ->selectRaw("SUM(CASE WHEN gender IN ('Pria','Wanita') THEN 1 ELSE 0 END) as total_peserta")
            ->selectRaw("SUM(total_hours) as total_hours")
            ->selectRaw("SUM(IF(age_category = '> 50 years old', 1, 0)) as total_above50th")
            ->selectRaw("SUM(IF(age_category = '30 - 50 years old', 1, 0)) as total_30_50th")
            ->selectRaw("SUM(IF(age_category = '< 30 years old', 1, 0)) as total_under30")
            ->selectRaw("SUM(IF(gender = 'Pria', total_hours, 0)) as total_hours_pria")
            ->selectRaw("SUM(IF(gender = 'Wanita', total_hours, 0)) as total_hours_wanita")
            ->selectRaw("SUM(IF(age_category = '< 30 years old', total_hours, 0)) as total_hours_under30")
            ->selectRaw("SUM(IF(age_category = '30 - 50 years old', total_hours, 0)) as total_hours_30_50th")
            ->selectRaw("SUM(IF(age_category = '> 50 years old', total_hours, 0)) as total_hours_above50th")
            ->groupBy('location');

        return $this->db->query()
            ->fromSub($sub, 'trn')
            ->leftJoin('account_styles as acc', 'acc.acc_style_caption', '=', 'trn.account_style')
            ->leftJoin('locations as loc', 'loc.location_name', '=', 'trn.location')
            ->select(['trn.*', 'acc.acc_style_link'])
            ->whereNotNull('loc.location_name')
            ->get()
            ->map(function ($row) {
                return [
                    $this->orgLink,
                    $this->compName,
                    $row->location ?? '',
                    '',
                    $row->acc_style_link ?? '',
                    'Training Total',
                    'Default',
                    'Training_' . ($row->location ?? ''),
                    '',
                    '',
                    '',
                    $row->end_date ?? '',
                    $row->end_date ?? '',
                    'Actual',
                    'Standard',
                    'Default',
                    'Overwrite',
                    '',
                    '',
                    $row->total_pria ?? 0,
                    $row->total_wanita ?? 0,
                    $row->total_peserta ?? 0,
                    $row->total_hours ?? 0,
                    $row->total_above50th ?? 0,
                    $row->total_30_50th ?? 0,
                    $row->total_under30 ?? 0,
                    $row->total_hours_pria ?? 0,
                    $row->total_hours_wanita ?? 0,
                    $row->total_hours_under30 ?? 0,
                    $row->total_hours_30_50th ?? 0,
                    $row->total_hours_above50th ?? 0
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Organization Link',
            'Organization',
            'Location',
            'Location Ref',
            'Account Style Link',
            'Account Style Caption',
            'Account Subtype',
            'Account Number',
            'Account Reference',
            'Account Supplier',
            'Account Reader',
            'Record Start YYYY-MM-DD',
            'Record End YYYY-MM-DD',
            'Record Data Quality',
            'Record Billing Type',
            'Record Subtype',
            'Record Entry Method',
            'Record Reference',
            'Record Invoice Number',
            'Total Pria',
            'Total Wanita',
            'Total Peserta',
            'Total Hours',
            'Total per >50th',
            'Total per 30 - 50 th',
            'Total per <30',
            'Total Hours Pria',
            'Total Hours Wanita',
            'Total Hours per <30',
            'Total Hours per 30 - 50 th',
            'Total Hours per >50th'
        ];
    }
}
