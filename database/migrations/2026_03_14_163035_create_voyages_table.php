<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voyages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conducteur_id')->constrained('conducteurs');
            $table->foreignId('conducteur_2_id')->nullable()->constrained('conducteurs'); // 2ème conducteur pour les voyages de nuit
            $table->foreignId('bus_id')->constrained('bus');
            $table->foreignId('ligne_id')->constrained('lignes');
            $table->dateTime('date_depart');
            $table->dateTime('date_retour_prevue')->nullable();
            $table->dateTime('date_retour_reelle')->nullable();
            $table->enum('periode', ['Jour', 'Nuit']);
            $table->enum('sens', ['Aller', 'Retour']);
            $table->enum('statut', ['Planifié', 'En cours', 'Terminé', 'Annulé'])->default('Planifié');
            $table->unsignedBigInteger('voyage_aller_id')->nullable();
            $table->foreign('voyage_aller_id')->references('id')->on('voyages')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voyages');
    }
};
