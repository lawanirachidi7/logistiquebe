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
    
    // Accueil / Dashboard
    Route::get('/', [VoyageController::class, 'dashboard'])->name('dashboard');
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // Conducteurs CRUD et statut
    Route::resource('conducteurs', ConducteurController::class);
    Route::get('conducteurs-statut', [ConducteurController::class, 'statut'])->name('conducteurs.statut');
    Route::get('conducteurs-import', [ConducteurController::class, 'showImportForm'])->name('conducteurs.import.form');
    Route::post('conducteurs-import', [ConducteurController::class, 'import'])->name('conducteurs.import');
    Route::delete('conducteurs-delete-all', [ConducteurController::class, 'deleteAll'])->name('conducteurs.deleteAll');

    // Bus CRUD et disponibilité
    Route::resource('bus', BusController::class);
    Route::get('bus-disponibilite', [BusController::class, 'disponibilite'])->name('bus.disponibilite');
    Route::post('bus-disponibilite', [BusController::class, 'updateDisponibilite'])->name('bus.disponibilite.update');

    // Lignes CRUD
    Route::resource('lignes', LigneController::class);

    // Villes CRUD
    Route::resource('villes', VilleController::class)->except(['show']);

    // Voyages : historique, planification, consultation
    Route::get('voyages-historique', [VoyageController::class, 'historique'])->name('voyages.historique');
    Route::delete('voyages-delete-all', [VoyageController::class, 'deleteAll'])->name('voyages.deleteAll');
    Route::get('voyages-edit-date', [VoyageController::class, 'editByDate'])->name('voyages.editByDate');
    Route::put('voyages-update-date', [VoyageController::class, 'updateByDate'])->name('voyages.updateByDate');
    Route::delete('voyages-delete-date', [VoyageController::class, 'deleteByDate'])->name('voyages.deleteByDate');
    Route::post('voyages-validate-date', [VoyageController::class, 'validateByDate'])->name('voyages.validateByDate');
    Route::post('voyages-validate-period', [VoyageController::class, 'validateByPeriod'])->name('voyages.validateByPeriod');
    Route::get('voyages-count-planifies', [VoyageController::class, 'countPlanifies'])->name('voyages.countPlanifies');
    Route::post('voyages-invalidate-period', [VoyageController::class, 'invalidateByPeriod'])->name('voyages.invalidateByPeriod');
    Route::get('voyages-count-termines', [VoyageController::class, 'countTermines'])->name('voyages.countTermines');
    Route::get('voyages-planification', [VoyageController::class, 'planification'])->name('voyages.planification');
    Route::post('voyages-planification', [VoyageController::class, 'planifier'])->name('voyages.planifier');
    Route::post('voyages/{voyage}/valider', [VoyageController::class, 'valider'])->name('voyages.valider');

    // Génération automatique de programmation
    Route::get('voyages-generer', [VoyageController::class, 'genererForm'])->name('voyages.generer.form');
    Route::post('voyages-generer', [VoyageController::class, 'generer'])->name('voyages.generer');
    Route::post('voyages-generer-preview', [VoyageController::class, 'previewGeneration'])->name('voyages.generer.preview');

    // Génération sur période
    Route::post('voyages-generer-periode', [VoyageController::class, 'genererSurPeriode'])->name('voyages.generer.periode');
    Route::post('voyages-generer-periode-preview', [VoyageController::class, 'previewPeriode'])->name('voyages.generer.periode.preview');

    // Téléchargement PDF programmation journalière
    Route::get('voyages-pdf', [VoyageController::class, 'downloadPDF'])->name('voyages.pdf');

    Route::resource('voyages', VoyageController::class)->except(['create']);

    // Statistiques
    Route::get('statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');
    Route::get('statistiques/conducteurs', [StatistiqueController::class, 'conducteurs'])->name('statistiques.conducteurs');
    Route::get('statistiques/conducteurs/{id}', [StatistiqueController::class, 'conducteurDetail'])->name('statistiques.conducteur.detail');
    Route::get('statistiques/bus', [StatistiqueController::class, 'bus'])->name('statistiques.bus');
    Route::get('statistiques/bus/{id}', [StatistiqueController::class, 'busDetail'])->name('statistiques.bus.detail');
    Route::get('statistiques/lignes', [StatistiqueController::class, 'lignes'])->name('statistiques.lignes');
    Route::get('statistiques/export/conducteurs', [StatistiqueController::class, 'exportConducteurs'])->name('statistiques.export.conducteurs');
    Route::get('statistiques/export/bus', [StatistiqueController::class, 'exportBus'])->name('statistiques.export.bus');

    // Configuration
    Route::prefix('configuration')->name('configuration.')->group(function () {
        // Paramètres (accessible aux managers et admins)
        Route::middleware(['manager'])->group(function () {
            Route::get('settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings');
            Route::put('settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
            Route::post('settings/reset', [App\Http\Controllers\SettingController::class, 'reset'])->name('settings.reset');
            
            Route::get('logo', [App\Http\Controllers\SettingController::class, 'logo'])->name('logo');
            Route::post('logo', [App\Http\Controllers\SettingController::class, 'uploadLogo'])->name('logo.upload');
        });
        
        // Gestion des utilisateurs (admin uniquement)
        Route::middleware(['admin'])->group(function () {
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
});
