<?php

namespace App\Exports;

use App\Models\DataCsr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseCSRExportFormat1 implements FromCollection, WithHeadings
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
        return $this->db->table('data_csr')
            ->where('account_style', $this->accountStyle)
            ->get()
            ->map(function ($row) {
                return [
                    $this->orgLink,      // Organization Link
                    $this->compName,     // Organization
                    $row->location ?? '',
                    '',                  // Location Ref
                    '',                  // Account Style Link
                    $row->account_style ?? '',
                    '',                  // Account Subtype
                    $row->account_number ?? '',
                    '',                  // Account Reference
                    '',                  // Account Supplier
                    '',                  // Account Reader
                    $row->year ? $row->year . '-01-01' : '',
                    $row->year ? $row->year . '-12-31' : '',
                    '', '', '', '', '', '',
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
                    $row->konghucu ?? 0,
                    $row->year ?? ''
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
            'Konghucu',
            'Period'
        ];
    }
}