<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer l'ancien index unique s'il existe (syntaxe MySQL)
        try {
            DB::statement('ALTER TABLE lignes DROP INDEX lignes_nom_unique');
        } catch (\Exception $e) {
            // Index n'existe pas, on continue
        }
        
        // Créer un nouvel index unique composite sur nom + horaire
        Schema::table('lignes', function (Blueprint $table) {
            $table->unique(['nom', 'horaire'], 'lignes_nom_horaire_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lignes', function (Blueprint $table) {
            $table->dropUnique('lignes_nom_horaire_unique');
            $table->unique('nom');
        });
    }
};
