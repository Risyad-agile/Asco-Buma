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
        Schema::create('syncronize', function (Blueprint $table) {
            $table->id();
            $table->integer('comp_id')->unsigned()->default(0); 
            $table->integer('connect_id')->unsigned()->default(0);  
            $table->string('sync_task_name', 100)->nullable();
            $table->string('sync_state_note', 100)->nullable(); 
            $table->dateTime('sync_time')->nullable();
            $table->char('sync_state', 1)->default('1'); //1:succes 2:fail
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syncronize');
    }
};
