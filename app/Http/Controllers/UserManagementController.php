<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Affiche la liste des utilisateurs
     */
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('configuration.users.index', compact('users'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        $roles = User::getRoles();
        return view('configuration.users.create', compact('roles'));
    }

    /**
     * Enregistre un nouvel utilisateur
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(array_keys(User::getRoles()))],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'actif' => true,
        ]);

        return redirect()->route('configuration.users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Affiche le formulaire de modification
     */
    public function edit(User $user)
    {
        $roles = User::getRoles();
        return view('configuration.users.edit', compact('user', 'roles'));
    }

    /**
     * Met à jour un utilisateur
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(array_keys(User::getRoles()))],
            'actif' => 'boolean',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'actif' => $request->has('actif'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('configuration.users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Supprime un utilisateur
     */
    public function destroy(User $user)
    {
        // Empêcher la suppression de son propre compte
        if (auth()->id() === $user->id) {
            return redirect()->route('configuration.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Empêcher la suppression du dernier admin
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('configuration.users.index')
                ->with('error', 'Impossible de supprimer le dernier administrateur.');
        }

        $user->delete();

        return redirect()->route('configuration.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    /**
     * Active/désactive un utilisateur
     */
    public function toggleStatus(User $user)
    {
        // Empêcher la désactivation de son propre compte
        if (auth()->id() === $user->id) {
            return redirect()->route('configuration.users.index')
                ->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['actif' => !$user->actif]);

        $status = $user->actif ? 'activé' : 'désactivé';
        return redirect()->route('configuration.users.index')
            ->with('success', "Utilisateur {$status} avec succès.");
    }

    /**
     * Réinitialise le mot de passe d'un utilisateur
     */
    public function resetPassword(User $user)
    {
        // Nouveau mot de passe temporaire
        $newPassword = 'password123';
        
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        return redirect()->route('configuration.users.index')
            ->with('success', "Mot de passe réinitialisé. Nouveau mot de passe: {$newPassword}");
    }
}
