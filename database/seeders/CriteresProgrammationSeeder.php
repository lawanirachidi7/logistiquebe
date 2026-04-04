<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CritereProgrammation;

class CriteresProgrammationSeeder extends Seeder
{
    /**
     * Initialise les critères de programmation avec les valeurs actuellement utilisées
     */
    public function run(): void
    {
        $this->command->info('Initialisation des critères de programmation...');

        // Utilise la méthode du modèle qui contient tous les critères par défaut
        CritereProgrammation::initDefaults();

        $count = CritereProgrammation::count();
        $this->command->info("{$count} critère(s) de programmation initialisé(s).");

        // Afficher un résumé par catégorie
        $parCategorie = CritereProgrammation::select('categorie')
            ->selectRaw('count(*) as total')
            ->groupBy('categorie')
            ->get();

        foreach ($parCategorie as $cat) {
            $this->command->info("  - {$cat->categorie}: {$cat->total} critère(s)");
        }
    }
}
