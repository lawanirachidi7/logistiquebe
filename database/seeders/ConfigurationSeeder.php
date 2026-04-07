<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mettre à jour le premier utilisateur en administrateur
        $firstUser = User::first();
        if ($firstUser) {
            $firstUser->update(['role' => 'admin']);
            $this->command->info("Utilisateur '{$firstUser->name}' défini comme administrateur.");
        }

        // Créer un admin par défaut si aucun utilisateur n'existe
        if (User::count() === 0) {
            User::create([
                'name' => 'Administrateur',
                'email' => 'admin@logistiquebe.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'actif' => true,
            ]);
            $this->command->info("Utilisateur admin créé: admin@logistiquebe.com / password123");
        }
        // Initialiser les paramètres par défaut
        Setting::initDefaults();
        $this->command->info("Paramètres par défaut initialisés.");
    }
}
