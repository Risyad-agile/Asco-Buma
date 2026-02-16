<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    
    protected function schedule(Schedule $schedule): void
    {  
        // Tiap jam (awal jam)
        // $schedule->command('sync:locations')->hourly();

        // Tiap 5 menit
        // $schedule->command('sync:locations')->everyFiveMinutes();

        // Jam tertentu, misalnya jam 2 pagi
        // $schedule->command('sync:locations')->dailyAt('06:05');

        // Setiap hari Senin jam 07:30
        // $schedule->command('sync:locations')->weeklyOn(1, '07:30');

        // Setiap tanggal 1 jam 01:00
        // $schedule->command('sync:locations')->monthlyOn(1, '01:00');
 
        // $schedule->call(function () {
        //     app(\App\Http\Controllers\Api\APISyncronize::class)->syncLocations();
        // })->hourly();

        $schedule->command('pipeline:envizi-daily')
            ->dailyAt('21:00')
            ->withoutOverlapping()
            ->onOneServer() 
            ->appendOutputTo(storage_path('logs/envizi_pipeline.log'));
        
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
