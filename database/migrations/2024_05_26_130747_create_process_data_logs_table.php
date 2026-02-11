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
        Schema::create('process_data_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('comp_id')->unsigned()->default(0); 
            $table->integer('connect_id')->unsigned()->default(0); 
            $table->string('process_data_type', 10)->nullable(); //RECEIVE RETRIEVE
            $table->string('process_data_origin', 50)->nullable(); //client api get, client ftp, client ftp receive,client upload
            $table->string('process_data_tittle', 100)->nullable(); //judul proses pada process data
            $table->string('process_data_note')->nullable(); //catatan proses
            $table->string('process_data_process_state', 10)->nullable(); //RECEIVE SEND
            $table->dateTime('process_data_process_time')->nullable();
            $table->string('process_data_describe_state')->nullable(); 
            $table->char('process_data_state', 1)->default('1'); //receive, send
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_data_logs');
    }
};
