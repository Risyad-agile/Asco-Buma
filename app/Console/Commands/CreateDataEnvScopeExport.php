<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DataEnv;
use App\Models\AccountStyles;
use App\Exports\NormalDataEnvScopeExport;
use App\Exports\SpecialDataEnvScopeExport;
use App\Exports\BiodieselDataEnvScopeExport;
use App\Exports\TotalOBandCoalEnvScopeExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Services\IntegrationLogger;

class CreateDataEnvScopeExport extends Command
{
    protected $signature = 'export:data-env
                            {--limit=500 : Rows per file}
                            {--scope=* : Scope(s) to export, e.g. --scope=1 --scope=2 (default all)}';

    protected $description = 'Export pending DataEnv rows to Excel split by scope (500 rows per file)';

    public function handle(IntegrationLogger $logger)
    {
        $limit  = (int) $this->option('limit');
        $scopes = $this->option('scope');

        if (empty($scopes)) {
            $scopes = [1, 2, 3, 4];
        }

        // ✅ Start run log (DB)
        $run = $logger->start([
            'run_type' => 'ENVIZI_EXPORT',
            'source'   => 'ENVIZI',
        ]);

        $logger->log($run, 'started', 'Envizi export started', [
            'limit' => $limit,
            'scopes' => $scopes,
            'command' => $this->getName(),
        ]);

        $totalFiles = 0;
        $totalRows  = 0;

        try {

            if (!is_dir(storage_path('app/exports'))) {
                mkdir(storage_path('app/exports'), 0775, true);
            }

            foreach ($scopes as $scope) {

                $scope = (int) $scope;
                $batch = 1;

                $logger->log($run, 'scope_started', "Scope {$scope} started", [
                    'scope' => $scope,
                ]);

                while (true) {

                    $rows = DataEnv::query()
                        ->where('export_status', 'pending')
                        ->where('scope', $scope)
                        ->orderBy('id')
                        ->limit($limit)
                        ->get();

                    if ($rows->isEmpty()) {
                        $this->info("✅ Scope {$scope}: no more pending records.");
                        $logger->log($run, 'scope_done', "Scope {$scope} no more pending records", [
                            'scope' => $scope,
                            'batch' => $batch,
                        ]);
                        break;
                    }

                    $ids = $rows->pluck('id')->toArray();

                    $logger->log($run, 'batch_loaded', "Loaded batch rows", [
                        'scope' => $scope,
                        'batch' => $batch,
                        'count' => $rows->count(),
                    ]);

                    // Load account styles for this batch
                    $captions = $rows->pluck('account_style_caption')
                        ->filter()
                        ->unique()
                        ->toArray();

                    $styles = AccountStyles::whereIn('acc_style_caption', $captions)
                        ->where('acc_style_state', 1)
                        ->get()
                        ->keyBy('acc_style_caption');

                    $specialIds = [];
                    $normalIds  = [];
                    $biodieselIds = [];
                    $totalOBandCoalIds = [];

                    foreach ($rows as $row) {
                        $style = $styles[$row->account_style_caption] ?? null;

                        // 🎯 Biodiesel B35
                        if ($row->account_style_caption === 'S1 - Biodiesel B35 - L') {
                            $biodieselIds[] = $row->id;
                        }

                        // 🎯 Total OB & Coal
                        elseif ($row->account_style_caption === 'Total OB Removal and Coal Production (Ton)') {
                            $totalOBandCoalIds[] = $row->id;
                        }
                        // 🔥 EXISTING LOGIC
                        elseif ($style && strtoupper((string) $style->acc_style_xls_format) === 'SPECIAL') {
                            $specialIds[] = $row->id;
                        } else {
                            $normalIds[] = $row->id;
                        }
                    }

                    $logger->log($run, 'batch_split', "Split rows into SPECIAL/NORMAL", [
                        'scope' => $scope,
                        'batch' => $batch,
                        'special_count' => count($specialIds),
                        'normal_count' => count($normalIds),
                    ]);

                    $prefix = rtrim(env('AWS_AGILE_POC_PREFIX', ''), '/') . '/';

                    // ===============================
                    // SPECIAL EXPORT (21 columns)
                    // ===============================
                    if (!empty($specialIds)) {

                        $filename = sprintf(
                            'POC Account Setup and Data Load Special Scope%d %s batch%03d.xlsx',
                            $scope,
                            now()->format('Ymd_His'),
                            $batch
                        );

                        $localPath = 'exports/' . $filename;

                        Excel::store(new SpecialDataEnvScopeExport($specialIds), $localPath, 'local');

                        $this->info("⚙ SPECIAL exported: " . count($specialIds) . " → {$filename}");

                        $logger->log($run, 'file_exported', 'SPECIAL exported locally', [
                            'scope' => $scope,
                            'batch' => $batch,
                            'type'  => 'SPECIAL',
                            'filename' => $filename,
                            'count' => count($specialIds),
                            'localPath' => $localPath,
                        ]);

                        $s3Key = $prefix . $filename;

                        // Storage::disk('s3_agile_poc')->put($s3Key, Storage::disk('local')->get($localPath));
                        // $exists = Storage::disk('s3_agile_poc')->exists($s3Key);

                        // $this->info("☁ Uploaded SPECIAL to Envizi S3: {$s3Key}");

                        // $logger->log($run, 's3_uploaded', 'SPECIAL uploaded to Envizi S3', [
                        //     'scope' => $scope,
                        //     'batch' => $batch,
                        //     'type'  => 'SPECIAL',
                        //     'filename' => $filename,
                        //     's3_key' => $s3Key,
                        //     'count' => count($specialIds),
                        //     'verified' => $exists,
                        // ], $exists ? 'info' : 'warning');

                        $totalFiles++;
                        $totalRows += count($specialIds);
                    }

                    // ===============================
                    // NORMAL EXPORT (13 columns)
                    // ===============================
                    if (!empty($normalIds)) {

                        $filename = sprintf(
                            'POC Account Setup and Data Load Normal Scope%d %s batch%03d.xlsx',
                            $scope,
                            now()->format('Ymd_His'),
                            $batch
                        );

                        $localPath = 'exports/' . $filename;

                        Excel::store(new NormalDataEnvScopeExport($normalIds), $localPath, 'local');

                        $this->info("⚙ NORMAL exported: " . count($normalIds) . " → {$filename}");

                        $logger->log($run, 'file_exported', 'NORMAL exported locally', [
                            'scope' => $scope,
                            'batch' => $batch,
                            'type'  => 'NORMAL',
                            'filename' => $filename,
                            'count' => count($normalIds),
                            'localPath' => $localPath,
                        ]);

                        $s3Key = $prefix . $filename;

                        // Storage::disk('s3_agile_poc')->put($s3Key, Storage::disk('local')->get($localPath));
                        // $exists = Storage::disk('s3_agile_poc')->exists($s3Key);

                        // $this->info("☁ Uploaded NORMAL to Envizi S3: {$s3Key}");

                        // $logger->log($run, 's3_uploaded', 'NORMAL uploaded to Envizi S3', [
                        //     'scope' => $scope,
                        //     'batch' => $batch,
                        //     'type'  => 'NORMAL',
                        //     'filename' => $filename,
                        //     's3_key' => $s3Key,
                        //     'count' => count($normalIds),
                        //     'verified' => $exists,
                        // ], $exists ? 'info' : 'warning');

                        $totalFiles++;
                        $totalRows += count($normalIds);
                    }

                    // ===============================
                    // BIODIESEL EXPORT 
                    // ===============================
                    if (!empty($biodieselIds)) {

                        $filename = sprintf(
                            'Account_Setup_and_Data_Load_S1_Biodiesel_B35_L_%d_%s_batch%03d.xlsx',
                            $scope,
                            now()->format('Ymd_His'),
                            $batch
                        );

                        $localPath = 'exports/' . $filename;

                        Excel::store(new BiodieselDataEnvScopeExport($biodieselIds), $localPath, 'local');

                        $this->info("⚙ BIODIESEL exported: " . count($biodieselIds) . " → {$filename}");

                        $logger->log($run, 'file_exported', 'BIODIESEL exported locally', [
                            'scope' => $scope,
                            'batch' => $batch,
                            'type'  => 'BIODIESEL',
                            'filename' => $filename,
                            'count' => count($biodieselIds),
                            'localPath' => $localPath,
                        ]);

                        $s3Key = $prefix . $filename;

                        // Storage::disk('s3_agile_poc')->put($s3Key, Storage::disk('local')->get($localPath));
                        // $exists = Storage::disk('s3_agile_poc')->exists($s3Key);

                        // $this->info("☁ Uploaded BIODIESEL to Envizi S3: {$s3Key}");

                        // $logger->log($run, 's3_uploaded', 'BIODIESEL uploaded to Envizi S3', [
                        //     'scope' => $scope,
                        //     'batch' => $batch,
                        //     'type'  => 'BIODIESEL',
                        //     'filename' => $filename,
                        //     's3_key' => $s3Key,
                        //     'count' => count($biodieselIds),
                        //     'verified' => $exists,
                        // ], $exists ? 'info' : 'warning');

                        $totalFiles++;
                        $totalRows += count($biodieselIds);
                    }

                    // ===============================
                    // TOTAL OB & COAL EXPORT 
                    // ===============================
                    if (!empty($totalOBandCoalIds)) {

                        $filename = sprintf(
                            'Account_Setup_and_Data_Load_Total_OB_&_Coal_Production_%d_%s_batch%03d.xlsx',
                            $scope,
                            now()->format('Ymd_His'),
                            $batch
                        );

                        $localPath = 'exports/' . $filename;

                        Excel::store(new TotalOBandCoalEnvScopeExport($totalOBandCoalIds), $localPath, 'local');

                        $this->info("⚙ TOTAL OB & COAL exported: " . count($totalOBandCoalIds) . " → {$filename}");

                        $logger->log($run, 'file_exported', 'TOTAL OB & COAL exported locally', [
                            'scope' => $scope,
                            'batch' => $batch,
                            'type'  => 'TOTAL OB & COAL',
                            'filename' => $filename,
                            'count' => count($totalOBandCoalIds),
                            'localPath' => $localPath,
                        ]);

                        $s3Key = $prefix . $filename;

                        // Storage::disk('s3_agile_poc')->put($s3Key, Storage::disk('local')->get($localPath));
                        // $exists = Storage::disk('s3_agile_poc')->exists($s3Key);

                        // $this->info("☁ Uploaded TOTAL OB & COAL to Envizi S3: {$s3Key}");

                        // $logger->log($run, 's3_uploaded', 'TOTAL OB & COAL uploaded to Envizi S3', [
                        //     'scope' => $scope,
                        //     'batch' => $batch,
                        //     'type'  => 'TOTAL OB & COAL',
                        //     'filename' => $filename,
                        //     's3_key' => $s3Key,
                        //     'count' => count($totalOBandCoalIds),
                        //     'verified' => $exists,
                        // ], $exists ? 'info' : 'warning');

                        $totalFiles++;
                        $totalRows += count($totalOBandCoalIds);
                    }

                    // Mark all rows in this batch as exported
                    DataEnv::whereIn('id', $ids)->update([
                        'export_status' => 'exported',
                        'exported_at'   => now(),
                    ]);

                    $this->info("📄 Scope {$scope}: batch {$batch} completed.");

                    $logger->log($run, 'batch_completed', "Scope {$scope} batch {$batch} completed", [
                        'scope' => $scope,
                        'batch' => $batch,
                        'count' => count($ids),
                    ]);

                    $batch++;
                }
            }

            // ✅ Finish run success
            $logger->success($run, [
                'total_files' => $totalFiles,
                'total_rows'  => $totalRows,
            ]);

            $logger->log($run, 'finished', 'Envizi export finished successfully', [
                'total_files' => $totalFiles,
                'total_rows'  => $totalRows,
            ]);

            return self::SUCCESS;
        } catch (\Throwable $e) {

            // ✅ Failure stored in DB (and includes trace in logger->fail)
            $logger->fail($run, $e);

            $this->error("❌ Export failed: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
