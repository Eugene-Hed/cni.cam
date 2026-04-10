<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historique_demandes', function (Blueprint $table) {
            $table->id('HistoriqueID');
            $table->unsignedBigInteger('DemandeID');
            $table->enum('AncienStatut', ['Soumise', 'EnCours', 'Approuvee', 'Rejetee', 'Terminee'])->nullable();
            $table->enum('NouveauStatut', ['Soumise', 'EnCours', 'Approuvee', 'Rejetee', 'Terminee', 'Annulee']);
            $table->timestamp('DateModification')->useCurrent();
            $table->text('Commentaire')->nullable();
            $table->unsignedBigInteger('ModifiePar')->nullable();

            $table->foreign('DemandeID')->references('DemandeID')->on('demandes')->onDelete('cascade');
            $table->foreign('ModifiePar')->references('UtilisateurID')->on('utilisateurs');
            $table->index('DemandeID');
            $table->index('ModifiePar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historique_demandes');
    }
};
