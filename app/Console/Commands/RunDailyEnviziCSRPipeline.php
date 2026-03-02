<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunDailyEnviziCSRPipeline extends Command
{
    protected $signature = 'pipeline:envizi-csr-daily {companyId}';
    protected $description = 'Run full CSR → Export Direct pipeline';

    public function handle()
    {
        $companyId = $this->argument('companyId');

        $this->info("🚀 Starting CSR Envizi pipeline for Company ID: {$companyId}");

        $commands = [
            "csr:fetch {$companyId}",
            "csr:export-direct {$companyId}",
        ];

        foreach ($commands as $cmd) {
            $this->info("➡ Running: {$cmd}");

            $exitCode = Artisan::call($cmd);
            $this->line(Artisan::output());

            if ($exitCode !== 0) {
                $this->error("❌ Command failed: {$cmd}");
                return self::FAILURE;
            }
        }

        $this->info("✅ CSR Envizi pipeline completed successfully.");
        return self::SUCCESS;
    }
}