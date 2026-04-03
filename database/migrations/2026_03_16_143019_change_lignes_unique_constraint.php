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
        // SQLite ne supporte pas DROP INDEX directement via Schema
        // On doit utiliser une requête SQL brute
        DB::statement('DROP INDEX IF EXISTS lignes_nom_unique');
        
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
