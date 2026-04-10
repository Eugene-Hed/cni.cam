<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id('UtilisateurID');
            $table->string('Codeutilisateur', 50)->unique();
            $table->string('Email', 100)->unique();
            $table->string('NumeroTelephone', 20);
            $table->string('Prenom', 50)->nullable();
            $table->string('Nom', 50)->nullable();
            $table->date('DateNaissance')->nullable();
            $table->text('Adresse')->nullable();
            $table->tinyInteger('RoleId');
            $table->timestamp('DateCreation')->useCurrent();
            $table->timestamp('DateMiseAJour')->useCurrent()->useCurrentOnUpdate();
            $table->string('PhotoUtilisateur', 255)->nullable();
            $table->string('Genre', 255)->nullable();
            $table->tinyInteger('IsActive')->nullable()->default(1);
            $table->unsignedBigInteger('RegionNaissanceID')->nullable();
            $table->unsignedBigInteger('DepartementNaissanceID')->nullable();
            $table->unsignedBigInteger('VilleNaissanceID')->nullable();
            $table->unsignedBigInteger('RegionResidenceID')->nullable();
            $table->unsignedBigInteger('DepartementResidenceID')->nullable();
            $table->unsignedBigInteger('VilleResidenceID')->nullable();
            $table->unsignedBigInteger('EthnieID')->nullable();
            $table->string('Profession', 100)->nullable();
            $table->string('CodeOTP', 6)->nullable();
            $table->dateTime('ExpirationOTP')->nullable();

            $table->index('NumeroTelephone');
            $table->foreign('RegionNaissanceID')->references('RegionID')->on('regions');
            $table->foreign('DepartementNaissanceID')->references('DepartementID')->on('departements');
            $table->foreign('VilleNaissanceID')->references('VilleID')->on('villes');
            $table->foreign('RegionResidenceID')->references('RegionID')->on('regions');
            $table->foreign('DepartementResidenceID')->references('DepartementID')->on('departements');
            $table->foreign('VilleResidenceID')->references('VilleID')->on('villes');
            $table->foreign('EthnieID')->references('EthnieID')->on('ethnies');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
