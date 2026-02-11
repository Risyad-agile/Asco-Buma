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
        Schema::create('account_data_load_csr', function (Blueprint $table) {
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
            $table->double('acc_data_qty', 8, 2)->nullable()->default(0);
            $table->double('acc_data_tot_cost', 16, 2)->nullable()->default(0);
            $table->date('record_date_start')->nullable();
            $table->date('record_date_end')->nullable();
            $table->string('record_quality', 30)->nullable();
            $table->string('record_billing_type', 30)->nullable();
            $table->string('record_subtype', 30)->nullable();
            $table->string('record_entry_method', 30)->nullable();
            $table->string('record_reference', 50)->nullable();
            $table->string('record_inv_no', 50)->nullable();
            $table->integer('csr_total')->unsigned()->nullable()->default(0);
            // $table->integer('csr_management')->unsigned()->nullable()->default(0);
            // $table->integer('csr_non_management')->unsigned()->nullable()->default(0);
            // $table->integer('csr_permanent')->unsigned()->nullable()->default(0);
            // $table->integer('csr_contract')->unsigned()->nullable()->default(0);
            // $table->integer('csr_direct')->unsigned()->nullable()->default(0);
            // $table->integer('csr_indirect')->unsigned()->nullable()->default(0);
            $table->integer('csr_male')->unsigned()->nullable()->default(0);
            $table->integer('csr_female')->unsigned()->nullable()->default(0);
            $table->integer('csr_less_30')->unsigned()->nullable()->default(0);
            $table->integer('csr_between_30_50')->unsigned()->nullable()->default(0);
            $table->integer('csr_more_50')->unsigned()->nullable()->default(0);
            $table->integer('csr_phd')->unsigned()->nullable()->default(0);
            $table->integer('csr_post_graduate')->unsigned()->nullable()->default(0);
            $table->integer('csr_bachelor_degree')->unsigned()->nullable()->default(0);
            $table->integer('csr_high_school')->unsigned()->nullable()->default(0);
            $table->integer('csr_junior_high_school')->unsigned()->nullable()->default(0);
            $table->integer('csr_elementary_school')->unsigned()->nullable()->default(0);
            $table->integer('csr_eduction_other')->unsigned()->nullable()->default(0);
            $table->integer('csr_islam')->unsigned()->nullable()->default(0);
            $table->integer('csr_budha')->unsigned()->nullable()->default(0);
            $table->integer('csr_hindu')->unsigned()->nullable()->default(0);
            $table->integer('csr_katolik')->unsigned()->nullable()->default(0);
            $table->integer('csr_kristen')->unsigned()->nullable()->default(0);
            $table->integer('csr_religion_other')->unsigned()->nullable()->default(0);
            $table->char('csr_state', 1)->default(1);  //0=non active 1=active
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_data_load_csr');
    }
};
