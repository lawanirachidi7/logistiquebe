<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Services\FatigueService;
use Carbon\Carbon;

class Conducteur extends Model
{
    protected $fillable = [
        'nom', 'prenom', 'ville_actuelle', 'famille_hors_parakou', 'specialiste_nuit', 'remplacant_nuit', 'actif'
    ];

    /**
     * Instance du service de fatigue (lazy loaded)
     */
    protected static ?FatigueService $fatigueService = null;

    /**
     * Obtient le service de fatigue
     */
    protected static function fatigueService(): FatigueService
    {
        if (self::$fatigueService === null) {
            self::$fatigueService = app(FatigueService::class);
        }
        return self::$fatigueService;
    }

    // Relations
    public function typesBus()
    {
        return $this->belongsToMany(TypeBus::class, 'conducteur_type_bus');
    }

    public function voyages()
    {
        return $this->hasMany(Voyage::class);
    }

    public function repos()
    {
        return $this->hasMany(ReposConducteur::class);
    }

    public function indisponibilites()
    {
        return $this->hasMany(Indisponibilite::class);
    }

    public function conges()
    {
        return $this->hasMany(Conge::class);
    }

    // Scopes
    public function scopeDisponible($query, $date = null)
    {
        $date = $date ?? now()->toDateString();
        
        return $query->where('actif', true)
            // Exclure les conducteurs en repos
            ->whereDoesntHave('repos', function($q) use ($date) {
                $q->where('date_debut', '<=', $date)
                  ->where('date_fin', '>=', $date);
            })
            // Exclure les conducteurs indisponibles
            ->whereDoesntHave('indisponibilites', function($q) use ($date) {
                $q->where('date_debut', '<=', $date)
                  ->where('date_fin', '>=', $date);
            })
            // Exclure les conducteurs en congé
            ->whereDoesntHave('conges', function($q) use ($date) {
                $q->where('valide', true)
                  ->where('date_debut', '<=', $date)
                  ->where('date_fin', '>=', $date);
            });
    }

    public function scopePeutConduireNuit($query)
    {
        return $query->where(function($q) {
            $q->where('specialiste_nuit', true)->orWhere('remplacant_nuit', true);
        });
    }

    // Utilitaires métier (exemples)
    public function calculerVoyagesConsecutifs($periode)
    {
        // À compléter : compter les voyages consécutifs selon la période
        return $this->voyages()->where('periode', $periode)->count();
    }

    public function besoinRepos()
    {
        // À compléter : logique de besoin de repos
        return false;
    }

    public function proposerRepos()
    {
        // À compléter : création automatique de période de repos
    }

    /**
     * Vérifie si le conducteur est disponible à une date donnée
     */
    public function estDisponible($date = null)
    {
        $date = $date ?? now()->toDateString();
        return $this->actif && !$this->estEnRepos($date) && !$this->estIndisponible($date) && !$this->estEnConge($date);
    }

    /**
     * Vérifie si le conducteur est en repos à une date donnée
     */
    public function estEnRepos($date = null)
    {
        $date = $date ?? now()->toDateString();
        
        return $this->repos()
            ->where('date_debut', '<=', $date)
            ->where('date_fin', '>=', $date)
            ->exists();
    }

    /**
     * Vérifie si le conducteur a une indisponibilité à une date donnée
     */
    public function estIndisponible($date = null)
    {
        $date = $date ?? now()->toDateString();
        
        return $this->indisponibilites()
            ->where('date_debut', '<=', $date)
            ->where('date_fin', '>=', $date)
            ->exists();
    }

    /**
     * Vérifie si le conducteur est en congé à une date donnée
     */
    public function estEnConge($date = null)
    {
        $date = $date ?? now()->toDateString();
        
        return $this->conges()
            ->where('valide', true)
            ->where('date_debut', '<=', $date)
            ->where('date_fin', '>=', $date)
            ->exists();
    }

    /**
     * Retourne le congé actif si le conducteur est en congé
     */
    public function getCongeActif($date = null)
    {
        $date = $date ?? now()->toDateString();
        
        return $this->conges()
            ->where('valide', true)
            ->where('date_debut', '<=', $date)
            ->where('date_fin', '>=', $date)
            ->first();
    }

    /**
     * Retourne le motif du repos ou de l'indisponibilité à une date donnée
     */
    public function getMotifIndisponibilite($date = null)
    {
        $date = $date ?? now()->toDateString();
        
        // Vérifier d'abord les congés
        $conge = $this->conges()
            ->where('valide', true)
            ->where('date_debut', '<=', $date)
            ->where('date_fin', '>=', $date)
            ->first();
        
        if ($conge) {
            return "Congé ({$conge->type_label}): {$conge->motif}";
        }
        
        // Vérifier les repos
        $repos = $this->repos()
            ->where('date_debut', '<=', $date)
            ->where('date_fin', '>=', $date)
            ->first();
        
        if ($repos) {
            return "Repos: {$repos->motif}";
        }
        
        // Vérifier les indisponibilités
        $indispo = $this->indisponibilites()
            ->where('date_debut', '<=', $date)
            ->where('date_fin', '>=', $date)
            ->first();
        
        if ($indispo) {
            return "Indisponible: {$indispo->motif}";
        }
        
        return null;
    }

    public function voyagesConsecutifs($periode)
    {
        return $this->calculerVoyagesConsecutifs($periode);
    }

    public function dernierRepos()
    {
        $dernierRepos = $this->repos()->latest('date_fin')->first();
        return $dernierRepos ? $dernierRepos->date_fin : 'Jamais';
    }

    public function aAtteintLimite($periode)
    {
        // Vérifier via le service de fatigue
        $verification = self::fatigueService()->peutEtreProgramme($this, $periode);
        return !$verification['peut_etre_programme'];
    }

    // =====================================
    // MÉTHODES DE FATIGUE INTELLIGENTE
    // =====================================

    /**
     * Calcule et retourne le score de fatigue actuel
     */
    public function getScoreFatigue(?Carbon $date = null): array
    {
        return self::fatigueService()->calculerScoreFatigue($this, $date);
    }

    /**
     * Retourne le niveau de fatigue (vert, jaune, orange, rouge)
     */
    public function getNiveauFatigue(?Carbon $date = null): string
    {
        return $this->getScoreFatigue($date)['niveau'];
    }

    /**
     * Retourne le score de fatigue numérique (0-100)
     */
    public function getScoreFatigueNumerique(?Carbon $date = null): int
    {
        return $this->getScoreFatigue($date)['score'];
    }

    /**
     * Vérifie si le conducteur peut être programmé pour un voyage
     */
    public function peutEtreProgramme(string $periode = 'Jour', ?Carbon $date = null): array
    {
        return self::fatigueService()->peutEtreProgramme($this, $periode, $date);
    }

    /**
     * Vérifie si le conducteur peut travailler de nuit (basé sur fatigue)
     */
    public function peutTravaillerNuit(?Carbon $date = null): bool
    {
        // Vérifier d'abord les attributs de base
        if (!$this->specialiste_nuit && !$this->remplacant_nuit) {
            return false;
        }

        // Vérifier ensuite la fatigue
        $verification = $this->peutEtreProgramme('Nuit', $date);
        return $verification['peut_etre_programme'];
    }

    /**
     * Retourne les alertes de fatigue pour ce conducteur
     */
    public function getAlertesFatigue(?Carbon $date = null): array
    {
        return $this->getScoreFatigue($date)['alertes'];
    }

    /**
     * Retourne la recommandation de repos
     */
    public function getRecommandationRepos(?Carbon $date = null): array
    {
        return $this->getScoreFatigue($date)['recommandation'];
    }

    /**
     * Génère un repos automatique si nécessaire
     */
    public function genererReposAutomatique(bool $forcer = false): ?ReposConducteur
    {
        return self::fatigueService()->genererReposAutomatique($this, $forcer);
    }

    /**
     * Nombre de voyages de nuit consécutifs
     */
    public function getVoyagesNuitConsecutifs(?Carbon $date = null): int
    {
        return $this->getScoreFatigue($date)['statistiques']['nuits_consecutives'];
    }

    /**
     * Nombre de voyages de jour consécutifs
     */
    public function getVoyagesJourConsecutifs(?Carbon $date = null): int
    {
        return $this->getScoreFatigue($date)['statistiques']['jours_consecutifs'];
    }

    /**
     * Nombre de jours de travail sans repos
     */
    public function getJoursSansRepos(?Carbon $date = null): int
    {
        return $this->getScoreFatigue($date)['statistiques']['jours_travail_consecutifs'];
    }

    /**
     * Accesseur pour la couleur du badge de fatigue
     */
    public function getCouleurFatigueAttribute(): string
    {
        return self::fatigueService()->getCouleurNiveau($this->getNiveauFatigue());
    }

    /**
     * Accesseur pour le badge HTML de fatigue
     */
    public function getBadgeFatigueAttribute(): string
    {
        $niveau = $this->getNiveauFatigue();
        $score = $this->getScoreFatigueNumerique();
        $couleur = $this->couleur_fatigue;
        
        $icone = match($niveau) {
            'rouge' => 'fa-exclamation-triangle',
            'orange' => 'fa-exclamation-circle',
            'jaune' => 'fa-info-circle',
            'vert' => 'fa-check-circle',
            default => 'fa-circle',
        };

        return "<span class=\"badge\" style=\"background-color: {$couleur}\"><i class=\"fas {$icone}\"></i> {$score}%</span>";
    }

    /**
     * Scope pour filtrer par niveau de fatigue
     */
    public function scopeAvecNiveauFatigue($query, string $niveau)
    {
        return $query->get()->filter(function ($conducteur) use ($niveau) {
            return $conducteur->getNiveauFatigue() === $niveau;
        });
    }

    /**
     * Scope pour exclure les conducteurs fatigués
     */
    public function scopeNonFatigues($query)
    {
        return $query->get()->filter(function ($conducteur) {
            return in_array($conducteur->getNiveauFatigue(), ['vert', 'jaune']);
        });
    }

    /**
     * Scope pour obtenir les conducteurs programmables pour une période
     */
    public function scopeProgrammablesPour($query, string $periode, $date = null)
    {
        return $query->get()->filter(function ($conducteur) use ($periode, $date) {
            $verification = $conducteur->peutEtreProgramme($periode, $date);
            return $verification['peut_etre_programme'];
        });
    }
}
