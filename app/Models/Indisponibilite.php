<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Indisponibilite extends Model
{
    protected $fillable = [
        'conducteur_id', 'bus_id', 'date_debut', 'date_fin', 'motif'
    ];

    public function conducteur()
    {
        return $this->belongsTo(Conducteur::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
}
