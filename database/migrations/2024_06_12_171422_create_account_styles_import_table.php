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
        Schema::create('account_styles_import', function (Blueprint $table) {
            $table->increments('id');
            $table->string('acc_style_product', 100)->nullable();
            $table->string('acc_style_datatype', 100)->nullable();
            $table->string('acc_style_scope', 50)->nullable(); 
            $table->string('acc_style_category', 50)->nullable(); 
            $table->string('acc_style_name', 100)->nullable(); 
            $table->string('acc_style_caption', 100)->nullable();
            $table->string('acc_style_qty_uom', 50)->nullable();
            $table->string('acc_style_cost_supported', 10)->nullable();
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_styles_import');
    }
};
