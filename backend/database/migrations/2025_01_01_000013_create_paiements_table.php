<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id('PaiementID');
            $table->unsignedBigInteger('DemandeID')->nullable();
            $table->decimal('Montant', 10, 2);
            $table->timestamp('DatePaiement')->useCurrent();
            $table->enum('StatutPaiement', ['EnAttente', 'Complete', 'Echoue']);
            $table->string('ReferenceTransaction', 100)->nullable();

            $table->foreign('DemandeID')->references('DemandeID')->on('demandes');
            $table->index('DemandeID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
