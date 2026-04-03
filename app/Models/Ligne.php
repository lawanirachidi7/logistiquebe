<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ligne extends Model
{
    protected $fillable = [
        'nom', 'type', 'ligne_retour_id', 'horaire', 'ville_depart', 'ville_arrivee', 'est_ligne_nord', 'aller_retour_meme_jour', 'distance_km', 'duree_estimee'
    ];

    protected $casts = [
        'horaire' => 'string',
        'est_ligne_nord' => 'boolean',
        'aller_retour_meme_jour' => 'boolean',
    ];



    /**
     * Obtenir l'horaire formaté (HH:MM)
     */
    public function getHoraireFormateAttribute()
    {
        return substr($this->horaire, 0, 5);
    }

    public function voyages()
    {
        return $this->hasMany(Voyage::class);
    }

    /**
     * Relation vers la ligne retour associée
     */
    public function ligneRetourAssociee()
    {
        return $this->belongsTo(Ligne::class, 'ligne_retour_id');
    }

    /**
     * Relation inverse: lignes qui ont cette ligne comme retour
     */
    public function lignesAllerAssociees()
    {
        return $this->hasMany(Ligne::class, 'ligne_retour_id');
    }

    /**
     * Récupère la ligne retour correspondante
     * Priorité: 1) Association explicite, 2) Même horaire, 3) N'importe quelle ligne inverse
     */
    public function getLigneRetour()
    {
        // Si une ligne retour est explicitement associée
        if ($this->ligne_retour_id) {
            return $this->ligneRetourAssociee;
        }

        // Chercher d'abord avec le même horaire
        $ligneRetour = self::where('ville_depart', $this->ville_arrivee)
            ->where('ville_arrivee', $this->ville_depart)
            ->where('horaire', $this->horaire)
            ->first();

        // Sinon, prendre n'importe quelle ligne retour
        if (!$ligneRetour) {
            $ligneRetour = self::where('ville_depart', $this->ville_arrivee)
                ->where('ville_arrivee', $this->ville_depart)
                ->first();
        }

        return $ligneRetour;
    }

    /**
     * Récupère la ligne aller correspondante
     * Priorité: 1) Lignes qui nous ont comme retour, 2) Même horaire, 3) N'importe quelle ligne inverse
     */
    public function getLigneAller()
    {
        // Chercher les lignes qui ont cette ligne comme retour associé
        $ligneAller = self::where('ligne_retour_id', $this->id)->first();
        
        if ($ligneAller) {
            return $ligneAller;
        }

        // Chercher d'abord avec le même horaire
        $ligneAller = self::where('ville_depart', $this->ville_arrivee)
            ->where('ville_arrivee', $this->ville_depart)
            ->where('horaire', $this->horaire)
            ->first();

        // Sinon, prendre n'importe quelle ligne aller
        if (!$ligneAller) {
            $ligneAller = self::where('ville_depart', $this->ville_arrivee)
                ->where('ville_arrivee', $this->ville_depart)
                ->first();
        }

        return $ligneAller;
    }

    /**
     * Scope pour les lignes Aller (départ de Parakou)
     */
    public function scopeAller($query)
    {
        return $query->where('type', 'Aller');
    }

    /**
     * Scope pour les lignes Retour (arrivée à Parakou)
     */
    public function scopeRetour($query)
    {
        return $query->where('type', 'Retour');
    }
}
