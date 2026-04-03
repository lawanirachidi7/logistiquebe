<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bus;

class UpdateBusPositions extends Command
{
    protected $signature = 'bus:update-positions';
    protected $description = 'Met à jour la position des bus basée sur leur dernière programmation';

    public function handle()
    {
        $buses = Bus::all();
        $updated = 0;

        foreach ($buses as $bus) {
            $dernierVoyage = $bus->voyages()
                ->with('ligne')
                ->whereIn('statut', ['Terminé', 'En cours', 'Planifié'])
                ->orderByDesc('date_depart')
                ->first();

            if ($dernierVoyage && $dernierVoyage->ligne) {
                $bus->update(['ville_actuelle' => $dernierVoyage->ligne->ville_arrivee]);
                $this->info("Bus {$bus->immatriculation} -> {$dernierVoyage->ligne->ville_arrivee}");
                $updated++;
            } else {
                $this->line("Bus {$bus->immatriculation} -> Parakou (par defaut)");
            }
        }

        $this->info("Positions mises a jour: {$updated} bus");
        return 0;
    }
}
