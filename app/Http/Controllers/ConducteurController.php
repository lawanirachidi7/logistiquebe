<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conducteur;
use App\Models\TypeBus;

class ConducteurController extends Controller
{
    /**
     * Affiche le statut des conducteurs (disponibilité, repos, etc.)
     */
    public function statut()
    {
        // Récupérer les conducteurs avec leur statut
        $conducteurs = Conducteur::all();
        return view('conducteurs.statut', compact('conducteurs'));
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $conducteurs = Conducteur::all();
        $fatigueService = app(\App\Services\FatigueService::class);
        $analyseFatigue = [];
        foreach ($conducteurs as $conducteur) {
            $analyseFatigue[$conducteur->id] = $fatigueService->calculerScoreFatigue($conducteur);
        }
        return view('conducteurs.index', compact('conducteurs', 'analyseFatigue'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $typesBus = TypeBus::all();
        return view('conducteurs.create', compact('typesBus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'specialiste_nuit' => 'nullable|string',
            'remplacant_nuit' => 'nullable|string',
            'famille_hors_parakou' => 'nullable|string',
            'actif' => 'nullable|string',
            'types_bus' => 'array',
            'types_bus.*' => 'exists:types_bus,id',
        ]);

        $conducteur = Conducteur::create([
            'nom' => $validatedData['nom'],
            'prenom' => $validatedData['prenom'],
            'specialiste_nuit' => $request->has('specialiste_nuit'),
            'remplacant_nuit' => $request->has('remplacant_nuit'),
            'famille_hors_parakou' => $request->has('famille_hors_parakou'),
            'actif' => $request->has('actif'),
        ]);

        if ($request->has('types_bus')) {
            $conducteur->typesBus()->sync($request->types_bus);
        }

        return redirect()->route('conducteurs.index')->with('success', 'Conducteur ajouté avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $conducteur = Conducteur::with('typesBus')->findOrFail($id);
        return view('conducteurs.show', compact('conducteur'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $conducteur = Conducteur::with('typesBus')->findOrFail($id);
        $typesBus = TypeBus::all();
        return view('conducteurs.edit', compact('conducteur', 'typesBus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $conducteur = Conducteur::findOrFail($id);

        $validatedData = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'specialiste_nuit' => 'nullable|string',
            'remplacant_nuit' => 'nullable|string',
            'famille_hors_parakou' => 'nullable|string',
            'actif' => 'nullable|string',
            'types_bus' => 'array',
            'types_bus.*' => 'exists:types_bus,id',
        ]);

        $conducteur->update([
            'nom' => $validatedData['nom'],
            'prenom' => $validatedData['prenom'],
            'specialiste_nuit' => $request->has('specialiste_nuit'),
            'remplacant_nuit' => $request->has('remplacant_nuit'),
            'famille_hors_parakou' => $request->has('famille_hors_parakou'),
            'actif' => $request->has('actif'),
        ]);

        if ($request->has('types_bus')) {
            $conducteur->typesBus()->sync($request->types_bus);
        } else {
            $conducteur->typesBus()->detach();
        }

        return redirect()->route('conducteurs.index')->with('success', 'Conducteur mis à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $conducteur = Conducteur::findOrFail($id);
        $conducteur->delete();

        return redirect()->route('conducteurs.index')->with('success', 'Conducteur supprimé avec succès');
    }

    /**
     * Affiche le formulaire d'importation
     */
    public function showImportForm()
    {
        $typesBus = TypeBus::all();
        return view('conducteurs.import', compact('typesBus'));
    }

    /**
     * Importe les conducteurs depuis un fichier CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
            'delete_existing' => 'nullable|boolean',
        ]);

        // Supprimer les conducteurs existants si demandé
        if ($request->has('delete_existing') && $request->delete_existing) {
            $this->deleteAllConducteurs();
        }

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Lire l'en-tête
        $header = fgetcsv($handle, 0, ';');
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);
        
        $imported = 0;
        $errors = [];
        $lineNumber = 1;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $lineNumber++;
            
            if (count($row) < 2) {
                $errors[] = "Ligne $lineNumber: données insuffisantes";
                continue;
            }

            $data = array_combine($header, array_pad($row, count($header), ''));
            
            try {
                $conducteur = Conducteur::create([
                    'nom' => $data['nom'] ?? '',
                    'prenom' => $data['prenom'] ?? '',
                    'ville_actuelle' => $data['ville_actuelle'] ?? 'Parakou',
                    'famille_hors_parakou' => $this->parseBoolean($data['famille_hors_parakou'] ?? 'non'),
                    'specialiste_nuit' => $this->parseBoolean($data['specialiste_nuit'] ?? 'non'),
                    'remplacant_nuit' => $this->parseBoolean($data['remplacant_nuit'] ?? 'non'),
                    'actif' => $this->parseBoolean($data['actif'] ?? 'oui'),
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Ligne $lineNumber: " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "$imported conducteur(s) importé(s) avec succès.";
        if (count($errors) > 0) {
            $message .= " " . count($errors) . " erreur(s) rencontrée(s).";
        }

        return redirect()->route('conducteurs.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /**
     * Parse une valeur booléenne depuis une chaîne
     */
    private function parseBoolean($value): bool
    {
        $value = strtolower(trim($value));
        return in_array($value, ['oui', 'yes', '1', 'true', 'vrai']);
    }

    /**
     * Supprime tous les conducteurs et leurs données liées
     */
    private function deleteAllConducteurs(): int
    {
        $count = Conducteur::count();
        
        // Supprimer toutes les données liées aux conducteurs
        \DB::table('conducteur_type_bus')->delete();
        \DB::table('repos_conducteurs')->delete();
        \DB::table('indisponibilites')->delete();
        \DB::table('voyages')->delete();
        
        // Supprimer les conducteurs
        Conducteur::query()->delete();
        
        return $count;
    }

    /**
     * Supprime tous les conducteurs
     */
    public function deleteAll()
    {
        $count = $this->deleteAllConducteurs();

        return redirect()->route('conducteurs.index')
            ->with('success', "$count conducteur(s) supprimé(s) avec succès.");
    }

}
