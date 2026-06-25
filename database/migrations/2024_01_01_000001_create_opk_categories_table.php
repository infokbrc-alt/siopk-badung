<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opk_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('nomor'); // 1-10 sesuai UU No.5/2017
            $table->string('nama', 100);          // Tradisi Lisan, Manuskrip, dst.
            $table->string('deskripsi', 255)->nullable();
            $table->string('ikon', 50)->nullable(); // emoji / icon class
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opk_categories');
    }
};
