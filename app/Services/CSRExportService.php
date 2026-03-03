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
        $date = Carbon::now('Asia/Jakarta')->format('Ymd');

        $exports = [
            \App\Exports\EnviziDirectExport::class          => "Account_Setup_and_Data_Load direct_{$date}.xlsx", 
            \App\Exports\EnviziIndirectExport::class        => "Account_Setup_and_Data_Load indirect_{$date}.xlsx", 
            \App\Exports\EnviziMidMGTExport::class          => "Account_Setup_and_Data_Load Mid Mngmt_{$date}.xlsx", 
            \App\Exports\EnviziPermanentExport::class       => "Account_Setup_and_Data_Load Permanent_{$date}.xlsx", 
            \App\Exports\EnviziSeniorMGTExport::class       => "Account_Setup_and_Data_Load Senior mngmt_{$date}.xlsx",
            \App\Exports\EnviziStaffExport::class           => "Account_Setup_and_Data_Load Staff_{$date}.xlsx",
            \App\Exports\EnviziDeathExport::class          => "Account_Setup_and_Data_Load TO death_{$date}.xlsx",
            \App\Exports\EnviziOthersExport::class         => "Account_Setup_and_Data_Load TO Others_{$date}.xlsx",
            \App\Exports\EnviziResignmentExport::class      => "Account_Setup_and_Data_Load TO Resign_{$date}.xlsx",
            \App\Exports\EnviziRetireExport::class         => "Account_Setup_and_Data_Load TO Retire_{$date}.xlsx",
            \App\Exports\EnviziSHEExport::class         => "Account_Setup_and_Data_Load SHE_{$date}.xlsx",
            \App\Exports\EnviziEmployeeExport::class        => "Account_Setup_and_Data_Load Employee total_{$date}.xlsx",
            \App\Exports\EnviziTOCEduExport::class        => "Account_Setup_and_Data_Load TOC - edu_{$date}.xlsx",
            \App\Exports\EnviziTOCLifeliLIMExport::class        => "Account_Setup_and_Data_Load TOC - Lifeli LIM_{$date}.xlsx",
            \App\Exports\EnviziTOCLiveliEduLiExport::class        => "Account_Setup_and_Data_Load TOC - Liveli Edu Li_{$date}.xlsx",
            \App\Exports\EnviziTOCLiveliLBMExport::class        => "Account_Setup_and_Data_Load TOC - liveli LBM_{$date}.xlsx",
            \App\Exports\EnviziTotalPeCatExport::class        => "Account_Setup_and_Data_Load total pe cat_{$date}.xlsx",

            // tambahkan 12 lainnya di sini
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