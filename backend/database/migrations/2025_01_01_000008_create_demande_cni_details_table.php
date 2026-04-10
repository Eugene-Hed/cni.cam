<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_cni_details', function (Blueprint $table) {
            $table->id('DetailID');
            $table->unsignedBigInteger('DemandeID');
            $table->enum('TypeDemande', ['premiere', 'renouvellement', 'perte', 'naturalisation'])->nullable();
            $table->string('Nom', 50);
            $table->string('Prenom', 50);
            $table->date('DateNaissance');
            $table->string('LieuNaissance', 100);
            $table->text('Adresse');
            $table->enum('Sexe', ['M', 'F']);
            $table->integer('Taille');
            $table->string('Profession', 100);
            $table->string('StatutCivil', 50)->nullable();
            $table->string('NumeroCNIPrecedente', 50)->nullable();
            $table->date('DatePerteVol')->nullable();
            $table->string('NumeroDecretNaturalisation', 100)->nullable();
            $table->string('NationalitePere', 100)->nullable();
            $table->string('NationaliteMere', 100)->nullable();

            $table->foreign('DemandeID')->references('DemandeID')->on('demandes')->onDelete('cascade');
            $table->index('DemandeID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_cni_details');
    }
};
