<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Affiche la page des paramètres généraux
     */
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        
        // Initialiser les paramètres par défaut s'ils n'existent pas
        if ($settings->isEmpty()) {
            Setting::initDefaults();
            $settings = Setting::all()->groupBy('group');
        }
        
        return view('configuration.settings', compact('settings'));
    }

    /**
     * Met à jour les paramètres
     */
    public function update(Request $request)
    {
        $settings = $request->except('_token', '_method');
        
        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if ($setting) {
                // Traitement spécial pour les checkboxes (booléens)
                if ($setting->type === 'boolean') {
                    $value = $value ? '1' : '0';
                }
                
                $setting->update(['value' => $value]);
                Cache::forget("setting.{$key}");
            }
        }
        
        // S'assurer que les checkboxes non cochées sont mises à 0
        $booleanSettings = Setting::where('type', 'boolean')->pluck('key')->toArray();
        foreach ($booleanSettings as $key) {
            if (!isset($settings[$key])) {
                Setting::where('key', $key)->update(['value' => '0']);
                Cache::forget("setting.{$key}");
            }
        }

        return redirect()->route('configuration.settings')
            ->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Réinitialise les paramètres par défaut
     */
    public function reset()
    {
        // Supprimer tous les paramètres existants
        Setting::truncate();
        
        // Réinitialiser avec les valeurs par défaut
        Setting::initDefaults();
        
        // Vider le cache
        Cache::flush();

        return redirect()->route('configuration.settings')
            ->with('success', 'Paramètres réinitialisés avec succès.');
    }

    /**
     * Aperçu du logo (redirige vers settings)
     */
    public function logo()
    {
        return redirect()->route('configuration.settings');
    }

    /**
     * Upload du logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $file->move(public_path('images'), 'logo.png');
        }

        return redirect()->route('configuration.settings')
            ->with('success', 'Logo mis à jour avec succès.');
    }
}
