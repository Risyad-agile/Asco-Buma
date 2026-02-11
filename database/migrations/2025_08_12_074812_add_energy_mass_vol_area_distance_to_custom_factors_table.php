<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnergyMassVolAreaDistanceToCustomFactorsTable extends Migration
{
    public function up(): void
    {
        Schema::table('custom_factors', function (Blueprint $table) {
            $table->decimal('energy', 20, 10)->nullable()->after('co2e');
            $table->decimal('mass', 20, 10)->nullable()->after('energy');
            $table->decimal('vol', 20, 10)->nullable()->after('mass');
            $table->decimal('area', 20, 10)->nullable()->after('vol');
            $table->decimal('distance', 20, 10)->nullable()->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('custom_factors', function (Blueprint $table) {
            $table->dropColumn(['energy', 'mass', 'vol', 'area', 'distance']);
        });
    }
}
