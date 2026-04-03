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
        Schema::table('lignes', function (Blueprint $table) {
            $table->boolean('aller_retour_meme_jour')->default(false)->after('est_ligne_nord')
                ->comment('Indique si le bus fait aller et retour dans la même journée');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lignes', function (Blueprint $table) {
            $table->dropColumn('aller_retour_meme_jour');
        });
    }
};
