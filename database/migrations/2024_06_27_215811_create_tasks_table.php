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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('comp_id')->unsigned(); 
            $table->string('task_name', 100)->nullable();
            $table->string('task_maker_name', 100)->nullable();
            $table->string('task_maker_message')->nullable();
            $table->dateTime('task_maker_time')->nullable();
            $table->string('task_checker_name', 100)->nullable();
            $table->string('task_checker_message')->nullable();
            $table->dateTime('task_checker_time')->nullable();
            $table->string('task_approval_name', 100)->nullable();
            $table->string('task_approval_message')->nullable();
            $table->dateTime('task_approval_time')->nullable();
            $table->string('task_final_state')->nullable();
            $table->string('task_file_name')->nullable();
            $table->string('task_progress',5)->nullable(); //0,30%, 65%,100%
            $table->string('task_last_message')->nullable();
            $table->string('task_approval_type', 10)->nullable();  //OVERIDE LEVELING 
            $table->char('task_state', 1)->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
