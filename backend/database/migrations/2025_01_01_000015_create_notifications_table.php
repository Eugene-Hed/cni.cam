<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('NotificationID');
            $table->unsignedBigInteger('UtilisateurID')->nullable();
            $table->unsignedBigInteger('DemandeID')->nullable();
            $table->text('Contenu');
            $table->string('TypeNotification', 50)->nullable();
            $table->boolean('EstLue')->default(false);
            $table->timestamp('DateCreation')->useCurrent();

            $table->foreign('UtilisateurID')->references('UtilisateurID')->on('utilisateurs');
            $table->foreign('DemandeID')->references('DemandeID')->on('demandes')->onDelete('cascade');
            $table->index('UtilisateurID');
            $table->index('DemandeID', 'idx_notifications_demande');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
