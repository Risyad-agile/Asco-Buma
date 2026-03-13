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
        return $this->db->table('data_csr as csr')
            ->leftJoin('account_styles as acc', 'csr.account_style', '=', 'acc.acc_style_caption') 
            ->where('csr.account_style', $this->accountStyle) 
            ->select('csr.*', 'acc.acc_style_link')
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
                    $row->account_number ?? '',
                    '',                  // Account Reference
                    '',                  // Account Supplier
                    '',                  // Account Reader
                    $row->created_utc_date ? substr($row->created_utc_date, 0, 10) : '',
                    $row->modified_utc_date ? substr($row->modified_utc_date, 0, 10) : '', 
                    'Actual', 'Standard', 'Default', 'Overwrite', '', '',
                    0,0,0,0,0,0,0,0,0,0,
                    0,0,0,0,0,0,0,0,0,0,
                    0,0,0,0,0,0,0,0,0 
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
            'Student',
            'Outcome Student',
            'Outcome Add Student',
            'Amount Spending Student',
            'Teacher',
            'Outcome Teacher',
            'Outcome Add Teacher',
            'Amount Spending Teacher',
            'Job Seeker',
            'Outcome Job Seeker',
            'Outcome Add Job Seeker',
            'Amount Spending Job Seeker',
            'Local Vendor',
            'Outcome Local Vendor',
            'Outcome Add Local Vendor',
            'Amount Spending Local Vendor',
            'Enterpreneur',
            'Outcome Enterpreneur',
            'Outcome add Enterpreneur',
            'Amount Spending Enterpreneur',
            'Farmer',
            'Outcome Farmer',
            'Outcome Add Farmer',
            'Amount Spending Farmer',
            'Buma Employee',
            'Outcome Buma Employee',
            'Outcome Add Buma Employee',
            'Amount Spending Buma Employee', 
            'Period'
        ];
    }
}