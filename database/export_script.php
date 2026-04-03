<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$data = [
    'users' => \App\Models\User::all()->toArray(),
    'conducteurs' => \App\Models\Conducteur::all()->toArray(),
    'bus' => \App\Models\Bus::all()->toArray(),
    'types_bus' => \App\Models\TypeBus::all()->toArray(),
    'lignes' => \App\Models\Ligne::all()->toArray(),
    'villes' => \App\Models\Ville::all()->toArray(),
    'voyages' => \App\Models\Voyage::all()->toArray(),
    'settings' => \App\Models\Setting::all()->toArray(),
];

file_put_contents(__DIR__ . '/../database/export_data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Export terminé! " . count($data['users']) . " users, " . count($data['conducteurs']) . " conducteurs, " . count($data['bus']) . " bus, " . count($data['lignes']) . " lignes, " . count($data['voyages']) . " voyages\n";
