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
        Schema::create('connectors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('comp_id')->unsigned()->default(0); 
            $table->string('connect_name',100)->nullable(); //nama koneksi
            $table->string('connect_type',30)->nullable(); //ENVIZI CLIENT-ACCSETUP CLIENT-ACCSTYLE CLIENT-CSRDATA
            $table->string('connect_protocol',5)->nullable(); //API FTP S3
            $table->string('connect_username',50)->nullable(); //user token api
            $table->string('connect_password',50)->nullable(); //user token api
            $table->string('connect_url')->nullable(); //url
            $table->string('connect_body')->nullable(); //url
            $table->string('connect_email',100)->nullable(); //email
            $table->text('connect_token_value')->nullable(); // token untuk akses api envizi
            $table->string('connect_access_key_id',30)->nullable(); //access key aws s3
            $table->string('connect_access_key_secret',100)->nullable(); //secret key aws s3
            $table->string('connect_access_region',100)->nullable(); //region evizi di aws s3
            $table->string('connect_storage_code',30)->nullable(); //posisi konfigurasi aws s3 di laravel, isi public untuk disimpan ke local cloud
            $table->string('connect_remote_folder',100)->nullable(); //folder evizi di aws s3
            $table->string('connect_data_load_name')->nullable(); //file account dataload
            $table->string('connect_data_load_name_csr')->nullable(); //file csr 
            $table->char('connect_state', 1)->default(1);  //0=non active 1=active
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connectors');
    }
};
