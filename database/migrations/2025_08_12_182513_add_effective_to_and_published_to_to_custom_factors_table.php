<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEffectiveToAndPublishedToToCustomFactorsTable extends Migration
{
    public function up(): void
    {
        Schema::table('custom_factors', function (Blueprint $table) {
            $table->date('effective_to')->nullable()->after('effective_date');
            $table->date('published_to')->nullable()->after('published_date');
        });
    }

    public function down(): void
    {
        Schema::table('custom_factors', function (Blueprint $table) {
            $table->dropColumn(['effective_to', 'published_to']);
        });
    }
}
