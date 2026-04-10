<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id('DocumentID');
            $table->unsignedBigInteger('DemandeID')->nullable();
            $table->enum('TypeDocument', [
                'Photo', 'PhotoIdentite', 'ActeNaissance', 'CertificatNationalite',
                'AncienneCNI', 'ActeMariage', 'JustificatifProfession',
                'DecretNaturalisation', 'CasierJudiciaire', 'DeclarationPerte', 'Signature'
            ])->nullable();
            $table->string('CheminFichier', 255);
            $table->timestamp('DateTelechargement')->useCurrent();
            $table->enum('StatutValidation', ['EnAttente', 'Approuve', 'Rejete'])->default('EnAttente');
            $table->unsignedBigInteger('Utilisateurid');
            $table->timestamp('DateValidation')->nullable();
            $table->unsignedBigInteger('ValidePar')->nullable();

            $table->foreign('DemandeID')->references('DemandeID')->on('demandes');
            $table->index('DemandeID');
            $table->index(['Utilisateurid', 'TypeDocument'], 'idx_documents_user_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
