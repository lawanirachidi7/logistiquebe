<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    protected $fillable = ['nom', 'actif'];

    protected $casts = [
        'actif' => 'boolean',
    ];

    public function scopeActif($query)
    {
        return $query->where('actif', true);
    }
}
