<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villes', function (Blueprint $table) {
            $table->id('VilleID');
            $table->unsignedBigInteger('DepartementID');
            $table->string('NomVille', 50);

            $table->foreign('DepartementID')->references('DepartementID')->on('departements');
            $table->index('DepartementID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villes');
    }
};
