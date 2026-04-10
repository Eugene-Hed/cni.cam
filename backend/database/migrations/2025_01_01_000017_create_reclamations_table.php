<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reclamations', function (Blueprint $table) {
            $table->id('ReclamationID');
            $table->unsignedBigInteger('UtilisateurID')->nullable();
            $table->unsignedBigInteger('DemandeID')->nullable();
            $table->string('TypeReclamation', 50)->nullable();
            $table->text('Description')->nullable();
            $table->enum('Statut', ['Ouverte', 'EnCours', 'Fermee'])->default('Ouverte');
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('UtilisateurID')->references('UtilisateurID')->on('utilisateurs');
            $table->foreign('DemandeID')->references('DemandeID')->on('demandes');
            $table->index('UtilisateurID');
            $table->index('DemandeID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reclamations');
    }
};
