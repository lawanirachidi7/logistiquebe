<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    protected $table = 'bus';

    protected $fillable = [
        'immatriculation', 'type_bus_id', 'ligne_nord', 'disponible', 'ville_actuelle'
    ];

    public function typeBus()
    {
        return $this->belongsTo(TypeBus::class);
    }

    public function voyages()
    {
        return $this->hasMany(Voyage::class);
    }

    public function scopeDisponible($query, $date = null)
    {
        return $query->where('disponible', true);
    }

    public function scopeLigneNord($query)
    {
        return $query->where('ligne_nord', true);
    }

    /**
     * Obtenir la position actuelle du bus à une date donnée
     * basée sur la dernière programmation terminée ou planifiée
     */
    public function getPositionADate($date = null)
    {
        $date = $date ?? now()->toDateString();
        
        // Chercher le dernier voyage terminé ou planifié jusqu'à cette date
        $dernierVoyage = $this->voyages()
            ->with('ligne')
            ->whereDate('date_depart', '<=', $date)
            ->whereIn('statut', ['Terminé', 'En cours', 'Planifié'])
            ->orderByDesc('date_depart')
            ->first();
        
        if ($dernierVoyage && $dernierVoyage->ligne) {
            // Si le voyage est terminé, le bus est à la ville d'arrivée
            if ($dernierVoyage->statut === 'Terminé') {
                return $dernierVoyage->ligne->ville_arrivee;
            }
            // Si planifié ou en cours, considérer qu'il sera à destination
            // (utile pour la programmation du lendemain)
            return $dernierVoyage->ligne->ville_arrivee;
        }
        
        // Par défaut, retourner la ville_actuelle enregistrée ou Parakou
        return $this->ville_actuelle ?? 'Parakou';
    }

    /**
     * Scope pour filtrer les bus par position à une date donnée
     */
    public function scopeAVille($query, $ville, $date = null)
    {
        $date = $date ?? now()->toDateString();
        
        return $query->where(function($q) use ($ville, $date) {
            // Bus dont la ville_actuelle est la ville demandée
            // ET qui n'ont pas de voyage planifié les emmenant ailleurs
            $q->where('ville_actuelle', $ville)
              ->whereDoesntHave('voyages', function($vq) use ($date) {
                  $vq->whereDate('date_depart', '<', $date)
                     ->whereIn('statut', ['Terminé', 'En cours', 'Planifié']);
              });
        })->orWhereHas('voyages', function($vq) use ($ville, $date) {
            // Ou bus dont le dernier voyage terminé/planifié les a amenés à cette ville
            $vq->whereDate('date_depart', '<=', $date)
               ->whereIn('statut', ['Terminé', 'En cours', 'Planifié'])
               ->whereHas('ligne', function($lq) use ($ville) {
                   $lq->where('ville_arrivee', $ville);
               });
        });
    }
}
