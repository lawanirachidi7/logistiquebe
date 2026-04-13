<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TypeBus;

class TypeBusController extends Controller
{
    public function index()
    {
        $types = TypeBus::all();
        return view('admin.typebus.index', compact('types'));
    }

    public function create()
    {
        return view('admin.typebus.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:255|unique:types_bus,libelle',
        ]);
        TypeBus::create($request->only('libelle'));
        return redirect()->route('admin.typebus.index')->with('success', 'Type de bus ajouté');
    }

    public function edit(TypeBus $typebus)
    {
        return view('admin.typebus.edit', compact('typebus'));
    }

    public function update(Request $request, TypeBus $typebus)
    {
        $request->validate([
            'libelle' => 'required|string|max:255|unique:types_bus,libelle,' . $typebus->id,
        ]);
        $typebus->update($request->only('libelle'));
        return redirect()->route('admin.typebus.index')->with('success', 'Type de bus modifié');
    }

    public function destroy(TypeBus $typebus)
    {
        $typebus->delete();
        return redirect()->route('admin.typebus.index')->with('success', 'Type de bus supprimé');
    }
}
