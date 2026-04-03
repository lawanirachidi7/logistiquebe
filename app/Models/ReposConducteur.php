<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReposConducteur extends Model
{
    protected $fillable = [
        'conducteur_id', 'date_debut', 'date_fin', 'motif', 'notes'
    ];

    public function conducteur()
    {
        return $this->belongsTo(Conducteur::class);
    }
}
