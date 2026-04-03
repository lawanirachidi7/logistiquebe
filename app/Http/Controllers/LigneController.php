<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ligne;
use App\Models\Ville;

class LigneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lignes = Ligne::with('ligneRetourAssociee')->get();
        return view('lignes.index', compact('lignes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $villes = Ville::actif()->orderBy('nom')->get();
        $lignesRetour = Ligne::where('type', 'Retour')->orderBy('nom')->get();
        return view('lignes.create', compact('villes', 'lignesRetour'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'ville_depart' => 'required|string|max:255',
            'ville_arrivee' => 'required|string|max:255|different:ville_depart',
            'nom' => 'required|string|max:255',
            'type' => 'required|in:Aller,Retour',
            'ligne_retour_id' => 'nullable|exists:lignes,id',
            'horaire' => 'required|date_format:H:i',
            'est_ligne_nord' => 'boolean',
            'aller_retour_meme_jour' => 'boolean',
            'distance_km' => 'nullable|integer',
            'duree_estimee' => 'nullable|integer',
        ]);

        // Vérifier unicité nom + horaire
        $existe = Ligne::where('nom', $validatedData['nom'])
            ->where('horaire', $validatedData['horaire'] . ':00')
            ->exists();
        
        if ($existe) {
            return back()->withErrors(['nom' => 'Cette ligne existe déjà avec cet horaire'])->withInput();
        }

        if (!$request->has('est_ligne_nord')) {
            $validatedData['est_ligne_nord'] = false;
        }
        
        if (!$request->has('aller_retour_meme_jour')) {
            $validatedData['aller_retour_meme_jour'] = false;
        }

        // Si c'est une ligne Retour, ne pas assigner de ligne_retour_id
        if ($validatedData['type'] === 'Retour') {
            $validatedData['ligne_retour_id'] = null;
        }

        Ligne::create($validatedData);

        return redirect()->route('lignes.index')->with('success', 'Ligne ajoutée avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $ligne = Ligne::findOrFail($id);
        return view('lignes.show', compact('ligne'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ligne = Ligne::findOrFail($id);
        $villes = Ville::actif()->orderBy('nom')->get();
        $lignesRetour = Ligne::where('type', 'Retour')
            ->where('id', '!=', $id)
            ->orderBy('nom')
            ->get();
        return view('lignes.edit', compact('ligne', 'villes', 'lignesRetour'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'ville_depart' => 'required|string|max:255',
            'ville_arrivee' => 'required|string|max:255|different:ville_depart',
            'nom' => 'required|string|max:255',
            'type' => 'required|in:Aller,Retour',
            'ligne_retour_id' => 'nullable|exists:lignes,id',
            'horaire' => 'required|date_format:H:i',
            'est_ligne_nord' => 'boolean',
            'aller_retour_meme_jour' => 'boolean',
            'distance_km' => 'nullable|integer',
            'duree_estimee' => 'nullable|integer',
        ]);

        // Vérifier unicité nom + horaire (sauf pour la ligne actuelle)
        $existe = Ligne::where('nom', $validatedData['nom'])
            ->where('horaire', $validatedData['horaire'] . ':00')
            ->where('id', '!=', $id)
            ->exists();
        
        if ($existe) {
            return back()->withErrors(['nom' => 'Cette ligne existe déjà avec cet horaire'])->withInput();
        }

        if (!$request->has('est_ligne_nord')) {
            $validatedData['est_ligne_nord'] = false;
        }
        
        if (!$request->has('aller_retour_meme_jour')) {
            $validatedData['aller_retour_meme_jour'] = false;
        }

        // Si c'est une ligne Retour, ne pas assigner de ligne_retour_id
        if ($validatedData['type'] === 'Retour') {
            $validatedData['ligne_retour_id'] = null;
        }

        Ligne::whereId($id)->update($validatedData);

        return redirect()->route('lignes.index')->with('success', 'Ligne mise à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $ligne = Ligne::findOrFail($id);
        $voyagesCount = $ligne->voyages()->count();
        
        // Si force=1 dans la requête, supprimer les voyages d'abord
        if ($request->has('force') && $request->force == '1') {
            $ligne->voyages()->delete();
            $ligne->delete();
            return redirect()->route('lignes.index')
                ->with('success', "Ligne \"{$ligne->nom}\" et ses {$voyagesCount} voyage(s) supprimés avec succès.");
        }
        
        // Vérifier s'il y a des voyages associés
        if ($voyagesCount > 0) {
            return redirect()->route('lignes.index')
                ->with('error', "Impossible de supprimer la ligne \"{$ligne->nom}\" car elle a {$voyagesCount} voyage(s) associé(s).")
                ->with('ligne_a_supprimer', $id);
        }

        $ligne->delete();

        return redirect()->route('lignes.index')->with('success', 'Ligne supprimée avec succès');
    }
}
