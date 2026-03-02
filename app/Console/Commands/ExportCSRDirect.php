<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ConnectorService;

class ExportCSRDirect extends Command
{
    protected $signature = 'csr:export-direct {companyId}';
    protected $description = 'Export CSR Direct data to Envizi format';

    public function handle()
    {
        $companyId = $this->argument('companyId');

        $this->info("🚀 Starting CSR Direct Export for Company ID: {$companyId}");

        try {

            $service = app(ConnectorService::class);

            $result = $service->exportDirect($companyId);

            $this->info("✅ Export success!");
            $this->info("File: " . $result['file']);

        } catch (\Exception $e) {

            $this->error("❌ Export failed: " . $e->getMessage());
        }
    }
}