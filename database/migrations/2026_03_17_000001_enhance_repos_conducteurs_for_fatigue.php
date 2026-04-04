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
        Schema::table('repos_conducteurs', function (Blueprint $table) {
            // Type de repos: jour seulement, nuit seulement, ou complet
            $table->enum('type_repos', ['jour', 'nuit', 'complet'])->default('complet')->after('motif');
            
            // Source du repos: manuel ou généré automatiquement par le système
            $table->enum('source', ['manuel', 'automatique', 'suggere'])->default('manuel')->after('type_repos');
            
            // Score de fatigue au moment de la création (0-100)
            $table->unsignedTinyInteger('score_fatigue_declencheur')->nullable()->after('source');
            
            // Contexte de la fatigue
            $table->unsignedSmallInteger('voyages_nuit_avant')->default(0)->after('score_fatigue_declencheur');
            $table->unsignedSmallInteger('voyages_jour_avant')->default(0)->after('voyages_nuit_avant');
            $table->unsignedSmallInteger('jours_travail_consecutifs')->default(0)->after('voyages_jour_avant');
            
            // Validation
            $table->boolean('accepte')->default(true)->after('jours_travail_consecutifs');
            $table->timestamp('accepte_le')->nullable()->after('accepte');
            $table->foreignId('accepte_par')->nullable()->after('accepte_le')->constrained('users');
        });

        // Créer une table pour l'historique de fatigue (tracking quotidien)
        Schema::create('historique_fatigue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conducteur_id')->constrained('conducteurs')->onDelete('cascade');
            $table->date('date');
            
            // Scores de fatigue (0-100)
            $table->unsignedTinyInteger('score_global')->default(0);
            $table->unsignedTinyInteger('score_nuit')->default(0);
            $table->unsignedTinyInteger('score_jour')->default(0);
            
            // Compteurs cumulés
            $table->unsignedSmallInteger('voyages_nuit_consecutifs')->default(0);
            $table->unsignedSmallInteger('voyages_jour_consecutifs')->default(0);
            $table->unsignedSmallInteger('jours_travail_consecutifs')->default(0);
            $table->unsignedSmallInteger('heures_conduites_semaine')->default(0);
            
            // Derniers repos
            $table->date('dernier_repos_complet')->nullable();
            $table->date('dernier_repos_nuit')->nullable();
            
            // Alerte générée
            $table->enum('niveau_alerte', ['vert', 'jaune', 'orange', 'rouge'])->default('vert');
            $table->text('message_alerte')->nullable();
            
            $table->timestamps();
            
            $table->unique(['conducteur_id', 'date']);
            $table->index(['date', 'niveau_alerte']);
        });

        // Créer une table pour les alertes fatigue
        Schema::create('alertes_fatigue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conducteur_id')->constrained('conducteurs')->onDelete('cascade');
            $table->date('date');
            
            $table->enum('niveau', ['info', 'attention', 'urgent', 'critique']);
            $table->enum('type', ['voyages_consecutifs', 'nuits_consecutives', 'sans_repos', 'surcharge_semaine', 'fatigue_generale']);
            $table->string('titre');
            $table->text('message');
            $table->json('contexte')->nullable(); // Données ayant déclenché l'alerte
            
            // Gestion de l'alerte
            $table->boolean('lue')->default(false);
            $table->boolean('traitee')->default(false);
            $table->string('action_prise')->nullable();
            $table->foreignId('traitee_par')->nullable()->constrained('users');
            $table->timestamp('traitee_le')->nullable();
            
            $table->timestamps();
            
            $table->index(['conducteur_id', 'lue']);
            $table->index(['date', 'niveau']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertes_fatigue');
        Schema::dropIfExists('historique_fatigue');
        
        Schema::table('repos_conducteurs', function (Blueprint $table) {
            $table->dropForeign(['accepte_par']);
            $table->dropColumn([
                'type_repos',
                'source',
                'score_fatigue_declencheur',
                'voyages_nuit_avant',
                'voyages_jour_avant',
                'jours_travail_consecutifs',
                'accepte',
                'accepte_le',
                'accepte_par'
            ]);
        });
    }
};
