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
        Schema::create('organizations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('org_link', 50)->nullable();
            $table->string('org_name', 100)->nullable();
            $table->string('org_name_import', 100)->nullable();
            $table->string('org_group_type', 50)->nullable();
            $table->string('org_group_hierarchy_name', 100)->nullable();
            $table->string('org_group_name_1', 50)->nullable();
            $table->string('org_group_name_2', 50)->nullable();
            $table->string('org_group_name_3', 50)->nullable();
            $table->string('org_location_name', 50)->nullable();
            $table->string('org_location_ref', 50)->nullable();
            $table->string('org_location_ref_no', 50)->nullable();
            $table->string('org_street_address', 100)->nullable();
            $table->string('org_city', 50)->nullable();
            $table->string('org_state_province', 50)->nullable();
            $table->string('org_postal_code', 10)->nullable();
            $table->string('org_country', 50)->nullable();
            $table->string('org_latitude_y', 30)->nullable();
            $table->string('org_longtitude_x', 30)->nullable();
            $table->date('org_location_close_date')->nullable();
            $table->char('org_state', 1)->default(1);  //0=non active 1=active
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
