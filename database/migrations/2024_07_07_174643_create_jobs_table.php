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
        Schema::create('jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('comp_id')->unsigned()->default(0);  
            $table->integer('connect_id')->unsigned()->default(0);  
            $table->string('job_name', 100)->nullable();
            $table->string('job_interval', 10)->nullable(); //HOURLY DAILY WEEKLY MONTHLY 
            $table->string('job_repeating_date', 3)->nullable(); //01 02 03 ... 30 31
            $table->string('job_repeating_day', 10)->nullable();  //SUNDAY MONDAY...SATURDAY
            $table->time('job_execute_time')->nullable();    
            $table->char('job_state', 1)->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
