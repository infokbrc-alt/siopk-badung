<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kecamatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('kode', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('desa_dinas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->onDelete('cascade');
            $table->string('nama', 100);
            $table->string('kode', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('desa_adats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->onDelete('cascade');
            $table->string('nama', 150);
            $table->string('banjar_adat', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desa_adats');
        Schema::dropIfExists('desa_dinas');
        Schema::dropIfExists('kecamatans');
    }
};
