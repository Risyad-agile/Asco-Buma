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
        Schema::create('account_style_group_maping', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('comp_id')->unsigned()->default(0); 
            $table->bigInteger('task_id')->unsigned()->default(0); 
            $table->string('acc_style_caption', 100)->nullable();
            $table->string('acc_style_caption_import', 100)->nullable();
            $table->string('acc_style_group_describe_state')->nullable();
            $table->char('acc_style_group_state', 1)->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_style_group_maping');
    }
};
