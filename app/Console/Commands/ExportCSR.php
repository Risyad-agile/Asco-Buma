<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConnectorService;
use Illuminate\Support\Facades\Storage;
use App\Services\IntegrationLogger;

class ExportCSR extends Command
{
    protected $signature = 'export:data-csr {companyId}';
    protected $description = 'Export CSR Direct data to Envizi format';
    // public function handle(\App\Services\IntegrationLogger $logger)

    public function handle(IntegrationLogger $logger)
    {
        $run = $logger->start([
            'run_type' => 'CSR_EXPORT',
            'source'   => 'CSR',
        ]);

        $this->info("Start CSR Pipeline...");

        // $companyId = 2; // sementara hardcode dulu
        $companyId = $this->argument('companyId');
        // ==============================
        // 1. CONNECT & FETCH API
        // ==============================
        $connector = app(\App\Services\ConnectorService::class);

        $this->info("Fetching CSR...");
        $dta_csr = $connector->fetchCSRFromSource($companyId);

        $this->info("Fetching TRN...");
        $dta_trn = $connector->fetchTRNFromSource($companyId);

        try {
            $this->info("Fetching TOC...");
            $dta_toc = $connector->fetchTOCFromSource($companyId);
            // dd($dta_toc);
        } catch (\Exception $e) {
            $this->warn("TOC skipped: " . $e->getMessage());
            $dta_toc = [];
        }

        try {
            $this->info("Fetching SHE...");
            $dta_she = $connector->fetchSHEFromSource($companyId);
        } catch (\Exception $e) {
            $this->warn("SHE skipped: " . $e->getMessage());
            $dta_she = [];
        }

        $logger->log($run, 'fetch_csr', 'Fetch CSR success');

        // ==============================
        // 2. SYNC KE DATABASE
        // ==============================
        $csrService = app(\App\Services\CSRService::class);

        $this->info("Sync CSR...");
        $csrService->syncCSR($dta_csr, $companyId);

        $this->info("Sync TRN...");
        $csrService->syncTRN($dta_trn, $companyId);

        $this->info("Sync TOC...");
        $csrService->syncTOC($dta_toc, $companyId);

        $this->info("Sync SHE...");
        $csrService->syncSHE($dta_she, $companyId);

        $logger->log($run, 'sync_csr', 'Sync CSR success');

        // ==============================
        // 3. GENERATE EXCEL
        // ==============================
        $this->info("Generating Excel...");

        $export = app(\App\Services\CSRExportService::class);
        $files = $export->generateAll($companyId);
        $logger->log($run, 'generate_excel', 'Generate Excel success', [
            'total_files' => count($files),
        ]);

        // ==============================
        // 4. UPLOAD TO S3
        // ==============================
        foreach ($files as $filePath) {
            $fileName = basename($filePath);

            if (!Storage::disk('local')->exists($filePath)) {
                $this->error("File tidak ditemukan: $filePath");
                continue;
            }

            $content = Storage::disk('local')->get($filePath);

            if ($content === false) {
                $this->error("Failed read: $fileName");
                continue;
            }

            try {
                $prefix = rtrim(env('AWS_AGILE_POC_PREFIX', ''), '/') . '/';
                $s3Key = $prefix . $fileName;

                Storage::disk('s3_agile_poc')->put($s3Key, $content);

                $logger->log($run, 's3_uploaded', 'File uploaded to S3', [
                    'filename' => $fileName,
                    's3_key' => $s3Key
                ]);

                $this->info("☁ Uploaded: $fileName");
            } catch (\Throwable $e) {
                $logger->log($run, 's3_failed', 'Upload failed', [
                    'filename' => $fileName,
                    'error' => $e->getMessage(),
                ], 'error');

                $this->error("❌ Upload failed: $fileName → " . $e->getMessage());
            }
        }
        $logger->success($run, [
            'total_files' => count($files),
        ]);

        $this->info("CSR Pipeline Done.");
    }
}
