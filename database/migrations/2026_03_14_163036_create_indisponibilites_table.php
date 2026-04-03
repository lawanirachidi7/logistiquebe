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
        Schema::create('indisponibilites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conducteur_id')->nullable();
            $table->unsignedBigInteger('bus_id')->nullable();
            $table->dateTime('date_debut');
            $table->dateTime('date_fin');
            $table->string('motif');
            $table->timestamps();

            $table->foreign('conducteur_id')->references('id')->on('conducteurs')->onDelete('set null');
            $table->foreign('bus_id')->references('id')->on('bus')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indisponibilites');
    }
};
