<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\APISyncronize;

class SyncLocations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync locations data from API Provider to local DB';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Mulai sync locations...');

        // Panggil controller
        $sync = app(APISyncronize::class)->syncLocations();

        if ($sync->getData()->status === 'success') {
            $this->info('Sync selesai. Total data: ' . $sync->getData()->count);
            return Command::SUCCESS;
        }

        $this->error('Sync gagal.');
        return Command::FAILURE;
    }
}
