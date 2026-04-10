<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ethnies', function (Blueprint $table) {
            $table->id('EthnieID');
            $table->string('NomEthnie', 50);
            $table->text('Description')->nullable();
            $table->unsignedBigInteger('RegionPrincipale')->nullable();

            $table->foreign('RegionPrincipale')->references('RegionID')->on('regions');
            $table->index('RegionPrincipale');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ethnies');
    }
};
