<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CritereProgrammation;

class CritereProgrammationController extends Controller
{
    /**
     * Affiche la liste des critères de programmation
     */
    public function index()
    {
        // Initialiser les critères par défaut s'ils n'existent pas
        if (CritereProgrammation::count() === 0) {
            CritereProgrammation::initDefaults();
        }

        $criteres = CritereProgrammation::orderBy('categorie')
            ->orderBy('ordre')
            ->get()
            ->groupBy('categorie');

        $categoriesLabels = CritereProgrammation::getCategoriesLabels();
        $categoriesIcons = CritereProgrammation::getCategoriesIcons();

        return view('configuration.criteres.index', compact('criteres', 'categoriesLabels', 'categoriesIcons'));
    }

    /**
     * Met à jour les critères de programmation
     */
    public function update(Request $request)
    {
        $criteres = $request->except('_token', '_method');

        foreach ($criteres as $cle => $valeur) {
            $critere = CritereProgrammation::where('cle', $cle)->first();
            
            if ($critere) {
                // Traitement spécial pour les checkboxes (booléens)
                if ($critere->type === 'boolean') {
                    $valeur = $valeur ? '1' : '0';
                }

                $critere->update(['valeur' => (string) $valeur]);
            }
        }

        // S'assurer que les checkboxes non cochées sont mises à 0
        $booleanCriteres = CritereProgrammation::where('type', 'boolean')->pluck('cle')->toArray();
        foreach ($booleanCriteres as $cle) {
            if (!isset($criteres[$cle])) {
                CritereProgrammation::where('cle', $cle)->update(['valeur' => '0']);
            }
        }

        // Vider le cache
        \Illuminate\Support\Facades\Cache::flush();

        return redirect()->route('configuration.criteres.index')
            ->with('success', 'Critères de programmation mis à jour avec succès.');
    }

    /**
     * Réinitialise tous les critères aux valeurs par défaut
     */
    public function reset()
    {
        CritereProgrammation::resetAll();

        return redirect()->route('configuration.criteres.index')
            ->with('success', 'Tous les critères ont été réinitialisés aux valeurs par défaut.');
    }

    /**
     * Active ou désactive un critère
     */
    public function toggleActive(CritereProgrammation $critere)
    {
        $critere->update(['actif' => !$critere->actif]);

        \Illuminate\Support\Facades\Cache::forget("critere.{$critere->cle}");

        $status = $critere->actif ? 'activé' : 'désactivé';
        return redirect()->route('configuration.criteres.index')
            ->with('success', "Critère \"{$critere->libelle}\" {$status}.");
    }

    /**
     * Exporte les critères en JSON
     */
    public function export()
    {
        $criteres = CritereProgrammation::all()->map(function ($critere) {
            return [
                'cle' => $critere->cle,
                'valeur' => $critere->valeur,
                'actif' => $critere->actif,
            ];
        });

        return response()->json($criteres, 200, [], JSON_PRETTY_PRINT)
            ->header('Content-Disposition', 'attachment; filename="criteres_programmation.json"');
    }

    /**
     * Importe les critères depuis JSON
     */
    public function import(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:json',
        ]);

        try {
            $json = file_get_contents($request->file('fichier')->path());
            $criteres = json_decode($json, true);

            foreach ($criteres as $data) {
                if (isset($data['cle'])) {
                    CritereProgrammation::where('cle', $data['cle'])->update([
                        'valeur' => $data['valeur'] ?? null,
                        'actif' => $data['actif'] ?? true,
                    ]);
                }
            }

            \Illuminate\Support\Facades\Cache::flush();

            return redirect()->route('configuration.criteres.index')
                ->with('success', 'Critères importés avec succès.');
        } catch (\Exception $e) {
            return redirect()->route('configuration.criteres.index')
                ->with('error', 'Erreur lors de l\'importation: ' . $e->getMessage());
        }
    }
}
