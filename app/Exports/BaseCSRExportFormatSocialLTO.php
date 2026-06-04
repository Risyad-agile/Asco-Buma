<?php

namespace App\Exports;

use App\Models\DataCsr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseCSRExportFormatSocialLTO implements FromCollection, WithHeadings
{
    protected int $companyId;
    protected string $category;
    protected $db;          // dynamic DB connection
    protected string $orgLink;
    protected string $compName;

    public function __construct(int $companyId, string $category)
    {
        $this->companyId = $companyId;
        $this->category = $category;

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
        return $this->db->table('data_toc as toc')
            ->leftJoin('account_styles as acc', 'acc.acc_style_caption', '=', 'toc.problem_category')
            ->leftJoin('locations as loc', 'loc.location_name', '=', 'toc.location')
            ->where('toc.problem_category', $this->category)
            ->whereNotNull('loc.location_name')
            ->selectRaw("
            toc.location,
            acc.acc_style_link,
            acc.acc_style_caption,
            MIN(DATE(toc.created_at)) AS created_at,
            MAX(DATE(toc.updated_at)) AS updated_at,
            SUM(IFNULL(toc.output_event, 0)) AS total_output_event,
            SUM(IFNULL(toc.output_all, 0)) AS total_output_all
        ")
            ->groupBy('toc.location', 'acc.acc_style_link', 'acc.acc_style_caption')
            ->get()
            ->map(function ($row) {
                return [
                    $this->orgLink,// Organization Link
                    $this->compName,// Organization
                    $row->location ?? '',// Location
                    '',// Location Ref
                    $row->acc_style_link,// Account Style Link
                    $row->acc_style_caption,// Account Style Caption
                    'Default',// Account Subtype
                    $row->acc_style_caption . '_' . ($row->location ?? ''),// Account Number
                    '',// Account Reference
                    '',// Account Supplier
                    '',// Account Reader
                    '2025-12-31',                    // Record Start YYYY-MM-DD
                    '2025-12-31',                    // Record End YYYY-MM-DD
                    'Actual',// Record Data Quality
                    'Standard',// Record Billing Type
                    'Default',// Record Subtype
                    'Overwrite',// Record Entry Method
                    '',// Record Reference
                    '',// Record Invoice Number
                    // '',// Buma Employee
                    // '',// Outcome Buma Employee
                    // '',// Outcome Add Buma Employee
                    $row->total_output_event ?? 0, // output event as Amount Spending Buma Employee
                    $row->total_output_all ?? 0, // output all as Outcome Buma Employee
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
            // 'Buma Employee',
            // 'Outcome Buma Employee',
            // 'Outcome Add Buma Employee',
            'Output Event',
            'Output All'
        ];
    }
}
