<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ville;

class VilleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $villes = [
            'Parakou',
            'Malanville',
            'Natitingou',
            'Djougou',
            'Kandi',
            'Banikoara',
            'Cotonou',
            'Porto-Novo',
            'Bohicon',
            'Abomey',
            'Lokossa',
            'Ouidah',
        ];

        foreach ($villes as $nom) {
            Ville::firstOrCreate(['nom' => $nom], ['actif' => true]);
        }
    }
}
