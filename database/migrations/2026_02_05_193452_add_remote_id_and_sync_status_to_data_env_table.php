<?php
// database/migrations/xxxx_xx_xx_add_remote_id_and_sync_status_to_data_env_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_env', function (Blueprint $table) {

            // ESG API unique identifier
            $table->unsignedBigInteger('remote_id')
                  ->nullable()
                  ->after('id');

            // Sync control
            $table->enum('sync_status', [
                'new',        // never synced before
                'synced',     // synced and unchanged
                'updated',    // updated from ESG
                'skipped'     // skipped because unchanged
            ])->default('new')->after('remote_id');

            $table->timestamp('last_synced_at')
                  ->nullable()
                  ->after('sync_status');

            // Indexes for performance
            $table->unique('remote_id');
            $table->index('sync_status');
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('data_env', function (Blueprint $table) {
            $table->dropUnique(['remote_id']);
            $table->dropIndex(['sync_status']);
            $table->dropIndex(['last_synced_at']);
            $table->dropColumn([
                'remote_id',
                'sync_status',
                'last_synced_at'
            ]);
        });
    }
};
