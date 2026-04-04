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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            
            // Destinataire (null = tous les utilisateurs)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // Type de notification
            $table->enum('type', [
                'fatigue_critique',      // Conducteur niveau rouge
                'fatigue_elevee',        // Conducteur niveau orange
                'repos_suggere',         // Repos auto-généré
                'repos_a_valider',       // Repos en attente de validation
                'repos_valide',          // Repos accepté
                'voyage_cree',           // Nouveau voyage créé
                'voyage_valide',         // Voyage terminé/validé
                'conducteur_indisponible', // Conducteur devenu indisponible
                'alerte_systeme',        // Alerte système générale
                'info',                  // Information générale
            ]);
            
            // Niveau d'importance
            $table->enum('niveau', ['info', 'warning', 'danger', 'success'])->default('info');
            
            // Contenu
            $table->string('titre');
            $table->text('message');
            $table->string('icone')->default('fa-bell');
            
            // Lien optionnel
            $table->string('lien')->nullable();
            $table->string('lien_texte')->nullable();
            
            // Contexte (données JSON liées)
            $table->json('contexte')->nullable();
            
            // Statut
            $table->boolean('lue')->default(false);
            $table->timestamp('lue_le')->nullable();
            
            // Expiration optionnelle
            $table->timestamp('expire_le')->nullable();
            
            $table->timestamps();
            
            // Index pour les requêtes fréquentes
            $table->index(['user_id', 'lue', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
