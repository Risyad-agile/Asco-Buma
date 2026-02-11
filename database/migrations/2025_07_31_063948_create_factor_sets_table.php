<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('factor_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // Contoh: "POC - Agile Co"
            $table->string('description')->nullable();      // Penjelasan singkat
            $table->string('source')->nullable();           // Contoh: "Internal", "DEFRA", dll
            $table->year('year')->nullable();               // Tahun faktor
            $table->unsignedBigInteger('company_id')->nullable(); // Jika dikaitkan ke perusahaan tertentu
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('factor_sets');
    }
};
