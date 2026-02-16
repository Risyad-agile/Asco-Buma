<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connector_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('connector_source_id');
            $table->string('job_name')->nullable();
            $table->string('schedule_type')->default('daily');
            $table->time('schedule_time')->nullable();
            $table->string('days_of_week')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connector_jobs');
    }
};
