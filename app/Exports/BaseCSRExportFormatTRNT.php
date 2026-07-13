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

    private const POSITION_CASE = "CASE 
        WHEN trn.level IN (1,2) THEN 'Blue Collar'
        WHEN trn.level IN (3,4) THEN 'Staff'
        WHEN trn.level IN (5,6) THEN 'Middle Management'
        WHEN trn.level IN (7,8) THEN 'Senior Management'
    END";

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
                DB::raw(self::POSITION_CASE . " as job_category"),
                DB::raw("MIN(start_date) as start_date"),
                DB::raw("MAX(end_date) as end_date"),
                DB::raw("'CSR Training Total' as account_style"),
            ])
            ->selectRaw("COUNT(DISTINCT IF(gender = 'Pria', personnel_number, NULL)) as total_pria")
            ->selectRaw("COUNT(DISTINCT IF(gender = 'Wanita', personnel_number, NULL)) as total_wanita")
            ->selectRaw("COUNT(DISTINCT personnel_number) as total_peserta")
            ->selectRaw("SUM(total_hours) as total_hours")
            ->selectRaw("COUNT(DISTINCT IF(age_category = '> 50 years old', personnel_number, NULL)) as total_above50th")
            ->selectRaw("COUNT(DISTINCT IF(age_category = '30 - 50 years old', personnel_number, NULL)) as total_30_50th")
            ->selectRaw("COUNT(DISTINCT IF(age_category = '< 30 years old', personnel_number, NULL)) as total_under30")
            ->selectRaw("SUM(IF(gender = 'Pria', total_hours, 0)) as total_hours_pria")
            ->selectRaw("SUM(IF(gender = 'Wanita', total_hours, 0)) as total_hours_wanita")
            ->selectRaw("SUM(IF(age_category = '< 30 years old', total_hours, 0)) as total_hours_under30")
            ->selectRaw("SUM(IF(age_category = '30 - 50 years old', total_hours, 0)) as total_hours_30_50th")
            ->selectRaw("SUM(IF(age_category = '> 50 years old', total_hours, 0)) as total_hours_above50th")
            ->selectRaw("AVG(IF(gender = 'Pria', total_hours, NULL)) as avg_hours_pria")
            ->selectRaw("AVG(IF(gender = 'Wanita', total_hours, NULL)) as avg_hours_wanita")
            ->groupBy('location', DB::raw(self::POSITION_CASE));

        return $this->db->query()
            ->fromSub($sub, 'trn')
            ->leftJoin('account_styles as acc', 'acc.acc_style_caption', '=', 'trn.account_style')
            ->leftJoin('locations as loc', 'loc.location_name', '=', 'trn.location')
            ->select(['trn.*', 'acc.acc_style_link'])
            ->whereNotNull('loc.location_name')
            ->whereNotNull('trn.job_category')
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
                    $row->total_peserta ?? 0,
                    $row->job_category ?? '',
                    $row->total_pria ?? 0,
                    $row->total_wanita ?? 0,
                    $row->total_hours ?? 0,
                    $row->total_under30 ?? 0,
                    $row->total_30_50th ?? 0,
                    $row->total_above50th ?? 0,
                    $row->total_hours_pria ?? 0,
                    $row->total_hours_wanita ?? 0,
                    $row->total_hours_under30 ?? 0,
                    $row->total_hours_30_50th ?? 0,
                    $row->total_hours_above50th ?? 0,
                    $row->avg_hours_pria ?? 0,
                    $row->avg_hours_wanita ?? 0
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
            'Total',
            'Job Category',
            'Total M',
            'Total F',
            'Total Hours',
            'Total < 30',
            'Total 30 - 50',
            'Total > 50',
            'Total Hrs M',
            'Total Hrs F',
            'Total Hours < 30',
            'Total Hours 30 - 50',
            'Total Hours > 50',
            'Avg Hrs M',
            'Avg Hrs F'
        ];
    }
}
