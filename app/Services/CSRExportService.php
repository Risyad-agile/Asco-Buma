<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CSRExportService
{
    protected ConnectorService $connector;

    public function __construct(ConnectorService $connector)
    {
        $this->connector = $connector;
    }

    /**
     * STEP 3
     * Generate 16 CSR Excel files
     */
    public function generateAll(int $companyId): array
    {
        $this->connector->setDynamicConnection($companyId);
        $date = Carbon::now('Asia/Jakarta')->format('Y');

        $exports = [
            //BaseCSRExportFormat1 
            \App\Exports\EnviziDirectExport::class     => "Account_Setup_and_Data_Load_Direct_{$date}.xlsx",
            \App\Exports\EnviziIndirectExport::class   => "Account_Setup_and_Data_Load_Indirect_{$date}.xlsx",
            \App\Exports\EnviziLevel4Export::class     => "Account_Setup_and_Data_Load_Level_4_{$date}.xlsx",
            \App\Exports\EnviziLevel5Export::class     => "Account_Setup_and_Data_Load_Level_5_{$date}.xlsx",
            \App\Exports\EnviziLevel6Export::class     => "Account_Setup_and_Data_Load_Level_6_{$date}.xlsx",
            \App\Exports\EnviziLevel7Export::class     => "Account_Setup_and_Data_Load_Level_7_{$date}.xlsx",
            \App\Exports\EnviziLevel8Export::class     => "Account_Setup_and_Data_Load_Level_8_{$date}.xlsx",
            \App\Exports\EnviziBlueCollarExport::class => "Account_Setup_and_Data_Load_Blue_Collar_{$date}.xlsx",
            \App\Exports\EnviziStaffExport::class      => "Account_Setup_and_Data_Load_Staff_{$date}.xlsx",
            \App\Exports\EnviziMidMGTExport::class     => "Account_Setup_and_Data_Load_Middle_Management_{$date}.xlsx",
            \App\Exports\EnviziPermanentExport::class  => "Account_Setup_and_Data_Load_Permanent_{$date}.xlsx",
            \App\Exports\EnviziSeniorMGTExport::class  => "Account_Setup_and_Data_Load_Senior_Management_{$date}.xlsx",

            //BaseCSRExportTurnOverFormat
            \App\Exports\EnviziDeathExport::class      => "Account_Setup_and_Data_Load_Turnover_Death_{$date}.xlsx",
            \App\Exports\EnviziOthersExport::class     => "Account_Setup_and_Data_Load_Turnover_Others_{$date}.xlsx",
            \App\Exports\EnviziResignmentExport::class => "Account_Setup_and_Data_Load_Turnover_Resign_{$date}.xlsx",
            \App\Exports\EnviziRetireExport::class     => "Account_Setup_and_Data_Load_Turnover_Retirement_{$date}.xlsx",

            //BaseCSRExportFormatTRN
            \App\Exports\EnviziTrainingExport::class       => "Account_Setup_and_Data_Load_Training_{$date}.xlsx",

            //BaseCSRExportFormatTRNT
            \App\Exports\EnviziTrainingTotalExport::class  => "Account_Setup_and_Data_Load_Training_Total_{$date}.xlsx",

            //BaseCSRExportFormatCat
            \App\Exports\EnviziTotalPeCatExport::class     => "Account_Setup_and_Data_Load_total_per_cat_{$date}.xlsx",

            //BaseCSRExportFormatSocialLTO
            \App\Exports\EnviziTOCSocialLOExport::class    => "Account_Setup_and_Data_Load_Social_License_To_Operate_{$date}.xlsx",

            //BaseCSRExportFormat3
            \App\Exports\EnviziSHEExport::class            => "Account_Setup_and_Data_Load_Safety_Health_Environment_{$date}.xlsx",

            //BaseCSRExportFormatLIM
            \App\Exports\EnviziTOCLifeliLIMExport::class   => "Account_Setup_and_Data_Load_Life_in_Mining_{$date}.xlsx",

            //BaseCSRExportFormatLBM
            \App\Exports\EnviziTOCLiveliLBMExport::class   => "Account_Setup_and_Data_Load_Life_Beyond_Mining_{$date}.xlsx",

            //BaseCSRExportFormatImplementationCost
            \App\Exports\EnviziImplementationCostExport::class   => "Account_Setup_and_Data_Load_Implementation_Cost_{$date}.xlsx",

            //BaseCSRExportFormat2
            \App\Exports\EnviziTotalEmployeeExport::class  => "Account_Setup_and_Data_Load_Employee_total_{$date}.xlsx",

            //BaseCSRExportFormatTOC 
            \App\Exports\EnviziTOCEduExport::class         => "Account_Setup_and_Data_Load_Access_and_Quality_{$date}.xlsx",

            //BaseCSRExportFormatSocialImpact
            \App\Exports\BaseCSRExportFormatSocialImpact::class   => "Account_Setup_and_Data_Load_Social_Impact_{$date}.xlsx",

            // Same format
            // \App\Exports\EnviziTOCWellbeingExport::class   => "Account_Setup_and_Data_Load_Wellbeing_{$date}.xlsx",
        ];

        $generatedFiles = [];

        foreach ($exports as $exportClass => $fileName) {
            try {
                // $cleanFileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
                $folder = 'csr_exports/';
                $localPath = $folder . $fileName;

                Excel::store(
                    new $exportClass($companyId),
                    $localPath,
                    'local'
                );

                $generatedFiles[] = $localPath;

                Log::info("✅ Generated: {$localPath}");
            } catch (\Throwable $e) {
                Log::error("❌ Export failed: {$exportClass} → " . $e->getMessage());
                if (str_contains($e->getMessage(), 'acc_style_caption')) {
                    dd("Failed class: {$exportClass}", $e->getMessage());
                }
            }
        }

        return $generatedFiles;
    }
}
