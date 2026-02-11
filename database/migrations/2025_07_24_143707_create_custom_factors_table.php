<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomFactorsTable extends Migration
{
    public function up(): void
    {
        Schema::create('custom_factors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('factor_set_id')->nullable();
            $table->string('source', 255)->nullable();
            $table->string('reference', 255)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('subcategory', 100)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('associate_code', 100)->nullable();
            $table->string('factor_link', 255)->nullable();
            $table->string('data_type', 100)->nullable();
            $table->string('sub_type', 100)->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('factor_value', 20, 10)->nullable();
            $table->decimal('co2', 20, 10)->nullable();
            $table->decimal('ch4', 20, 10)->nullable();
            $table->decimal('n2o', 20, 10)->nullable();
            $table->decimal('biogenic', 20, 10)->nullable();
            $table->decimal('co2e', 20, 10)->nullable();
            $table->decimal('energy', 20, 10)->nullable();
            $table->decimal('mass', 20, 10)->nullable();
            $table->decimal('vol', 20, 10)->nullable();
            $table->decimal('area', 20, 10)->nullable();
            $table->decimal('distance', 20, 10)->nullable();
            $table->string('calculation_method', 255)->nullable();
            $table->text('description')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('published_date')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('sector', 100)->nullable();
            $table->string('scope', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_factors');
    }
}

