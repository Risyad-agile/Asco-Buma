<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Http\Controllers\UserMigrationController;

class UserMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:user-migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengubah dan memindahkan user dari Laravel 5 ke Laravel 10';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        #di eksekusi setelah migrasi secara manual, rename role dan permsion yang ada
        #jalankan agile_user_migration
        #eksekusi command ini
        $usermigrationcontrol=new UserMigrationController;
        $usermigrationcontrol->assignUserRole();
    }
}
