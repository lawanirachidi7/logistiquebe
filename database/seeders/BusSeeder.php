<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bus;
use App\Models\TypeBus;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        $scania = TypeBus::where('libelle', 'Scania')->first();
        $cg = TypeBus::where('libelle', 'CG')->first();
        $neoplan = TypeBus::where('libelle', 'Néoplan')->first();
        $marcopolo = TypeBus::where('libelle', 'MarcoPolo')->first();

        $bus = [
            // Bus Scania - Ligne Nord
            ['immatriculation' => 'BP 3070', 'type_bus_id' => $scania->id, 'ligne_nord' => true, 'disponible' => true],
            ['immatriculation' => 'BP 3082', 'type_bus_id' => $scania->id, 'ligne_nord' => true, 'disponible' => true],
            ['immatriculation' => 'BP 3073', 'type_bus_id' => $scania->id, 'ligne_nord' => true, 'disponible' => true],
            ['immatriculation' => 'BS 0396', 'type_bus_id' => $scania->id, 'ligne_nord' => true, 'disponible' => true],
            ['immatriculation' => 'BP 3085', 'type_bus_id' => $scania->id, 'ligne_nord' => true, 'disponible' => true],
            
            // Bus Scania - Standards
            ['immatriculation' => 'CJ 6320', 'type_bus_id' => $scania->id, 'ligne_nord' => false, 'disponible' => true],
            ['immatriculation' => 'CJ 6310', 'type_bus_id' => $scania->id, 'ligne_nord' => false, 'disponible' => true],
            ['immatriculation' => 'CJ 6321', 'type_bus_id' => $scania->id, 'ligne_nord' => false, 'disponible' => true],
            ['immatriculation' => 'CJ 6315', 'type_bus_id' => $scania->id, 'ligne_nord' => false, 'disponible' => true],
            ['immatriculation' => 'CJ 6318', 'type_bus_id' => $scania->id, 'ligne_nord' => false, 'disponible' => true],
            
            // Bus CG
            ['immatriculation' => 'CG 1001', 'type_bus_id' => $cg->id, 'ligne_nord' => false, 'disponible' => true],
            ['immatriculation' => 'CG 1002', 'type_bus_id' => $cg->id, 'ligne_nord' => false, 'disponible' => true],
            ['immatriculation' => 'CG 1003', 'type_bus_id' => $cg->id, 'ligne_nord' => false, 'disponible' => true],
            ['immatriculation' => 'CG 1004', 'type_bus_id' => $cg->id, 'ligne_nord' => false, 'disponible' => true],
            ['immatriculation' => 'CG 1005', 'type_bus_id' => $cg->id, 'ligne_nord' => false, 'disponible' => true],
            
            // Bus Néoplan
            ['immatriculation' => 'NP 2001', 'type_bus_id' => $neoplan->id, 'ligne_nord' => false, 'disponible' => true],
            ['immatriculation' => 'NP 2002', 'type_bus_id' => $neoplan->id, 'ligne_nord' => false, 'disponible' => true],
            ['immatriculation' => 'NP 2003', 'type_bus_id' => $neoplan->id, 'ligne_nord' => true, 'disponible' => true],
            
            // Bus MarcoPolo
            ['immatriculation' => 'MP 3001', 'type_bus_id' => $marcopolo->id, 'ligne_nord' => false, 'disponible' => true],
            ['immatriculation' => 'MP 3002', 'type_bus_id' => $marcopolo->id, 'ligne_nord' => false, 'disponible' => true],
        ];
        
        foreach ($bus as $b) {
            Bus::firstOrCreate(['immatriculation' => $b['immatriculation']], $b);
        }
    }
}
