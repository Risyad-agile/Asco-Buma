<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id'); 
            $table->integer('org_id')->unsigned()->default(0); 
            $table->integer('connect_id')->unsigned()->default(0); 
            $table->string('comp_name',50)->nullable(); 
            $table->string('comp_address',100)->nullable();
            $table->string('comp_pos_code',6)->nullable();
            $table->string('comp_city', 50)->nullable();
            $table->string('comp_province', 30)->nullable();
            $table->string('comp_phone',16)->nullable();
            $table->string('comp_email',100)->nullable();
            $table->string('comp_logo',100)->nullable();
            $table->char('comp_state', 1)->default(1);  //0=non active 1=active
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
};
