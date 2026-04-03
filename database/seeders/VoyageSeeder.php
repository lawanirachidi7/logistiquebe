<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Voyage;
use App\Models\Bus;
use App\Models\Ligne;
use App\Models\Conducteur;
use Carbon\Carbon;

class VoyageSeeder extends Seeder
{
    public function run(): void
    {
        // Programmation Février 2026
        $programmations = [
            // Semaine 1 - Février 2026
            // 01/02/2026 - Dimanche
            ['date_depart' => '2026-02-01 06:00:00', 'bus_immat' => 'BP 3070', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Alphonse', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-01 06:00:00', 'bus_immat' => 'CJ 6320', 'ligne_nom' => 'Parakou-Malanville', 'conducteur_nom' => 'Mathieu', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-01 18:00:00', 'bus_immat' => 'BP 3082', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Symphorien', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // 02/02/2026 - Lundi
            ['date_depart' => '2026-02-02 06:00:00', 'bus_immat' => 'BP 3073', 'ligne_nom' => 'Parakou-Natitingou', 'conducteur_nom' => 'Pascal', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-02 06:00:00', 'bus_immat' => 'CJ 6310', 'ligne_nom' => 'Parakou-Bohicon', 'conducteur_nom' => 'Yves', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-02 14:00:00', 'bus_immat' => 'BP 3070', 'ligne_nom' => 'Cotonou-Parakou', 'conducteur_nom' => 'Alphonse', 'periode' => 'Jour', 'sens' => 'Retour', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-02 18:00:00', 'bus_immat' => 'CG 1001', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Arnaud', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // 03/02/2026 - Mardi
            ['date_depart' => '2026-02-03 06:00:00', 'bus_immat' => 'BS 0396', 'ligne_nom' => 'Parakou-Djougou', 'conducteur_nom' => 'Hadarou', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-03 06:00:00', 'bus_immat' => 'CJ 6321', 'ligne_nom' => 'Parakou-Porto-Novo', 'conducteur_nom' => 'Akim', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-03 18:00:00', 'bus_immat' => 'NP 2001', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Casimir', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // 04/02/2026 - Mercredi
            ['date_depart' => '2026-02-04 06:00:00', 'bus_immat' => 'BP 3085', 'ligne_nom' => 'Parakou-Kandi', 'conducteur_nom' => 'Firmin', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-04 06:00:00', 'bus_immat' => 'CJ 6315', 'ligne_nom' => 'Parakou-Lokossa', 'conducteur_nom' => 'Pacome', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-04 18:00:00', 'bus_immat' => 'MP 3001', 'ligne_nom' => 'Cotonou-Parakou', 'conducteur_nom' => 'Valère', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // 05/02/2026 - Jeudi
            ['date_depart' => '2026-02-05 06:00:00', 'bus_immat' => 'BP 3070', 'ligne_nom' => 'Parakou-Banikoara', 'conducteur_nom' => 'Séverin', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-05 06:00:00', 'bus_immat' => 'CG 1002', 'ligne_nom' => 'Parakou-Abomey', 'conducteur_nom' => 'Hibrahim', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-05 18:00:00', 'bus_immat' => 'NP 2002', 'ligne_nom' => 'Cotonou-Parakou', 'conducteur_nom' => 'Rodrigue', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // 06/02/2026 - Vendredi
            ['date_depart' => '2026-02-06 06:00:00', 'bus_immat' => 'BP 3082', 'ligne_nom' => 'Parakou-Malanville', 'conducteur_nom' => 'Emmanuel', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-06 06:00:00', 'bus_immat' => 'CJ 6318', 'ligne_nom' => 'Parakou-Ouidah', 'conducteur_nom' => 'Nourou', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-06 18:00:00', 'bus_immat' => 'CG 1003', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Eugène', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // 07/02/2026 - Samedi
            ['date_depart' => '2026-02-07 06:00:00', 'bus_immat' => 'BP 3073', 'ligne_nom' => 'Parakou-Natitingou', 'conducteur_nom' => 'Yobodo', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-07 06:00:00', 'bus_immat' => 'MP 3002', 'ligne_nom' => 'Parakou-Bohicon', 'conducteur_nom' => 'Iliassou', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-07 18:00:00', 'bus_immat' => 'CG 1004', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Arnaud', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // Semaine 2 - Février 2026
            // 08/02/2026 - Dimanche
            ['date_depart' => '2026-02-08 06:00:00', 'bus_immat' => 'BS 0396', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Léon', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-08 06:00:00', 'bus_immat' => 'NP 2003', 'ligne_nom' => 'Parakou-Djougou', 'conducteur_nom' => 'Justin', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-08 18:00:00', 'bus_immat' => 'CG 1005', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Casimir', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // 09/02/2026 - Lundi
            ['date_depart' => '2026-02-09 06:00:00', 'bus_immat' => 'BP 3085', 'ligne_nom' => 'Parakou-Kandi', 'conducteur_nom' => 'Barnabé', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-09 06:00:00', 'bus_immat' => 'CJ 6320', 'ligne_nom' => 'Parakou-Porto-Novo', 'conducteur_nom' => 'Crépin', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-09 18:00:00', 'bus_immat' => 'BP 3070', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Symphorien', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // 10/02/2026 - Mardi
            ['date_depart' => '2026-02-10 06:00:00', 'bus_immat' => 'BP 3082', 'ligne_nom' => 'Parakou-Malanville', 'conducteur_nom' => 'Didier', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-10 06:00:00', 'bus_immat' => 'CJ 6310', 'ligne_nom' => 'Parakou-Lokossa', 'conducteur_nom' => 'Alphonse', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-10 18:00:00', 'bus_immat' => 'NP 2001', 'ligne_nom' => 'Cotonou-Parakou', 'conducteur_nom' => 'Valère', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // Semaine 3 - mi-février
            // 15/02/2026 - Dimanche  
            ['date_depart' => '2026-02-15 06:00:00', 'bus_immat' => 'BP 3073', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Pascal', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-15 06:00:00', 'bus_immat' => 'CJ 6321', 'ligne_nom' => 'Parakou-Natitingou', 'conducteur_nom' => 'Mathieu', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-15 18:00:00', 'bus_immat' => 'MP 3001', 'ligne_nom' => 'Cotonou-Parakou', 'conducteur_nom' => 'Rodrigue', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // 20/02/2026 - Vendredi
            ['date_depart' => '2026-02-20 06:00:00', 'bus_immat' => 'BS 0396', 'ligne_nom' => 'Parakou-Banikoara', 'conducteur_nom' => 'Hadarou', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-20 06:00:00', 'bus_immat' => 'CG 1001', 'ligne_nom' => 'Parakou-Abomey', 'conducteur_nom' => 'Yves', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-20 18:00:00', 'bus_immat' => 'CG 1002', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Eugène', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // Semaine 4 - fin février
            // 25/02/2026 - Mercredi
            ['date_depart' => '2026-02-25 06:00:00', 'bus_immat' => 'BP 3085', 'ligne_nom' => 'Parakou-Djougou', 'conducteur_nom' => 'Firmin', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-25 06:00:00', 'bus_immat' => 'CJ 6315', 'ligne_nom' => 'Parakou-Ouidah', 'conducteur_nom' => 'Akim', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-25 18:00:00', 'bus_immat' => 'NP 2002', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Arnaud', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
            
            // 28/02/2026 - Samedi (dernier jour février)
            ['date_depart' => '2026-02-28 06:00:00', 'bus_immat' => 'BP 3070', 'ligne_nom' => 'Parakou-Malanville', 'conducteur_nom' => 'Séverin', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-28 06:00:00', 'bus_immat' => 'CJ 6318', 'ligne_nom' => 'Parakou-Bohicon', 'conducteur_nom' => 'Pacome', 'periode' => 'Jour', 'sens' => 'Aller', 'statut' => 'Terminé'],
            ['date_depart' => '2026-02-28 18:00:00', 'bus_immat' => 'MP 3002', 'ligne_nom' => 'Parakou-Cotonou', 'conducteur_nom' => 'Casimir', 'periode' => 'Nuit', 'sens' => 'Aller', 'statut' => 'Terminé'],
        ];

        foreach ($programmations as $prog) {
            $bus = Bus::where('immatriculation', $prog['bus_immat'])->first();
            $ligne = Ligne::where('nom', $prog['ligne_nom'])->first();
            $conducteur = Conducteur::where('nom', $prog['conducteur_nom'])->first();
            
            if ($bus && $ligne && $conducteur) {
                Voyage::firstOrCreate([
                    'conducteur_id' => $conducteur->id,
                    'bus_id' => $bus->id,
                    'ligne_id' => $ligne->id,
                    'date_depart' => Carbon::parse($prog['date_depart']),
                ], [
                    'periode' => $prog['periode'],
                    'sens' => $prog['sens'],
                    'statut' => $prog['statut'],
                ]);
            }
        }
    }
}
