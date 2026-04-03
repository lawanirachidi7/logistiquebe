<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bus;
use App\Models\TypeBus;

class BusController extends Controller
{
    /**
     * Affiche la page de gestion de disponibilité des bus
     */
    public function disponibilite()
    {
        $bus = Bus::with('typeBus')->get();
        return view('bus.disponibilite', compact('bus'));
    }

    /**
     * Met à jour la disponibilité des bus
     */
    public function updateDisponibilite(Request $request)
    {
        $disponibles = $request->input('disponible', []);
        
        // On remet tout à indisponible
        Bus::query()->update(['disponible' => false]);
        
        // On met à jour ceux qui sont cochés
        if (!empty($disponibles)) {
            Bus::whereIn('id', array_keys($disponibles))->update(['disponible' => true]);
        }

        return redirect()->route('bus.disponibilite')->with('success', 'Disponibilité mise à jour');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buses = Bus::with('typeBus')->get();
        return view('bus.index', compact('buses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $typesBus = TypeBus::all();
        return view('bus.create', compact('typesBus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'immatriculation' => 'required|unique:bus|max:255',
            'type_bus_id' => 'required|exists:types_bus,id',
            'ligne_nord' => 'boolean',
        ]);

        $validatedData['disponible'] = true; // Par défaut
        if (!$request->has('ligne_nord')) {
            $validatedData['ligne_nord'] = false;
        }

        Bus::create($validatedData);

        return redirect()->route('bus.index')->with('success', 'Bus ajouté avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $bus = Bus::with('typeBus')->findOrFail($id);
        return view('bus.show', compact('bus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $bus = Bus::findOrFail($id);
        $typesBus = TypeBus::all();
        return view('bus.edit', compact('bus', 'typesBus'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'immatriculation' => 'required|max:255|unique:bus,immatriculation,' . $id,
            'type_bus_id' => 'required|exists:types_bus,id',
            'ligne_nord' => 'boolean',
            'disponible' => 'boolean',
        ]);

        if (!$request->has('ligne_nord')) $validatedData['ligne_nord'] = false;
        if (!$request->has('disponible')) $validatedData['disponible'] = false;

        Bus::whereId($id)->update($validatedData);

        return redirect()->route('bus.index')->with('success', 'Bus mis à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $bus = Bus::findOrFail($id);

        // Supprimer les voyages associés en cascade
        $bus->voyages()->delete();

        $bus->delete();

        return redirect()->route('bus.index')->with('success', 'Bus et ses voyages associés supprimés avec succès');
    }
}
