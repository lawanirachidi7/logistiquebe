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
        Schema::table('bus', function (Blueprint $table) {
            $table->string('ville_actuelle')->default('Parakou')->after('disponible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bus', function (Blueprint $table) {
            $table->dropColumn('ville_actuelle');
        });
    }
};
