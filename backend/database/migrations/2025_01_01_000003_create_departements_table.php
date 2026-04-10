<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departements', function (Blueprint $table) {
            $table->id('DepartementID');
            $table->unsignedBigInteger('RegionID');
            $table->string('NomDepartement', 50);

            $table->foreign('RegionID')->references('RegionID')->on('regions');
            $table->index('RegionID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departements');
    }
};
