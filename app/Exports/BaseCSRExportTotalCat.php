<?php

namespace App\Exports;

use App\Models\DataCsr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseCSRExportTotalCat implements FromCollection, WithHeadings
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
        $sub = $this->db->table('data_csr as csr')
        ->select([
            'csr.location',
            'csr.year',
            'csr.month',
            DB::raw("'CSR Employee - Total per cat' as account_style"),
        ])
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Direct', csr.employee_total,0)) as Direct")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Indirect', csr.employee_total,0)) as InDirect")
        ->selectRaw("
                        SUM(IF(csr.account_style = 'CSR Employee - Direct', csr.employee_total,0)) +
                        SUM(IF(csr.account_style = 'CSR Employee - Indirect', csr.employee_total,0))
                        as TotalEmployee
                    ")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Level 4', csr.employee_total,0)) as Level4")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Level 5', csr.employee_total,0)) as Level5")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Level 6', csr.employee_total,0)) as Level6")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Level 7', csr.employee_total,0)) as Level7")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Level 8', csr.employee_total,0)) as Level8")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Middle Management', csr.employee_total,0)) as MidManage")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee – Outsource', csr.employee_total,0)) as Outsource")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Senior Management', csr.employee_total,0)) as SnrManage")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Staff', csr.employee_total,0)) as Staff")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Turnover - Death', csr.employee_total,0)) as TODeath")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Turnover - Others', csr.employee_total,0)) as TOOthers")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Turnover - Resignation', csr.employee_total,0)) as TOResign")
        ->selectRaw("SUM(IF(csr.account_style = 'CSR Employee - Turnover - Retirement', csr.employee_total,0)) as TORetire")
        ->groupBy(
                    'csr.location',
                    'csr.year',
                    'csr.month'
                );
        return $this->db->query()
            ->fromSub($sub, 'csr')
            ->leftJoin('account_styles as acc', 'acc.acc_style_caption', '=', 'csr.account_style')
            ->leftjoin('locations as loc','loc.location_name','=','csr.location')
            ->select('csr.*', 'acc.acc_style_link')
            ->whereNotNull('loc.location_name')
            ->get()
            ->map(function ($row) {
                return [
                    $this->orgLink,      // Organization Link
                    $this->compName,     // Organization
                    $row->location ?? '',
                    '',                  // Location Ref
                    $row->acc_style_link ?? '', // Account Style Link
                    $row->account_style ?? '',
                    'Default',           // Account Subtype
                    ($row->account_style ?? '') . '_' . ($row->location ?? ''), // Account Number
                    '',                  // Account Reference
                    '',                  // Account Supplier
                    '',                  // Account Reader
                    sprintf('%04d-%02d-01', $row->year, $row->month), // Record Start (1st of month)
                    sprintf('%04d-%02d-01', $row->year, $row->month), // Record End (1st of month) 
                    'Actual', 'Standard', 'Default', 'Overwrite', '', '',
                    $row->TotalEmployee, // Total Employee
                    $row->Direct,
                    $row->InDirect, 
                    $row->Level4, 
                    $row->Level5,
                    $row->Level6,
                    $row->Level7,
                    $row->Level8,
                    $row->MidManage,
                    $row->Outsource,
                    $row->SnrManage,
                    $row->Staff,
                    $row->TODeath,
                    $row->TOOthers,
                    $row->TOResign,
                    $row->TORetire
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
            'Employees (Number)',
            'Direct',
            'Indirect',
            'Level 4',
            'Level 5',
            'Level 6',
            'Level 7',
            'Level 8',
            'Middle Management',
            'Outsource',
            'Senior Management',
            'Staff',
            'Turnover Death',
            'Turnover Others',
            'Turnover Resignation',
            'Turnover Retirement'
        ];
    }
}