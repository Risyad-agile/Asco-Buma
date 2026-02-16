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
        Schema::create('integration_runs', function (Blueprint $table) {
            $table->id();
        
            $table->string('run_type');                 // BUMA_SYNC | ENVIZI_EXPORT
            $table->string('source')->nullable();       // BUMA | ENVIZI | SYSTEM
            $table->string('status')->default('running'); // running|success|failed
        
            $table->unsignedTinyInteger('scope')->nullable();
            $table->string('organization')->nullable();
        
            $table->unsignedInteger('total_pages')->default(0);
            $table->unsignedBigInteger('total_rows')->default(0);
            $table->unsignedInteger('total_files')->default(0);
        
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('error_message')->nullable();
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_runs');
    }
};
