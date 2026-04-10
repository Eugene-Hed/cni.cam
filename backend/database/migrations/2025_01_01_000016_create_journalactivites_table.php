<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journalactivites', function (Blueprint $table) {
            $table->id('JournalID');
            $table->unsignedBigInteger('UtilisateurID')->nullable();
            $table->string('TypeActivite', 50)->nullable();
            $table->text('Description')->nullable();
            $table->string('AdresseIP', 45)->nullable();
            $table->timestamp('DateHeure')->useCurrent();

            $table->foreign('UtilisateurID')->references('UtilisateurID')->on('utilisateurs');
            $table->index('UtilisateurID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journalactivites');
    }
};
