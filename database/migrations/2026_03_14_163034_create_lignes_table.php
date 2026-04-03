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
        Schema::create('lignes', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->string('ville_depart');
            $table->string('ville_arrivee');
            $table->boolean('est_ligne_nord')->default(false);
            $table->integer('distance_km')->nullable();
            $table->integer('duree_estimee')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lignes');
    }
};
