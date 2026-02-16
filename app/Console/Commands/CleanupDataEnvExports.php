<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Services\IntegrationLogger;

class CleanupDataEnvExports extends Command
{
    protected $signature = 'exports:cleanup 
                            {--days=7 : Delete files older than X days}';

    protected $description = 'Delete old Excel export files from storage/app/exports';

    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $files = Storage::disk('local')->files('exports');

        if (empty($files)) {
            $this->info('No files found.');
            return self::SUCCESS;
        }

        $deleted = 0;

        foreach ($files as $file) {

            // Only delete XLSX files
            if (!str_ends_with($file, '.xlsx')) {
                continue;
            }

            $lastModified = Carbon::createFromTimestamp(
                Storage::disk('local')->lastModified($file)
            );

            if ($lastModified->lt($cutoff)) {
                Storage::disk('local')->delete($file);
                $deleted++;
                $this->info("Deleted: {$file}");
            }
        }

        $this->info("Cleanup completed. {$deleted} file(s) deleted.");

        return self::SUCCESS;
    }
}
