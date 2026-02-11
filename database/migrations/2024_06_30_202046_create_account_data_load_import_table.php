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
        Schema::create('account_data_load_import', function (Blueprint $table) {
            $table->increments('id'); 
            $table->integer('comp_id')->unsigned()->default(0);  
            $table->bigInteger('task_id')->unsigned()->default(0); 
            $table->string('organization_name', 100)->nullable();
            $table->string('location_name', 100)->nullable();
            $table->string('acc_style_caption', 100)->nullable();
            $table->string('acc_number', 100)->nullable();
            $table->string('acc_reference', 50)->nullable();
            $table->string('acc_supplier', 50)->nullable();
            $table->date('record_date_start')->nullable();
            $table->date('record_date_end')->nullable();
            $table->string('record_quality', 30)->nullable();
            $table->double('acc_data_tot_cost', 16, 2)->nullable()->default(0);
            $table->string('record_reference', 50)->nullable();
            $table->string('record_inv_no', 50)->nullable();
            $table->double('acc_data_qty', 12, 2)->nullable()->default(0);
            $table->char('acc_data_state', 1)->default('1');
            $table->timestamps();
        });
    }
  

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_data_load_import');
    }
};
