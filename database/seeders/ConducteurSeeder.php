<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Conducteur;

class ConducteurSeeder extends Seeder
{
    public function run(): void
    {
        $conducteurs = [
            // Groupe CG - Conducteurs polyvalents (basés à Parakou)
            ['nom' => 'Yves', 'prenom' => 'Ahouandjinou', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Akim', 'prenom' => 'Sanni', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Alphonse', 'prenom' => 'Dossou', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Hibrahim', 'prenom' => 'Moussa', 'ville_actuelle' => 'Malanville', 'famille_hors_parakou' => true, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Nourou', 'prenom' => 'Adamou', 'ville_actuelle' => 'Kandi', 'famille_hors_parakou' => true, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            
            // Conducteurs Scania - Expérimentés
            ['nom' => 'Pascal', 'prenom' => 'Hounkonnou', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => true, 'actif' => true],
            ['nom' => 'Symphorien', 'prenom' => 'Agossou', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => true, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Yobodo', 'prenom' => 'Kossou', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => true, 'actif' => true],
            ['nom' => 'Pacome', 'prenom' => 'Tossou', 'ville_actuelle' => 'Cotonou', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Iliassou', 'prenom' => 'Yaya', 'ville_actuelle' => 'Djougou', 'famille_hors_parakou' => true, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Léon', 'prenom' => 'Gbaguidi', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => true, 'actif' => true],
            ['nom' => 'Hadarou', 'prenom' => 'Bio', 'ville_actuelle' => 'Natitingou', 'famille_hors_parakou' => true, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            
            // Spécialistes nuit - basés à Parakou principalement
            ['nom' => 'Arnaud', 'prenom' => 'Sossou', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => true, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Casimir', 'prenom' => 'Hounkpatin', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => true, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Valère', 'prenom' => 'Ahounou', 'ville_actuelle' => 'Cotonou', 'famille_hors_parakou' => false, 'specialiste_nuit' => true, 'remplacant_nuit' => false, 'actif' => true],
            
            // Conducteurs ligne Nord
            ['nom' => 'Mathieu', 'prenom' => 'Akpata', 'ville_actuelle' => 'Banikoara', 'famille_hors_parakou' => true, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Séverin', 'prenom' => 'Houessou', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => true, 'actif' => true],
            ['nom' => 'Emmanuel', 'prenom' => 'Gbenou', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Rodrigue', 'prenom' => 'Adandé', 'ville_actuelle' => 'Cotonou', 'famille_hors_parakou' => false, 'specialiste_nuit' => true, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Firmin', 'prenom' => 'Kpanou', 'ville_actuelle' => 'Kandi', 'famille_hors_parakou' => true, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            
            // Conducteurs supplémentaires - répartis
            ['nom' => 'Justin', 'prenom' => 'Aïssi', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => true, 'actif' => true],
            ['nom' => 'Barnabé', 'prenom' => 'Dossavi', 'ville_actuelle' => 'Bohicon', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Crépin', 'prenom' => 'Agboton', 'ville_actuelle' => 'Cotonou', 'famille_hors_parakou' => false, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Didier', 'prenom' => 'Mensah', 'ville_actuelle' => 'Porto-Novo', 'famille_hors_parakou' => true, 'specialiste_nuit' => false, 'remplacant_nuit' => false, 'actif' => true],
            ['nom' => 'Eugène', 'prenom' => 'Tokpo', 'ville_actuelle' => 'Parakou', 'famille_hors_parakou' => false, 'specialiste_nuit' => true, 'remplacant_nuit' => false, 'actif' => true],
        ];
        
        foreach ($conducteurs as $c) {
            Conducteur::firstOrCreate(['nom' => $c['nom']], $c);
        }
    }
}
