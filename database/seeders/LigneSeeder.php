<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ligne;

class LigneSeeder extends Seeder
{
    public function run(): void
    {
        $lignes = [
            // Lignes Nord (départ Parakou)
            ['nom' => 'Parakou-Malanville', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Malanville', 'est_ligne_nord' => true, 'distance_km' => 280, 'duree_estimee' => 360],
            ['nom' => 'Parakou-Natitingou', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Natitingou', 'est_ligne_nord' => true, 'distance_km' => 200, 'duree_estimee' => 300],
            ['nom' => 'Parakou-Djougou', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Djougou', 'est_ligne_nord' => true, 'distance_km' => 135, 'duree_estimee' => 180],
            ['nom' => 'Parakou-Kandi', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Kandi', 'est_ligne_nord' => true, 'distance_km' => 250, 'duree_estimee' => 330],
            ['nom' => 'Parakou-Banikoara', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Banikoara', 'est_ligne_nord' => true, 'distance_km' => 300, 'duree_estimee' => 390],
            
            // Lignes Sud (départ Parakou)
            ['nom' => 'Parakou-Cotonou', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Cotonou', 'est_ligne_nord' => false, 'distance_km' => 415, 'duree_estimee' => 480],
            ['nom' => 'Parakou-Porto-Novo', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Porto-Novo', 'est_ligne_nord' => false, 'distance_km' => 430, 'duree_estimee' => 510],
            ['nom' => 'Parakou-Bohicon', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Bohicon', 'est_ligne_nord' => false, 'distance_km' => 280, 'duree_estimee' => 330],
            ['nom' => 'Parakou-Abomey', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Abomey', 'est_ligne_nord' => false, 'distance_km' => 285, 'duree_estimee' => 345],
            ['nom' => 'Parakou-Lokossa', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Lokossa', 'est_ligne_nord' => false, 'distance_km' => 350, 'duree_estimee' => 420],
            ['nom' => 'Parakou-Ouidah', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Ouidah', 'est_ligne_nord' => false, 'distance_km' => 440, 'duree_estimee' => 510],
            
            // Lignes depuis Cotonou
            ['nom' => 'Cotonou-Parakou', 'ville_depart' => 'Cotonou', 'ville_arrivee' => 'Parakou', 'est_ligne_nord' => false, 'distance_km' => 415, 'duree_estimee' => 480],
            ['nom' => 'Cotonou-Malanville', 'ville_depart' => 'Cotonou', 'ville_arrivee' => 'Malanville', 'est_ligne_nord' => true, 'distance_km' => 695, 'duree_estimee' => 720],
            ['nom' => 'Cotonou-Natitingou', 'ville_depart' => 'Cotonou', 'ville_arrivee' => 'Natitingou', 'est_ligne_nord' => true, 'distance_km' => 615, 'duree_estimee' => 660],
            
            // Lignes locales
            ['nom' => 'Parakou-Tchaourou', 'ville_depart' => 'Parakou', 'ville_arrivee' => 'Tchaourou', 'est_ligne_nord' => false, 'distance_km' => 70, 'duree_estimee' => 90],
        ];
        foreach ($lignes as $ligne) {
            Ligne::firstOrCreate(['nom' => $ligne['nom']], $ligne);
        }
    }
}
