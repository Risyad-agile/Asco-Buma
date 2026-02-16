<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunDailyEnviziPipeline extends Command
{
    protected $signature = 'pipeline:envizi-daily';
    protected $description = 'Run full BUMA → Export → Cleanup pipeline';

    public function handle()
    {
        $this->info("🚀 Starting daily Envizi pipeline...");

        $commands = [
            'sync:buma-scope 1 --org=BIG --pageSize=100',
            'sync:buma-scope 2 --org=BIG --pageSize=100',
            'sync:buma-scope 3 --org=BIG --pageSize=100',
            'export:data-env --scope=1 --limit=1000',
            'export:data-env --scope=2 --limit=1000',
            'export:data-env --scope=3 --limit=1000',
            'exports:cleanup',
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

        $this->info("✅ Daily Envizi pipeline completed successfully.");
        return self::SUCCESS;
    }
}
