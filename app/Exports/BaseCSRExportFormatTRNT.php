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
        $sub = $this->db->table('data_toc as toc')
            ->select([
                'location',
                DB::raw("DATE(toc.created_utc_date) as created_utc_date"),
                DB::raw("DATE(toc.modified_utc_date) as modified_utc_date"), 
                DB::raw("'CSR Training Total' as account_style"),
            ])

            // gender
            ->selectRaw("SUM(IF(gender = 'Pria',1,0)) as total_pria")
            ->selectRaw("SUM(IF(gender = 'Wanita',1,0)) as total_wanita")

            // total peserta
            ->selectRaw("
                SUM(
                    CASE 
                        WHEN gender IN ('Pria','Wanita') THEN 1 
                        ELSE 0 
                    END
                ) as total_peserta
            ")

            // total hours
            ->selectRaw("SUM(total_hours) as total_hours")

            // age category
            ->selectRaw("SUM(IF(age_category = '> 50 Years Old',1,0)) as total_above50th")
            ->selectRaw("SUM(IF(age_category = '30 - 50 years old',1,0)) as total_30_50th")
            ->selectRaw("SUM(IF(age_category = '< 30 Years Old',1,0)) as total_under30")

            // ->groupBy('location');

            ->groupBy(
                    'toc.location',
                     DB::raw('DATE(toc.created_utc_date)'),
                     DB::raw('DATE(toc.modified_utc_date)')
                );

        return $this->db->query()
            ->fromSub($sub, 'toc')
            ->leftJoin('account_styles as acc', 'acc.acc_style_caption', '=', 'toc.account_style')
            ->leftJoin('locations as loc', 'loc.location_name', '=', 'toc.location')

            ->select([
                'toc.*',
                'acc.acc_style_link',
            ])

            ->where('toc.account_style', $this->accountStyle)
            ->whereNotNull('loc.location_name')

            ->get()

            ->map(function ($row) {
                return [
                    $this->orgLink,                     // Organization Link
                    $this->compName,                    // Organization
                    $row->location ?? '',
                    '',                                 // Location Ref
                    $row->acc_style_link ?? '',        // Account Style Link
                    $row->account_style ?? '',
                    'Default',                         // Account Subtype
                    'Training_'.$row->location ?? '',  // Account Number
                    '',                                 // Account Reference
                    '',                                 // Account Supplier
                    '',                                 // Account Reader
                    $row->created_utc_date ? substr($row->created_utc_date, 0, 10) : '',
                    $row->modified_utc_date ? substr($row->modified_utc_date, 0, 10) : '', 
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
            'Total per <30'
        ];
    }
}