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
        Schema::create('conges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conducteur_id');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('type', ['annuel', 'maladie', 'maternite', 'paternite', 'sans_solde', 'special', 'autre'])->default('annuel');
            $table->string('motif');
            $table->text('notes')->nullable();
            $table->boolean('valide')->default(true);
            $table->unsignedBigInteger('valide_par')->nullable();
            $table->timestamp('valide_le')->nullable();
            $table->timestamps();

            $table->foreign('conducteur_id')->references('id')->on('conducteurs')->onDelete('cascade');
            $table->foreign('valide_par')->references('id')->on('users')->onDelete('set null');
            
            // Index pour optimiser les recherches de disponibilité
            $table->index(['conducteur_id', 'date_debut', 'date_fin']);
            $table->index(['date_debut', 'date_fin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conges');
    }
};
