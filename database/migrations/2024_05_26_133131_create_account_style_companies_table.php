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
        Schema::create('account_style_companies', function (Blueprint $table) {
            $table->id();
            $table->integer('comp_id')->unsigned();
            $table->foreign('comp_id')->references('id')->on('companies');
            $table->string('acc_style_comp_link', 50)->nullable();
            $table->string('acc_style_comp_caption', 100)->nullable();
            $table->string('acc_style_comp_caption_import', 100)->nullable();
            $table->string('acc_style_comp_subtype', 50)->nullable();
            $table->string('acc_style_comp_number', 100)->nullable();
            $table->string('acc_style_comp_reference', 100)->nullable();
            $table->string('acc_style_comp_supplier', 50)->nullable();
            $table->string('acc_style_comp_reader', 50)->nullable();
            $table->char('acc_style_comp_state', 1)->default(1);  //0=non active 1=active
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_style_companies');
    }
};
