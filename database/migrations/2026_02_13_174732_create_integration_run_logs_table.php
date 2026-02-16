<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('integration_run_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration_run_id')
                ->constrained('integration_runs')
                ->cascadeOnDelete();
        
            $table->string('level')->default('info');   // info|warning|error
            $table->string('event');                    // e.g. page_fetched, upserted, file_exported, s3_uploaded
            $table->unsignedTinyInteger('scope')->nullable();
            $table->unsignedInteger('page')->nullable();
            $table->unsignedInteger('batch')->nullable();
            $table->string('type')->nullable();         // NORMAL|SPECIAL
            $table->string('filename')->nullable();
            $table->string('s3_key')->nullable();
            $table->unsignedInteger('count')->nullable(); // rows/files/etc
        
            $table->text('message');
            $table->json('context')->nullable();
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_run_logs');
    }
};
