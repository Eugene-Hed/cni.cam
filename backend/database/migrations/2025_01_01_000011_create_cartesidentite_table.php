<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cartesidentite', function (Blueprint $table) {
            $table->id('CarteID');
            $table->unsignedBigInteger('UtilisateurID');
            $table->unsignedBigInteger('DemandeID')->nullable();
            $table->string('NumeroCarteIdentite', 50)->unique();
            $table->date('DateEmission')->nullable();
            $table->date('DateExpiration')->nullable();
            $table->string('CodeQR', 255)->nullable();
            $table->string('CheminFichier', 255)->nullable();
            $table->enum('Statut', ['Active', 'Expiree', 'Perdue', 'Annulee'])->default('Active');

            $table->foreign('DemandeID')->references('DemandeID')->on('demandes');
            $table->foreign('UtilisateurID')->references('UtilisateurID')->on('utilisateurs');
            $table->index('DemandeID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cartesidentite');
    }
};
