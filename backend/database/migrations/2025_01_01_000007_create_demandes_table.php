<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandes', function (Blueprint $table) {
            $table->id('DemandeID');
            $table->string('NumeroReference', 50)->nullable();
            $table->unsignedBigInteger('UtilisateurID')->nullable();
            $table->enum('TypeDemande', ['CNI', 'CertificatNationalite', 'NATIONALITE']);
            $table->enum('SousTypeDemande', ['premiere', 'renouvellement', 'perte', 'naturalisation'])->nullable();
            $table->enum('Statut', ['Soumise', 'EnCours', 'Approuvee', 'Rejetee', 'Terminee', 'Annulee']);
            $table->timestamp('DateSoumission')->useCurrent();
            $table->timestamp('DateAchevement')->nullable();
            $table->decimal('MontantPaiement', 10, 2)->nullable();
            $table->string('StatutPaiement', 50)->nullable();
            $table->boolean('SignatureRequise')->default(false);
            $table->boolean('SignatureEnregistree')->default(false);
            $table->string('CheminSignature', 255)->nullable();
            $table->timestamp('DateSignature')->nullable();
            $table->boolean('SignatureOfficierRequise')->default(false);
            $table->boolean('SignatureOfficierEnregistree')->default(false);
            $table->string('CheminSignatureOfficier', 255)->nullable();
            $table->timestamp('DateSignatureOfficier')->nullable();

            $table->foreign('UtilisateurID')->references('UtilisateurID')->on('utilisateurs');
            $table->index(['UtilisateurID', 'TypeDemande', 'Statut'], 'idx_demandes_user_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};
