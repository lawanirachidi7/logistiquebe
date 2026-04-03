<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeBus;

class TypeBusSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['libelle' => 'CG', 'description' => 'Type CG'],
            ['libelle' => 'Scania', 'description' => 'Type Scania'],
            ['libelle' => 'Néoplan', 'description' => 'Type Néoplan'],
            ['libelle' => 'MarcoPolo', 'description' => 'Type MarcoPolo'],
        ];
        foreach ($types as $type) {
            TypeBus::firstOrCreate(['libelle' => $type['libelle']], $type);
        }
    }
}
