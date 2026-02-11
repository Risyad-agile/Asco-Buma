<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_env', function (Blueprint $table) {
            $table->id();
            $table->string('organization')->nullable();
            $table->string('location')->nullable();
            $table->string('account_style_caption')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_reference')->nullable();
            $table->string('account_supplier')->nullable();
            $table->dateTime('record_start')->nullable();
            $table->dateTime('record_end')->nullable();
            $table->decimal('quantity', 20, 4)->nullable();
            $table->decimal('total_cost_incl_tax_local_currency', 20, 4)->nullable();
            $table->string('record_reference')->nullable();
            $table->string('record_invoice_number')->nullable();
            $table->string('record_data_quality')->nullable();
            $table->integer('total')->nullable();
            $table->dateTime('created_utc_date')->nullable();
            $table->dateTime('modified_utc_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_env');
    }
};
