<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BumaScopeService;
use App\Services\IntegrationLogger;

class SyncBumaScope extends Command
{
    protected $signature = 'sync:buma-scope
                            {scope : 1|2|3|4}
                            {--org=BIG : Organization code}
                            {--pageSize=100 : Page size}';

    protected $description = 'Sync BUMA ESG scope data (scope 1/2/3) into data_env';

    public function handle(BumaScopeService $service, IntegrationLogger $logger)
    {
        $scope    = (int) $this->argument('scope');
        $org      = (string) $this->option('org');
        $pageSize = (int) $this->option('pageSize');

        $this->info("🔄 Syncing BUMA Scope {$scope} for {$org} (pageSize={$pageSize})");

        // ✅ Start DB run log
        $run = $logger->start([
            'run_type'     => 'BUMA_SYNC',
            'source'       => 'BUMA',
            'scope'        => $scope,
            'organization' => $org,
        ]);

        $logger->log($run, 'started', 'BUMA sync started', [
            'scope' => $scope,
            'organization' => $org,
            'pageSize' => $pageSize,
            'command' => $this->getName(),
        ]);

        try {
            $result = $service->syncScope($scope, $org, $pageSize);

            // ✅ Success summary to DB
            $logger->log($run, 'summary', 'BUMA sync summary', [
                'scope' => $scope,
                'organization' => $org,
                'pagesSynced' => $result['pagesSynced'] ?? null,
                'inserted' => $result['inserted'] ?? 0,
                'updated'  => $result['updated'] ?? 0,
                'skipped'  => $result['skipped'] ?? 0,
            ]);

            $logger->success($run, [
                'total_pages' => (int) ($result['pagesSynced'] ?? 0),
                'total_rows'  => (int) (($result['inserted'] ?? 0) + ($result['updated'] ?? 0) + ($result['skipped'] ?? 0)),
            ]);

        } catch (\Throwable $e) {

            // ✅ Failure to DB (stores message + exception + trace via IntegrationLogger::fail)
            $logger->fail($run, $e);

            $this->error("❌ Sync failed: " . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("✅ Done");
        $this->line("Pages   : {$result['pagesSynced']}");
        $this->line("Inserted: {$result['inserted']}");
        $this->line("Updated : {$result['updated']}");
        $this->line("Skipped : {$result['skipped']}");

        return self::SUCCESS;
    }
}
