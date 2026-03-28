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
            \App\Exports\EnviziDirectExport::class     => "Account_Setup_and_Data_Load direct_{$date}.xlsx", 
            \App\Exports\EnviziIndirectExport::class   => "Account_Setup_and_Data_Load indirect_{$date}.xlsx", 
            \App\Exports\EnviziMidMGTExport::class     => "Account_Setup_and_Data_Load Mid Mngmt_{$date}.xlsx", 
            \App\Exports\EnviziPermanentExport::class  => "Account_Setup_and_Data_Load Permanent_{$date}.xlsx", 
            \App\Exports\EnviziSeniorMGTExport::class  => "Account_Setup_and_Data_Load Senior mngmt_{$date}.xlsx",
            \App\Exports\EnviziStaffExport::class      => "Account_Setup_and_Data_Load Staff_{$date}.xlsx",
            \App\Exports\EnviziDeathExport::class      => "Account_Setup_and_Data_Load TO death_{$date}.xlsx",
            \App\Exports\EnviziOthersExport::class     => "Account_Setup_and_Data_Load TO Others_{$date}.xlsx",
            \App\Exports\EnviziResignmentExport::class => "Account_Setup_and_Data_Load TO Resign_{$date}.xlsx",
            \App\Exports\EnviziRetireExport::class     => "Account_Setup_and_Data_Load TO Retire_{$date}.xlsx",
            \App\Exports\EnviziLevel4Export::class     => "Account_Setup_and_Data_Load Level4_{$date}.xlsx",
            \App\Exports\EnviziLevel5Export::class     => "Account_Setup_and_Data_Load Level5_{$date}.xlsx",
            \App\Exports\EnviziLevel6Export::class     => "Account_Setup_and_Data_Load Level6_{$date}.xlsx",
            \App\Exports\EnviziLevel7Export::class     => "Account_Setup_and_Data_Load Level7_{$date}.xlsx",
            \App\Exports\EnviziLevel8Export::class     => "Account_Setup_and_Data_Load Level8_{$date}.xlsx",
            \App\Exports\EnviziBlueCollarExport::class => "Account_Setup_and_Data_Load BlueCollar_{$date}.xlsx",
            //BaseCSRExportFormat2
            \App\Exports\EnviziTotalEmployeeExport::class  => "Account_Setup_and_Data_Load Employee total_{$date}.xlsx",
            //BaseCSRExportFormat3
            \App\Exports\EnviziSHEExport::class            => "Account_Setup_and_Data_Load SHE_{$date}.xlsx",
            //BaseCSRExportFormatCat
            \App\Exports\EnviziTotalPeCatExport::class     => "Account_Setup_and_Data_Load total pe cat_{$date}.xlsx",
            //BaseCSRExportFormatTRN
            \App\Exports\EnviziTrainingExport::class       => "Account_Setup_and_Data_Load_Training_{$date}.xlsx",
            \App\Exports\EnviziTrainingTotalExport::class  => "Account_Setup_and_Data_Load_TrainingTotal_{$date}.xlsx",
            //BaseCSRExportFormatTOC 
            \App\Exports\EnviziTOCEduExport::class         => "Account_Setup_and_Data_Load TOC - edu_{$date}.xlsx",
            \App\Exports\EnviziTOCLifeliLIMExport::class   => "Account_Setup_and_Data_Load TOC - Lifeli LIM_{$date}.xlsx",
            \App\Exports\EnviziTOCSocLicOpExport::class    => "Account_Setup_and_Data_Load TOC - Social LicOp_{$date}.xlsx",
            \App\Exports\EnviziTOCLiveliLBMExport::class   => "Account_Setup_and_Data_Load TOC - liveli LBM_{$date}.xlsx",

        ];

        $generatedFiles = [];

        foreach ($exports as $exportClass => $fileName) {

            try {

                Excel::store(
                    new $exportClass($companyId),
                    $fileName,
                    'local'
                );

                $generatedFiles[] = storage_path("app/{$fileName}");

                Log::info("✅ Generated: {$fileName}");

            } catch (\Exception $e) {

                Log::error("❌ Failed export: {$fileName}", [
                    'message' => $e->getMessage()
                ]);
            }
        }

        return $generatedFiles;
    }
}