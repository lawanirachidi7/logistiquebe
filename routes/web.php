<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ConducteurController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\LigneController;
use App\Http\Controllers\VoyageController;
use App\Http\Controllers\VilleController;
use App\Http\Controllers\StatistiqueController;

// Routes d'authentification (login, register, logout, etc.)
Auth::routes();

// Toutes les routes protégées par authentification
Route::middleware(['auth'])->group(function () {
    
    // Accueil / Dashboard (tous)
    Route::get('/', [VoyageController::class, 'dashboard'])->name('dashboard');
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // ===== ROUTES EN CONSULTATION (tous les rôles) =====
    
    // Conducteurs - Consultation
    Route::get('conducteurs', [ConducteurController::class, 'index'])->name('conducteurs.index');
    Route::get('conducteurs/{conducteur}', [ConducteurController::class, 'show'])->name('conducteurs.show');
    Route::get('conducteurs-statut', [ConducteurController::class, 'statut'])->name('conducteurs.statut');

    // Bus - Consultation
    Route::get('bus', [BusController::class, 'index'])->name('bus.index');
    Route::get('bus/{bus}', [BusController::class, 'show'])->name('bus.show');
    Route::get('bus-disponibilite', [BusController::class, 'disponibilite'])->name('bus.disponibilite');

    // Lignes - Consultation
    Route::get('lignes', [LigneController::class, 'index'])->name('lignes.index');
    Route::get('lignes/{ligne}', [LigneController::class, 'show'])->name('lignes.show');

    // Villes - Consultation
    Route::get('villes', [VilleController::class, 'index'])->name('villes.index');

    // Voyages - Consultation
    Route::get('voyages', [VoyageController::class, 'index'])->name('voyages.index');
    Route::get('voyages/{voyage}', [VoyageController::class, 'show'])->name('voyages.show');
    Route::get('voyages-historique', [VoyageController::class, 'historique'])->name('voyages.historique');
    Route::get('voyages-planification', [VoyageController::class, 'planification'])->name('voyages.planification');
    Route::get('voyages-generer', [VoyageController::class, 'genererForm'])->name('voyages.generer.form');
    Route::get('voyages-edit-date', [VoyageController::class, 'editByDate'])->name('voyages.editByDate');
    Route::get('voyages-count-planifies', [VoyageController::class, 'countPlanifies'])->name('voyages.countPlanifies');
    Route::get('voyages-count-termines', [VoyageController::class, 'countTermines'])->name('voyages.countTermines');
    Route::get('voyages-pdf', [VoyageController::class, 'downloadPDF'])->name('voyages.pdf');

    // Statistiques - Consultation (tous)
    Route::get('statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');
    Route::get('statistiques/conducteurs', [StatistiqueController::class, 'conducteurs'])->name('statistiques.conducteurs');
    Route::get('statistiques/conducteurs/{id}', [StatistiqueController::class, 'conducteurDetail'])->name('statistiques.conducteur.detail');
    Route::get('statistiques/bus', [StatistiqueController::class, 'bus'])->name('statistiques.bus');
    Route::get('statistiques/bus/{id}', [StatistiqueController::class, 'busDetail'])->name('statistiques.bus.detail');
    Route::get('statistiques/lignes', [StatistiqueController::class, 'lignes'])->name('statistiques.lignes');
    Route::get('statistiques/export/conducteurs', [StatistiqueController::class, 'exportConducteurs'])->name('statistiques.export.conducteurs');
    Route::get('statistiques/export/bus', [StatistiqueController::class, 'exportBus'])->name('statistiques.export.bus');

    // ===== ROUTES D'ACTION (admin et opérateur uniquement) =====
    Route::middleware(['can.action'])->group(function () {
        
        // Conducteurs - Actions
        Route::get('conducteurs/create', [ConducteurController::class, 'create'])->name('conducteurs.create');
        Route::post('conducteurs', [ConducteurController::class, 'store'])->name('conducteurs.store');
        Route::get('conducteurs/{conducteur}/edit', [ConducteurController::class, 'edit'])->name('conducteurs.edit');
        Route::put('conducteurs/{conducteur}', [ConducteurController::class, 'update'])->name('conducteurs.update');
        Route::patch('conducteurs/{conducteur}', [ConducteurController::class, 'update']);
        Route::delete('conducteurs/{conducteur}', [ConducteurController::class, 'destroy'])->name('conducteurs.destroy');
        Route::get('conducteurs-import', [ConducteurController::class, 'showImportForm'])->name('conducteurs.import.form');
        Route::post('conducteurs-import', [ConducteurController::class, 'import'])->name('conducteurs.import');
        Route::delete('conducteurs-delete-all', [ConducteurController::class, 'deleteAll'])->name('conducteurs.deleteAll');

        // Bus - Actions
        Route::get('bus/create', [BusController::class, 'create'])->name('bus.create');
        Route::post('bus', [BusController::class, 'store'])->name('bus.store');
        Route::get('bus/{bus}/edit', [BusController::class, 'edit'])->name('bus.edit');
        Route::put('bus/{bus}', [BusController::class, 'update'])->name('bus.update');
        Route::patch('bus/{bus}', [BusController::class, 'update']);
        Route::delete('bus/{bus}', [BusController::class, 'destroy'])->name('bus.destroy');
        Route::post('bus-disponibilite', [BusController::class, 'updateDisponibilite'])->name('bus.disponibilite.update');

        // Lignes - Actions
        Route::get('lignes/create', [LigneController::class, 'create'])->name('lignes.create');
        Route::post('lignes', [LigneController::class, 'store'])->name('lignes.store');
        Route::get('lignes/{ligne}/edit', [LigneController::class, 'edit'])->name('lignes.edit');
        Route::put('lignes/{ligne}', [LigneController::class, 'update'])->name('lignes.update');
        Route::patch('lignes/{ligne}', [LigneController::class, 'update']);
        Route::delete('lignes/{ligne}', [LigneController::class, 'destroy'])->name('lignes.destroy');

        // Villes - Actions
        Route::get('villes/create', [VilleController::class, 'create'])->name('villes.create');
        Route::post('villes', [VilleController::class, 'store'])->name('villes.store');
        Route::get('villes/{ville}/edit', [VilleController::class, 'edit'])->name('villes.edit');
        Route::put('villes/{ville}', [VilleController::class, 'update'])->name('villes.update');
        Route::patch('villes/{ville}', [VilleController::class, 'update']);
        Route::delete('villes/{ville}', [VilleController::class, 'destroy'])->name('villes.destroy');

        // Voyages - Actions
        Route::post('voyages', [VoyageController::class, 'store'])->name('voyages.store');
        Route::get('voyages/{voyage}/edit', [VoyageController::class, 'edit'])->name('voyages.edit');
        Route::put('voyages/{voyage}', [VoyageController::class, 'update'])->name('voyages.update');
        Route::patch('voyages/{voyage}', [VoyageController::class, 'update']);
        Route::delete('voyages/{voyage}', [VoyageController::class, 'destroy'])->name('voyages.destroy');
        Route::delete('voyages-delete-all', [VoyageController::class, 'deleteAll'])->name('voyages.deleteAll');
        Route::put('voyages-update-date', [VoyageController::class, 'updateByDate'])->name('voyages.updateByDate');
        Route::delete('voyages-delete-date', [VoyageController::class, 'deleteByDate'])->name('voyages.deleteByDate');
        Route::post('voyages-validate-date', [VoyageController::class, 'validateByDate'])->name('voyages.validateByDate');
        Route::post('voyages-validate-period', [VoyageController::class, 'validateByPeriod'])->name('voyages.validateByPeriod');
        Route::post('voyages-invalidate-period', [VoyageController::class, 'invalidateByPeriod'])->name('voyages.invalidateByPeriod');
        Route::post('voyages-planification', [VoyageController::class, 'planifier'])->name('voyages.planifier');
        Route::post('voyages/{voyage}/valider', [VoyageController::class, 'valider'])->name('voyages.valider');
        Route::post('voyages-generer', [VoyageController::class, 'generer'])->name('voyages.generer');
        Route::post('voyages-generer-preview', [VoyageController::class, 'previewGeneration'])->name('voyages.generer.preview');
        Route::post('voyages-generer-periode', [VoyageController::class, 'genererSurPeriode'])->name('voyages.generer.periode');
        Route::post('voyages-generer-periode-preview', [VoyageController::class, 'previewPeriode'])->name('voyages.generer.periode.preview');
    });

    // ===== CONFIGURATION (admin uniquement) =====
    Route::prefix('configuration')->name('configuration.')->middleware(['admin'])->group(function () {
        // Paramètres
        Route::get('settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings');
        Route::put('settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/reset', [App\Http\Controllers\SettingController::class, 'reset'])->name('settings.reset');
        
        Route::get('logo', [App\Http\Controllers\SettingController::class, 'logo'])->name('logo');
        Route::post('logo', [App\Http\Controllers\SettingController::class, 'uploadLogo'])->name('logo.upload');
        
        // Gestion des utilisateurs
        Route::get('users', [App\Http\Controllers\UserManagementController::class, 'index'])->name('users.index');
        Route::get('users/create', [App\Http\Controllers\UserManagementController::class, 'create'])->name('users.create');
        Route::post('users', [App\Http\Controllers\UserManagementController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [App\Http\Controllers\UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [App\Http\Controllers\UserManagementController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [App\Http\Controllers\UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::patch('users/{user}/toggle', [App\Http\Controllers\UserManagementController::class, 'toggleStatus'])->name('users.toggle');
        Route::patch('users/{user}/reset-password', [App\Http\Controllers\UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    });
});
