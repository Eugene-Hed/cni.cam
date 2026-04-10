<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id('RegionID');
            $table->string('NomRegion', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
