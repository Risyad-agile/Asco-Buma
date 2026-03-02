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
    protected $db; // connection dynamic

    public function __construct(int $companyId, string $accountStyle)
    {
        $this->companyId = $companyId;
        $this->accountStyle = $accountStyle;

        // pakai dynamic connection sesuai companyId
        $this->db = DB::connection('dynamic'); 
        // until here cause no data valid 
    }

    public function collection()
    {
        return collect($this->data);
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