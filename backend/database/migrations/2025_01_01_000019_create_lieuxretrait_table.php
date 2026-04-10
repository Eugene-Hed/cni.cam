<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lieuxretrait', function (Blueprint $table) {
            $table->id('LieuID');
            $table->string('NomLieu', 100);
            $table->text('Adresse')->nullable();
            $table->string('NumeroContact', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lieuxretrait');
    }
};
