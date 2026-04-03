<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ville;

class VilleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $villes = Ville::orderBy('nom')->get();
        return view('villes.index', compact('villes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('villes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255|unique:villes',
            'actif' => 'boolean',
        ]);

        if (!$request->has('actif')) {
            $validatedData['actif'] = true;
        }

        Ville::create($validatedData);

        return redirect()->route('villes.index')->with('success', 'Ville ajoutée avec succès');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ville = Ville::findOrFail($id);
        return view('villes.edit', compact('ville'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255|unique:villes,nom,' . $id,
            'actif' => 'boolean',
        ]);

        if (!$request->has('actif')) {
            $validatedData['actif'] = false;
        }

        Ville::whereId($id)->update($validatedData);

        return redirect()->route('villes.index')->with('success', 'Ville mise à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $ville = Ville::findOrFail($id);
        $ville->delete();

        return redirect()->route('villes.index')->with('success', 'Ville supprimée avec succès');
    }
}
