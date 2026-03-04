<?php

namespace App\Exports;

use App\Models\DataCsr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseCSRExportFormat4 implements FromCollection, WithHeadings
{
    protected int $companyId;
    protected string $accountStyle;
    protected $db; // connection dynamic
    protected string $orgLink;
    protected string $compName;

    public function __construct(int $companyId, string $accountStyle)
    {
        $this->companyId = $companyId;
        $this->accountStyle = $accountStyle;

        // pakai dynamic connection sesuai companyId
        $this->db = DB::connection('dynamic'); 
        // until here cause no data valid 

        // ambil org_link & comp_name dari default DB
        $company = DB::table('companies')
            ->where('id', $companyId)
            ->first(['org_link', 'comp_name']);

        $this->orgLink = $company->org_link ?? '';
        $this->compName = $company->comp_name ?? '';// COMPANY NAME null di database
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
                    '', // Total Employee
                    '', // Direct
                    '', // In direct
                    '', // Level 4
                    '', // Level 5
                    '', // Level 6
                    '', // Level 7
                    '', // Level 8
                    '', // Level 9
                    '', // Middle Management
                    '', // Permanent
                    '', // Senior Management
                    '', // Staff
                    '', // Turnover Death
                    '', // Turnover Others
                    '', // Turnover Resignation
                    '', // Turnover Retirement
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
            'Total Employee',
            'Direct',
            'In direct',
            'Level 4',
            'Level 5',
            'Level 6',
            'Level 7',
            'Level 8',
            'Level 9',
            'Middle Management',
            'Permanent',
            'Senior Management',
            'Staff',
            'Turnover Death',
            'Turnover Others',
            'Turnover Resignation',
            'Turnover Retirement',
        ];
    }
}