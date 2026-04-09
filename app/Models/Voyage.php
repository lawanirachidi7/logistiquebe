<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voyage extends Model
{
    protected $fillable = [
        'conducteur_id', 'conducteur_2_id', 'bus_id', 'ligne_id', 'date_depart', 'date_retour_prevue', 'date_retour_reelle', 'periode', 'sens', 'statut', 'voyage_aller_id', 'notes', 'force_nuit'
    ];

    protected static function booted()
    {
        static::creating(function ($voyage) {
            // Extraire la date du voyage
            $dateVoyage = date('Y-m-d', strtotime($voyage->date_depart));
            
            // Vérifier que le conducteur n'est pas en repos ou indisponible
            if ($voyage->conducteur) {
                if ($voyage->conducteur->estEnRepos($dateVoyage)) {
                    $motif = $voyage->conducteur->getMotifIndisponibilite($dateVoyage);
                    throw new \Exception("Conducteur {$voyage->conducteur->prenom} {$voyage->conducteur->nom} en repos ({$motif})");
                }
                if ($voyage->conducteur->estIndisponible($dateVoyage)) {
                    $motif = $voyage->conducteur->getMotifIndisponibilite($dateVoyage);
                    throw new \Exception("Conducteur {$voyage->conducteur->prenom} {$voyage->conducteur->nom} indisponible ({$motif})");
                }
            }
            
            // Vérifier le 2ème conducteur si présent
            if ($voyage->conducteur_2_id && $voyage->conducteur2) {
                if ($voyage->conducteur2->estEnRepos($dateVoyage)) {
                    $motif = $voyage->conducteur2->getMotifIndisponibilite($dateVoyage);
                    throw new \Exception("2ème conducteur {$voyage->conducteur2->prenom} {$voyage->conducteur2->nom} en repos ({$motif})");
                }
                if ($voyage->conducteur2->estIndisponible($dateVoyage)) {
                    $motif = $voyage->conducteur2->getMotifIndisponibilite($dateVoyage);
                    throw new \Exception("2ème conducteur {$voyage->conducteur2->prenom} {$voyage->conducteur2->nom} indisponible ({$motif})");
                }
            }
            
            // Vérifier limite de voyages
            if ($voyage->conducteur && method_exists($voyage->conducteur, 'aAtteintLimite') && $voyage->conducteur->aAtteintLimite($voyage->periode)) {
                // Notification session (affichée après redirection)
                session()->flash('warning', 'Limite de voyages atteinte pour le conducteur, mais la programmation est autorisée.');
            }
            // Vérifier compatibilité nuit (exception opérateur possible)
            if (
                $voyage->periode === 'Nuit'
                && $voyage->conducteur
                && method_exists($voyage->conducteur, 'peutTravaillerNuit')
                && !$voyage->conducteur->peutTravaillerNuit()
                && empty($voyage->force_nuit)
            ) {
                session()->flash('warning', 'Conducteur non autorisé la nuit, mais la programmation est autorisée.');
            }
        });

        static::created(function ($voyage) {
            // Si c'est un aller, préparer le retour (notification, etc.)
            if ($voyage->sens === 'Aller') {
                // Planifier suggestion de retour (à implémenter)
            }
            // Mettre à jour les compteurs (à implémenter)
            // $voyage->conducteur->increment('voyages_consecutifs_' . strtolower($voyage->periode));
        });
    }

    public function conducteur()
    {
        return $this->belongsTo(Conducteur::class);
    }

    public function conducteur2()
    {
        return $this->belongsTo(Conducteur::class, 'conducteur_2_id');
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function ligne()
    {
        return $this->belongsTo(Ligne::class);
    }

    public function voyageAller()
    {
        return $this->belongsTo(self::class, 'voyage_aller_id');
    }

    public function voyageRetour()
    {
        return $this->hasOne(self::class, 'voyage_aller_id');
    }

    public function scopeAllers($query)
    {
        return $query->where('sens', 'Aller');
    }

    public function scopeRetours($query)
    {
        return $query->where('sens', 'Retour');
    }

    public function scopePeriode($query, $periode)
    {
        return $query->where('periode', $periode);
    }
}
