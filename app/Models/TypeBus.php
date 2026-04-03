<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypeBus extends Model
{
    protected $table = 'types_bus';

    protected $fillable = [
        'libelle', 'description'
    ];

    public function bus()
    {
        return $this->hasMany(Bus::class);
    }

    public function conducteurs()
    {
        return $this->belongsToMany(Conducteur::class, 'conducteur_type_bus');
    }
}
