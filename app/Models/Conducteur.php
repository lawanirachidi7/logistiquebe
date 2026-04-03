<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conducteur extends Model
{
    protected $fillable = [
        'nom', 'prenom', 'ville_actuelle', 'famille_hors_parakou', 'specialiste_nuit', 'remplacant_nuit', 'actif'
    ];

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
        return $this->actif && !$this->estEnRepos($date) && !$this->estIndisponible($date);
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
     * Retourne le motif du repos ou de l'indisponibilité à une date donnée
     */
    public function getMotifIndisponibilite($date = null)
    {
        $date = $date ?? now()->toDateString();
        
        // Vérifier d'abord les repos
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
        // À compléter : vérifier la limite de voyages consécutifs
        return false;
    }
}
