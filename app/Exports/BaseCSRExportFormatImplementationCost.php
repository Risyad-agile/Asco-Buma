<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BaseCSRExportFormatImplementationCost implements FromCollection, WithHeadings
{
    protected int $companyId;
    protected $db;
    protected string $orgLink;
    protected string $compName;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;

        $this->db = DB::connection('dynamic');

        $company = DB::table('companies')
            ->where('id', $companyId)
            ->first(['org_link', 'comp_name']);

        $this->orgLink = $company->org_link ?? '';
        $this->compName = $company->comp_name ?? '';
    }

    public function collection()
    {
        return $this->db->table('data_toc_summary as s')
            ->leftJoin('locations as loc', 'loc.location_name', '=', DB::raw("CONCAT(s.location, '_OFFICE')"))
            ->whereNotNull('loc.location_name')
            ->where('s.organization', '!=', '')
            ->select(
                's.location',
                's.total_cost_planning',
                's.total_cost_implementation',
                's.pillar_education_health',
                's.pillar_economic_dev',
                's.pillar_infra_env',
                's.pillar_socio_culture',
                's.created_at',
                's.updated_at'
            )
            ->get()
            ->map(function ($row) {
                return [
                    $this->orgLink,
                    $this->compName,
                    $row->location ? $row->location . '_OFFICE' : '',   // Location
                    '',
                    '8004319',                                          // Account Style Link
                    'Implementation Cost',                              // Account Style Caption
                    'Default',
                    'Implementation_Cost_' . ($row->location ?? ''),   // Account Number
                    '',
                    '',
                    '',
                    '2025-12-31',                    // Record Start YYYY-MM-DD
                    '2025-12-31',                    // Record End YYYY-MM-DD
                    'Actual',
                    'Standard',
                    'Default',
                    'Overwrite',
                    '',
                    '',
                    $row->total_cost_implementation ?? 0,
                    $row->total_cost_planning ?? 0,
                    $row->pillar_education_health ?? 0,
                    $row->pillar_economic_dev ?? 0,
                    $row->pillar_infra_env ?? 0,
                    $row->pillar_socio_culture ?? 0,
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
            'Total Cost',
            'Plan',
            'Education & Health',
            'Economic Dev',
            'Infrastructure & Envi Dev',
            'Socio Culture & Rel',
        ];
    }
}
