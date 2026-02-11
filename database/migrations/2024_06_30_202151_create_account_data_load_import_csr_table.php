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
        Schema::create('account_data_load_import_csr', function (Blueprint $table) {
            $table->increments('id'); 
            $table->integer('comp_id')->unsigned()->default(0);   
            $table->bigInteger('task_id')->unsigned()->default(0); 
            $table->string('organization_name', 100)->nullable();
            $table->string('location_name', 100)->nullable();  
            $table->string('acc_style_caption', 100)->nullable();   
            $table->date('record_date_start')->nullable();
            $table->date('record_date_end')->nullable(); 
            $table->integer('csr_total')->unsigned()->nullable()->default(0); 
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
        Schema::dropIfExists('account_data_load_import_csr');
    }
};
 