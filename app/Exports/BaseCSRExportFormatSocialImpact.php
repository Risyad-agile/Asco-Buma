<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseCSRExportFormatSocialImpact implements FromCollection, WithHeadings
{
    protected int $companyId;
    protected $db;
    protected string $orgLink;
    protected string $compName;
    protected $styleLink;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
        $this->db = DB::connection('dynamic');

        $company = DB::table('companies')
            ->where('id', $companyId)
            ->first(['org_link', 'comp_name']);

        $this->orgLink  = $company->org_link ?? '';
        $this->compName = $company->comp_name ?? '';

        $style = $this->db->table('account_styles')
            ->where('acc_style_caption', 'Social Impact')
            ->where('acc_style_state', 1)
            ->first(['acc_style_link']);

        $this->styleLink = $style->acc_style_link ?? '';
    }

    public function collection()
    {
        return $this->db->table('data_toc_summary as s')
            ->where('s.organization', '!=', '')
            ->where('s.location', '!=', '')
            ->whereNotNull('s.location')
            ->select(
                's.location',
                's.total_impacted_real',
                's.total_implementation',
                's.total_observation'
            )
            ->get()
            ->map(function ($row) {
                $loc = $row->location ? $row->location . '_OFFICE' : '';
                return [
                    $this->orgLink,                  // Organization Link
                    $this->compName,                 // Organization
                    $loc,                            // Location
                    '',                              // Location Ref
                    '8004561',                       // Account Style Link
                    'Social Impact',                 // Account Style Caption
                    'Default',                       // Account Subtype
                    'Social Impact_' . $loc,         // Account Number
                    '',                              // Account Reference
                    '',                              // Account Supplier
                    '',                              // Account Reader
                    date('Y') . '-12-01',                    // Record Start YYYY-MM-DD
                    date('Y') . '-12-01',                    // Record End YYYY-MM-DD
                    'Actual',                        // Record Data Quality
                    'Standard',                      // Record Billing Type
                    'Default',                       // Record Subtype
                    'Overwrite',                     // Record Entry Method
                    '',                              // Record Reference
                    '',                              // Record Invoice Number
                    $row->total_impacted_real ?? 0,  // Total Impacted Real
                    $row->total_implementation ?? 0, // Total Implementation
                    $row->total_observation ?? 0,    // Total Observation
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
            'Total Impacted Real',
            'Total Implementation',
            'Total Observation',
        ];
    }
}