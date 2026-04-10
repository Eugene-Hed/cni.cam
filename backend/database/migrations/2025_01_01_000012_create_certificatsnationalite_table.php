<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificatsnationalite', function (Blueprint $table) {
            $table->id('CertificatID');
            $table->unsignedBigInteger('DemandeID')->nullable();
            $table->string('NumeroCertificat', 50)->unique();
            $table->date('DateEmission')->nullable();
            $table->string('CheminPDF', 255)->nullable();
            $table->boolean('SignaturePresidentielle')->default(false);
            $table->string('CheminSignaturePresident', 255)->nullable();

            $table->foreign('DemandeID')->references('DemandeID')->on('demandes')->onDelete('cascade');
            $table->index('DemandeID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificatsnationalite');
    }
};
