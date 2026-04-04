<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Conge extends Model
{
    protected $fillable = [
        'conducteur_id',
        'date_debut',
        'date_fin',
        'type',
        'motif',
        'notes',
        'valide',
        'valide_par',
        'valide_le',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'valide' => 'boolean',
        'valide_le' => 'datetime',
    ];

    /**
     * Types de congés disponibles
     */
    const TYPE_ANNUEL = 'annuel';
    const TYPE_MALADIE = 'maladie';
    const TYPE_MATERNITE = 'maternite';
    const TYPE_PATERNITE = 'paternite';
    const TYPE_SANS_SOLDE = 'sans_solde';
    const TYPE_SPECIAL = 'special';
    const TYPE_AUTRE = 'autre';

    /**
     * Labels pour les types de congés
     */
    const TYPES_LABELS = [
        'annuel' => 'Congé annuel',
        'maladie' => 'Congé maladie',
        'maternite' => 'Congé maternité',
        'paternite' => 'Congé paternité',
        'sans_solde' => 'Congé sans solde',
        'special' => 'Congé spécial',
        'autre' => 'Autre',
    ];

    /**
     * Couleurs pour les badges des types
     */
    const TYPES_COULEURS = [
        'annuel' => 'primary',
        'maladie' => 'danger',
        'maternite' => 'pink',
        'paternite' => 'info',
        'sans_solde' => 'secondary',
        'special' => 'warning',
        'autre' => 'dark',
    ];

    /**
     * Icônes pour les types de congés
     */
    const TYPES_ICONES = [
        'annuel' => 'fa-umbrella-beach',
        'maladie' => 'fa-briefcase-medical',
        'maternite' => 'fa-baby',
        'paternite' => 'fa-baby-carriage',
        'sans_solde' => 'fa-money-bill-wave',
        'special' => 'fa-star',
        'autre' => 'fa-calendar-alt',
    ];

    // =====================================
    // RELATIONS
    // =====================================

    public function conducteur()
    {
        return $this->belongsTo(Conducteur::class);
    }

    public function validateur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    // =====================================
    // SCOPES
    // =====================================

    /**
     * Scope pour les congés actifs à une date donnée
     */
    public function scopeActifsA($query, $date = null)
    {
        $date = $date ?? Carbon::today();
        return $query->where('date_debut', '<=', $date)
                     ->where('date_fin', '>=', $date)
                     ->where('valide', true);
    }

    /**
     * Scope pour les congés à venir
     */
    public function scopeAVenir($query)
    {
        return $query->where('date_debut', '>', Carbon::today())
                     ->where('valide', true);
    }

    /**
     * Scope pour les congés passés
     */
    public function scopePasses($query)
    {
        return $query->where('date_fin', '<', Carbon::today());
    }

    /**
     * Scope pour les congés validés
     */
    public function scopeValides($query)
    {
        return $query->where('valide', true);
    }

    /**
     * Scope pour les congés en attente de validation
     */
    public function scopeEnAttente($query)
    {
        return $query->where('valide', false);
    }

    /**
     * Scope pour filtrer par type
     */
    public function scopeDeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer par conducteur
     */
    public function scopePourConducteur($query, $conducteurId)
    {
        return $query->where('conducteur_id', $conducteurId);
    }

    /**
     * Scope pour les congés dans une période donnée
     */
    public function scopeDansPeriode($query, $debut, $fin)
    {
        return $query->where(function($q) use ($debut, $fin) {
            $q->whereBetween('date_debut', [$debut, $fin])
              ->orWhereBetween('date_fin', [$debut, $fin])
              ->orWhere(function($q2) use ($debut, $fin) {
                  $q2->where('date_debut', '<=', $debut)
                     ->where('date_fin', '>=', $fin);
              });
        });
    }

    // =====================================
    // ACCESSEURS
    // =====================================

    /**
     * Retourne le label du type de congé
     */
    public function getTypeLabelAttribute()
    {
        return self::TYPES_LABELS[$this->type] ?? 'Inconnu';
    }

    /**
     * Retourne la couleur du badge pour le type
     */
    public function getTypeCouleurAttribute()
    {
        return self::TYPES_COULEURS[$this->type] ?? 'secondary';
    }

    /**
     * Retourne l'icône pour le type
     */
    public function getTypeIconeAttribute()
    {
        return self::TYPES_ICONES[$this->type] ?? 'fa-calendar';
    }

    /**
     * Calcule la durée du congé en jours
     */
    public function getDureeAttribute()
    {
        return $this->date_debut->diffInDays($this->date_fin) + 1;
    }

    /**
     * Vérifie si le congé est actuellement actif
     */
    public function getEstActifAttribute()
    {
        return $this->estActif();
    }

    /**
     * Retourne le statut du congé
     */
    public function getStatutAttribute()
    {
        if (!$this->valide) {
            return 'en_attente';
        }
        
        $today = Carbon::today();
        
        if ($this->date_fin < $today) {
            return 'termine';
        }
        
        if ($this->date_debut > $today) {
            return 'a_venir';
        }
        
        return 'en_cours';
    }

    /**
     * Retourne le label du statut
     */
    public function getStatutLabelAttribute()
    {
        return match($this->statut) {
            'en_attente' => 'En attente',
            'termine' => 'Terminé',
            'a_venir' => 'À venir',
            'en_cours' => 'En cours',
            default => 'Inconnu'
        };
    }

    /**
     * Retourne la couleur du badge pour le statut
     */
    public function getStatutCouleurAttribute()
    {
        return match($this->statut) {
            'en_attente' => 'warning',
            'termine' => 'secondary',
            'a_venir' => 'info',
            'en_cours' => 'success',
            default => 'dark'
        };
    }

    // =====================================
    // MÉTHODES UTILITAIRES
    // =====================================

    /**
     * Vérifie si le congé est actif à une date donnée
     */
    public function estActif($date = null): bool
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        
        return $this->valide 
            && $this->date_debut <= $date 
            && $this->date_fin >= $date;
    }

    /**
     * Vérifie si le congé chevauche une période donnée
     */
    public function chevauche($debut, $fin): bool
    {
        $debut = Carbon::parse($debut);
        $fin = Carbon::parse($fin);
        
        return $this->date_debut <= $fin && $this->date_fin >= $debut;
    }

    /**
     * Valide le congé
     */
    public function valider($userId = null): void
    {
        $this->update([
            'valide' => true,
            'valide_par' => $userId ?? auth()->id(),
            'valide_le' => now(),
        ]);
    }

    /**
     * Annule la validation du congé
     */
    public function annulerValidation(): void
    {
        $this->update([
            'valide' => false,
            'valide_par' => null,
            'valide_le' => null,
        ]);
    }

    /**
     * Retourne les types de congés disponibles pour un select
     */
    public static function getTypesForSelect(): array
    {
        return self::TYPES_LABELS;
    }
}
