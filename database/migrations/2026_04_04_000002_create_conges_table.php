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
            $table->foreignId('conducteur_id')->constrained()->onDelete('cascade');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->enum('type', [
                'annuel',
                'maladie',
                'maternite',
                'paternite',
                'sans_solde',
                'special',
                'autre'
            ])->default('annuel');
            $table->text('motif')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('valide')->default(false);
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('valide_le')->nullable();
            $table->timestamps();

            // Index pour les recherches fréquentes
            $table->index(['date_debut', 'date_fin']);
            $table->index(['conducteur_id', 'valide']);
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
