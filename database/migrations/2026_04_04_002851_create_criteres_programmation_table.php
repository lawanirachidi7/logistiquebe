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
        Schema::create('criteres_programmation', function (Blueprint $table) {
            $table->id();
            $table->string('cle')->unique(); // Clé unique du critère
            $table->string('libelle'); // Nom affiché
            $table->text('description')->nullable(); // Description détaillée
            $table->string('categorie'); // Catégorie: horaires, conducteurs, bus, regles
            $table->string('type'); // Type: boolean, integer, string, time
            $table->text('valeur'); // Valeur (stockée en texte, castée selon type)
            $table->text('valeur_defaut'); // Valeur par défaut
            $table->boolean('actif')->default(true); // Critère actif ou non
            $table->integer('ordre')->default(0); // Ordre d'affichage
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criteres_programmation');
    }
};
