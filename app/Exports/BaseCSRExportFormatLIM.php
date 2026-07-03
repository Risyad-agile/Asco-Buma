<?php

namespace App\Exports;

use App\Models\DataCsr;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseCSRExportFormatLIM implements FromCollection, WithHeadings
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
        $beneficiaries = [
            'Student'       => 'Student',
            'Teacher'       => 'Teacher',
            'JobSeeker'     => 'JobSeeker',
            'LocalVendor'   => 'LocalVendor',
            'Entrepreneur'  => 'Entrepreneur',
            'Farmer'        => 'Farmer',
            'Buma Employee' => 'BumaEmployee',
        ];

        $selects = [
            'toc.location',
            'acc.acc_style_link',
            'acc.acc_style_caption',
            'MIN(DATE(toc.created_at)) AS created_at',
            'MAX(DATE(toc.updated_at)) AS updated_at',
        ];

        foreach ($beneficiaries as $label => $alias) {
            $selects[] = "SUM(IF(toc.tipe_beneficiaries = '$label', toc.output, 0)) AS T_$alias";
            $selects[] = "SUM(IF(toc.tipe_beneficiaries = '$label', toc.outcome, 0)) AS T_{$alias}Outcome";
            $selects[] = "SUM(IF(toc.tipe_beneficiaries = '$label', IFNULL(toc.outcome_additional, 0), 0)) AS T_{$alias}Additional";
            $selects[] = "0 AS T_AmountSpending$alias";
        }

        return $this->db->table('data_toc as toc')
            ->leftjoin('account_styles as acc','acc.acc_style_caption','=','toc.problem_category')
            ->leftJoin('locations as loc', 'loc.location_name', '=', 'toc.location')
            ->where('toc.problem_category', $this->category)
            ->whereNotNull('loc.location_name')
            ->selectRaw(implode(",\n", $selects))
            ->groupBy('toc.location', 'acc.acc_style_link', 'acc.acc_style_caption')
            ->get()
            ->map(function ($row) { 
                return [
                    $this->orgLink,      // Organization Link
                    $this->compName,     // Organization
                    $row->location ?? '',
                    '',                  // Location Ref
                    $row->acc_style_link, //'17011833',          // Account Style Link
                    $row->acc_style_caption, //'Theory of Change - Education',
                    'Default',
                    $row->acc_style_caption.'_'.($row->location ?? ''),
                    // 'TOC - Education - Access and Quality_' . ($row->location ?? ''),
                    '',                  // Account Reference
                    '',                  // Account Supplier
                    '',                  // Account Reader
                    // date('Y') . '-12-01',                    // Record Start YYYY-MM-DD
                    // date('Y') . '-12-01',                    // Record End YYYY-MM-DD
                    '2026-06-01',                    // Record Start YYYY-MM-DD
                    '2026-06-01',                    // Record End YYYY-MM-DD
                    'Actual',
                    'Standard',
                    'Default',
                    'Overwrite',
                    '',
                    '',
                    $row->T_JobSeeker ?? 0,
                    $row->T_JobSeekerOutcome ?? 0,
                    $row->T_JobSeekerAdditional ?? 0,
                    $row->T_LocalVendor ?? 0,
                    $row->T_LocalVendorOutcome ?? 0,
                    $row->T_LocalVendorAdditional ?? 0
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
            'Job Seeker',
            'Outcome Job Seeker',
            'Outcome Add Job Seeker',
            'Local Vendor',
            'Outcome Local Vendor',
            'Outcome Add Local Vendor'
        ];
    }
}