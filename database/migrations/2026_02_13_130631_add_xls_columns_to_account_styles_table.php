<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('account_styles', function (Blueprint $table) {
            $table->string('acc_style_xls_format', 10)->default('NORMAL')->after('acc_style_state');
            $table->string('acc_style_xls_header', 255)->default('STANDARD')->after('acc_style_xls_format');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('account_styles', function (Blueprint $table) {
            $table->dropColumn(['acc_style_xls_format', 'acc_style_xls_header']);
        });
    }
};
