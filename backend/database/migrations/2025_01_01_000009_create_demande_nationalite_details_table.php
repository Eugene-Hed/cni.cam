<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_nationalite_details', function (Blueprint $table) {
            $table->id('DetailID');
            $table->unsignedBigInteger('DemandeID');
            $table->string('Nom', 50);
            $table->string('Prenom', 50);
            $table->date('DateNaissance');
            $table->string('LieuNaissance', 100);
            $table->enum('Sexe', ['M', 'F'])->nullable();
            $table->string('NomPere', 100);
            $table->string('NomMere', 100);
            $table->text('Adresse');
            $table->string('Ville', 100)->nullable();
            $table->string('CodePostal', 20)->nullable();
            $table->string('Telephone', 20);
            $table->string('EtatCivil', 50)->nullable();
            $table->string('Profession', 100)->nullable();
            $table->string('NationaliteActuelle', 100)->nullable();
            $table->enum('Motif', ['naissance', 'mariage', 'naturalisation', 'filiation']);

            $table->foreign('DemandeID')->references('DemandeID')->on('demandes')->onDelete('cascade');
            $table->index('DemandeID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_nationalite_details');
    }
};
