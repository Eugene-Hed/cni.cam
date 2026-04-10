<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rendezvous', function (Blueprint $table) {
            $table->id('RendezVousID');
            $table->unsignedBigInteger('DemandeID')->nullable();
            $table->dateTime('DateRendezVous')->nullable();
            $table->string('Lieu', 100)->nullable();
            $table->enum('Statut', ['Planifie', 'Termine', 'Annule'])->default('Planifie');

            $table->foreign('DemandeID')->references('DemandeID')->on('demandes');
            $table->index('DemandeID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rendezvous');
    }
};
