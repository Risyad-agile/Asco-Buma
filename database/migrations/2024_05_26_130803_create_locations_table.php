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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->integer('comp_id')->unsigned();
            $table->foreign('comp_id')->references('id')->on('companies');
            $table->integer('org_id')->unsigned();
            $table->foreign('org_id')->references('id')->on('organizations');
            $table->string('location_type', 50)->nullable();
            $table->string('location_name', 100)->nullable();
            $table->string('location_name_import', 100)->nullable();
            $table->string('location_country', 50)->nullable();
            $table->string('location_region', 50)->nullable();
            $table->string('location_id', 50)->nullable();
            $table->string('location_reff_no', 50)->nullable();
            $table->string('location_reff', 50)->nullable();
            $table->string('location_billing_address', 100)->nullable();
            $table->string('location_note', 100)->nullable();
            $table->char('location_state', 1)->default(1);  //0=non active 1=active
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
