<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Vérifier que l'utilisateur peut gérer les utilisateurs
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Accès non autorisé');
        }

        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Accès non autorisé');
        }

        $roles = [
            User::ROLE_ADMIN => 'Administrateur',
            User::ROLE_MANAGER => 'Manager',
            User::ROLE_OPERATEUR => 'Opérateur',
        ];

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Accès non autorisé');
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:admin,manager,operateur',
            'actif' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'actif' => $request->has('actif'),
        ]);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Accès non autorisé');
        }

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Accès non autorisé');
        }

        $roles = [
            User::ROLE_ADMIN => 'Administrateur',
            User::ROLE_MANAGER => 'Manager',
            User::ROLE_OPERATEUR => 'Opérateur',
        ];

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Accès non autorisé');
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,manager,operateur',
            'actif' => 'nullable|boolean',
        ];

        // Le mot de passe est optionnel lors de la modification
        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::defaults()];
        }

        $validatedData = $request->validate($rules);

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->role = $validatedData['role'];
        $user->actif = $request->has('actif');

        if ($request->filled('password')) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Utilisateur modifié avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Accès non autorisé');
        }

        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès');
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        if (!auth()->user()->canManageUsers()) {
            abort(403, 'Accès non autorisé');
        }

        // Empêcher la désactivation de son propre compte
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Vous ne pouvez pas désactiver votre propre compte');
        }

        $user->actif = !$user->actif;
        $user->save();

        $status = $user->actif ? 'activé' : 'désactivé';
        return redirect()->route('users.index')->with('success', "Utilisateur {$status} avec succès");
    }
}
