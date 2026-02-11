<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('data_env', function (Blueprint $table) {
            $table->unsignedTinyInteger('scope')->after('id')->default(1)->index();
            // ensure remote_id is indexed too
            $table->unsignedBigInteger('remote_id')->nullable()->change();

            // If you previously had unique(remote_id), drop it first:
            // $table->dropUnique('data_env_remote_id_unique');

            $table->unique(['scope', 'remote_id'], 'data_env_scope_remote_unique');
        });
    }

    public function down(): void
    {
        Schema::table('data_env', function (Blueprint $table) {
            $table->dropUnique('data_env_scope_remote_unique');
            $table->dropColumn('scope');
        });
    }
};
