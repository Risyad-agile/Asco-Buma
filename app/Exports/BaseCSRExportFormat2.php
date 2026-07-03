<?php

namespace App\Exports;

use App\Models\DataCsr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseCSRExportFormat2 implements FromCollection, WithHeadings
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
        ->whereIn('csr.account_style', [
                'CSR Employee - Level 1',
                'CSR Employee - Level 2',
                'CSR Employee - Level 3',
                'CSR Employee - Level 4',
                'CSR Employee - Level 5',
                'CSR Employee - Level 6',
                'CSR Employee - Level 7',
                'CSR Employee - Level 8',
                ])
        ->select([
            'csr.location',
            'csr.year',
            'csr.month',
            DB::raw("'CSR Employee - Employee Total' as account_style"),
        ])  
        ->selectRaw('sum(csr.employee_total) as employee_total')
        ->selectRaw('SUM(csr.male) as male')
        ->selectRaw('SUM(csr.female) as female')
        ->selectRaw('SUM(csr.k30_th) as k30_th')
        ->selectRaw('SUM(csr.thirty_to_50_th) as thirty_to_50_th')
        ->selectRaw('SUM(csr.more_than_50_th) as more_than_50_th')
        ->selectRaw('SUM(csr.phd) as phd')
        ->selectRaw('SUM(csr.postgraduate) as postgraduate')
        ->selectRaw('SUM(csr.undergraduate) as undergraduate')
        ->selectRaw('SUM(csr.diploma) as diploma')
        ->selectRaw('SUM(csr.high_school) as high_school')
        ->selectRaw('SUM(csr.junior_high_school) as junior_high_school')
        ->selectRaw('SUM(csr.elementary_school) as elementary_school')
        ->selectRaw('SUM(csr.others) as others')
        ->selectRaw('SUM(csr.islam) as islam')
        ->selectRaw('SUM(csr.kristen) as kristen')
        ->selectRaw('SUM(csr.katolik) as katolik')
        ->selectRaw('SUM(csr.hindu) as hindu')
        ->selectRaw('SUM(csr.budha) as budha')
        ->selectRaw('SUM(csr.konghucu) as konghucu')
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
                    $row->employee_total ?? 0,
                    $row->male ?? 0,
                    $row->female ?? 0,
                    $row->k30_th ?? 0,
                    $row->thirty_to_50_th ?? 0,
                    $row->more_than_50_th ?? 0,
                    $row->phd ?? 0,
                    $row->postgraduate ?? 0,
                    $row->undergraduate ?? 0,
                    $row->diploma ?? 0,
                    $row->high_school ?? 0,
                    $row->junior_high_school ?? 0,
                    $row->elementary_school ?? 0,
                    $row->others ?? 0,
                    $row->islam ?? 0,
                    $row->kristen ?? 0,
                    $row->katolik ?? 0,
                    $row->hindu ?? 0,
                    $row->budha ?? 0,
                    $row->konghucu ?? 0
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
            'Male',
            'Female',
            'Under30yr',
            '30to50yr',
            'Above50yr',
            'PhD',
            'Postgraduate',
            'Undergraduate',
            'Diploma',
            'High School',
            'Junior High School',
            'Elementry School',
            'Others',
            'Moslem',
            'Christian',
            'Catholic',
            'Hindu',
            'Buddha',
            'Konghucu'
        ];
    }
}