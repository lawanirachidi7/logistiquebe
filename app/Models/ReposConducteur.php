<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ReposConducteur extends Model
{
    protected $fillable = [
        'conducteur_id', 
        'date_debut', 
        'date_fin', 
        'motif', 
        'notes',
        // Nouveaux champs pour la gestion intelligente
        'type_repos',
        'source',
        'score_fatigue_declencheur',
        'voyages_nuit_avant',
        'voyages_jour_avant',
        'jours_travail_consecutifs',
        'accepte',
        'accepte_le',
        'accepte_par',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'accepte' => 'boolean',
        'accepte_le' => 'datetime',
        'score_fatigue_declencheur' => 'integer',
        'voyages_nuit_avant' => 'integer',
        'voyages_jour_avant' => 'integer',
        'jours_travail_consecutifs' => 'integer',
    ];

    /**
     * Types de repos disponibles
     */
    const TYPE_JOUR = 'jour';
    const TYPE_NUIT = 'nuit';
    const TYPE_COMPLET = 'complet';

    /**
     * Sources possibles
     */
    const SOURCE_MANUEL = 'manuel';
    const SOURCE_AUTOMATIQUE = 'automatique';
    const SOURCE_SUGGERE = 'suggere';

    /**
     * Motifs de repos
     */
    const MOTIFS = [
        'Repos réglementaire',
        'Maladie',
        'Congé',
        'Force majeure',
        'Récupération fatigue nuit',
        'Récupération fatigue jour',
        'Récupération fatigue générale',
    ];

    public function conducteur()
    {
        return $this->belongsTo(Conducteur::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'accepte_par');
    }

    /**
     * Scope pour les repos actifs à une date donnée
     */
    public function scopeActifsA($query, $date = null)
    {
        $date = $date ?? Carbon::today();
        return $query->where('date_debut', '<=', $date)
                     ->where('date_fin', '>=', $date);
    }

    /**
     * Scope pour les repos en attente de validation
     */
    public function scopeEnAttente($query)
    {
        return $query->where('accepte', false)
                     ->where('source', '!=', self::SOURCE_MANUEL);
    }

    /**
     * Scope pour les repos automatiques
     */
    public function scopeAutomatiques($query)
    {
        return $query->where('source', self::SOURCE_AUTOMATIQUE);
    }

    /**
     * Scope pour les repos suggérés
     */
    public function scopeSuggeres($query)
    {
        return $query->where('source', self::SOURCE_SUGGERE);
    }

    /**
     * Scope par type de repos
     */
    public function scopeDeType($query, string $type)
    {
        return $query->where('type_repos', $type);
    }

    /**
     * Vérifie si le repos est actif aujourd'hui
     */
    public function estActif(): bool
    {
        $today = Carbon::today();
        return $this->date_debut <= $today && $this->date_fin >= $today;
    }

    /**
     * Vérifie si c'est un repos de nuit
     */
    public function estReposNuit(): bool
    {
        return $this->type_repos === self::TYPE_NUIT;
    }

    /**
     * Vérifie si c'est un repos de jour
     */
    public function estReposJour(): bool
    {
        return $this->type_repos === self::TYPE_JOUR;
    }

    /**
     * Vérifie si c'est un repos complet
     */
    public function estReposComplet(): bool
    {
        return $this->type_repos === self::TYPE_COMPLET;
    }

    /**
     * Calcule la durée en jours
     */
    public function getDureeAttribute(): int
    {
        return $this->date_debut->diffInDays($this->date_fin) + 1;
    }

    /**
     * Retourne le libellé du type
     */
    public function getTypeLibelleAttribute(): string
    {
        return match($this->type_repos) {
            self::TYPE_NUIT => 'Repos nuit uniquement',
            self::TYPE_JOUR => 'Repos jour uniquement',
            self::TYPE_COMPLET => 'Repos complet',
            default => 'Non spécifié',
        };
    }

    /**
     * Retourne le libellé de la source
     */
    public function getSourceLibelleAttribute(): string
    {
        return match($this->source) {
            self::SOURCE_AUTOMATIQUE => 'Généré automatiquement',
            self::SOURCE_SUGGERE => 'Suggestion système',
            self::SOURCE_MANUEL => 'Créé manuellement',
            default => 'Inconnu',
        };
    }

    /**
     * Retourne la couleur du badge selon la source
     */
    public function getBadgeCouleurAttribute(): string
    {
        return match($this->source) {
            self::SOURCE_AUTOMATIQUE => 'danger',
            self::SOURCE_SUGGERE => 'warning',
            self::SOURCE_MANUEL => 'primary',
            default => 'secondary',
        };
    }

    /**
     * Retourne l'icône selon le type
     */
    public function getIconeAttribute(): string
    {
        return match($this->type_repos) {
            self::TYPE_NUIT => 'fa-moon',
            self::TYPE_JOUR => 'fa-sun',
            self::TYPE_COMPLET => 'fa-bed',
            default => 'fa-pause-circle',
        };
    }

    /**
     * Accepter le repos
     */
    public function accepter(?int $userId = null): void
    {
        $this->update([
            'accepte' => true,
            'accepte_le' => now(),
            'accepte_par' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Refuser le repos (le supprimer)
     */
    public function refuser(): void
    {
        $this->delete();
    }
}
