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
        Schema::create('account_data_load', function (Blueprint $table) {
            $table->id();
            $table->integer('org_id')->unsigned(); 
            $table->bigInteger('loc_id')->unsigned(); 
            $table->integer('acc_style_id')->unsigned(); 
            $table->integer('comp_id')->unsigned()->default(0);   
            $table->bigInteger('task_id')->unsigned()->default(0); 
            $table->string('organization_link', 50)->nullable();
            $table->string('organization_name', 100)->nullable();
            $table->string('location_name', 100)->nullable();
            $table->string('location_ref', 50)->nullable();
            $table->string('acc_style_link', 50)->nullable();
            $table->string('acc_style_caption', 100)->nullable();
            $table->string('acc_subtype', 50)->nullable();
            $table->string('acc_number', 100)->nullable();
            $table->string('acc_reference', 50)->nullable();
            $table->string('acc_supplier', 50)->nullable();
            $table->string('acc_reader', 50)->nullable();
            $table->double('acc_data_qty', 12, 2)->nullable()->default(0);
            $table->double('acc_data_tot_cost', 16, 2)->nullable()->default(0);
            $table->date('record_date_start')->nullable();
            $table->date('record_date_end')->nullable();
            $table->string('record_quality', 30)->nullable();
            $table->string('record_billing_type', 30)->nullable();
            $table->string('record_subtype', 30)->nullable();
            $table->string('record_entry_method', 30)->nullable();
            $table->string('record_reference', 50)->nullable();
            $table->string('record_inv_no', 50)->nullable();
            $table->char('acc_data_state', 1)->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_data_load');
    }
};
