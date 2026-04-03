<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Conducteur;
use App\Models\Bus;
use App\Models\TypeBus;
use App\Models\Ligne;
use App\Models\Ville;
use App\Models\Voyage;
use App\Models\Setting;
use App\Models\ReposConducteur;
use App\Models\Indisponibilite;

class ImportDataSeeder extends Seeder
{
    public function run(): void
    {
        $dataFile = database_path('export_data.json');
        
        if (!file_exists($dataFile)) {
            $this->command->error("Fichier d'export non trouvé: $dataFile");
            return;
        }
        
        $data = json_decode(file_get_contents($dataFile), true);
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Import dans l'ordre des dépendances
        if (!empty($data['villes'])) {
            Ville::truncate();
            foreach ($data['villes'] as $item) {
                Ville::create($item);
            }
            $this->command->info("Villes importées: " . count($data['villes']));
        }
        
        if (!empty($data['types_bus'])) {
            TypeBus::truncate();
            foreach ($data['types_bus'] as $item) {
                TypeBus::create($item);
            }
            $this->command->info("Types de bus importés: " . count($data['types_bus']));
        }
        
        if (!empty($data['conducteurs'])) {
            Conducteur::truncate();
            foreach ($data['conducteurs'] as $item) {
                Conducteur::create($item);
            }
            $this->command->info("Conducteurs importés: " . count($data['conducteurs']));
        }
        
        if (!empty($data['bus'])) {
            Bus::truncate();
            foreach ($data['bus'] as $item) {
                Bus::create($item);
            }
            $this->command->info("Bus importés: " . count($data['bus']));
        }
        
        if (!empty($data['lignes'])) {
            Ligne::truncate();
            foreach ($data['lignes'] as $item) {
                Ligne::create($item);
            }
            $this->command->info("Lignes importées: " . count($data['lignes']));
        }
        
        if (!empty($data['voyages'])) {
            Voyage::truncate();
            foreach ($data['voyages'] as $item) {
                Voyage::create($item);
            }
            $this->command->info("Voyages importés: " . count($data['voyages']));
        }
        
        if (!empty($data['users'])) {
            User::truncate();
            foreach ($data['users'] as $item) {
                User::create($item);
            }
            $this->command->info("Utilisateurs importés: " . count($data['users']));
        }
        
        if (!empty($data['settings'])) {
            Setting::truncate();
            foreach ($data['settings'] as $item) {
                Setting::create($item);
            }
            $this->command->info("Paramètres importés: " . count($data['settings']));
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info("Import terminé!");
    }
}
