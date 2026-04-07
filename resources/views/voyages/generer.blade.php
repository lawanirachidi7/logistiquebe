@extends('layouts.app')

@push('styles')
<style>
    /* Wizard Steps */
    .wizard-steps {
        display: flex;
        justify-content: center;
        margin-bottom: 2rem;
        position: relative;
    }
    
    .wizard-steps::before {
        content: '';
        position: absolute;
        top: 24px;
        left: 20%;
        right: 20%;
        height: 3px;
        background: #e2e8f0;
        z-index: 0;
    }
    
    .wizard-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 1;
        flex: 1;
        max-width: 200px;
    }
    
    .wizard-step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .wizard-step.active .wizard-step-number {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: #fff;
        transform: scale(1.1);
    }
    
    .wizard-step.completed .wizard-step-number {
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff;
    }
    
    .wizard-step-label {
        margin-top: 10px;
        font-size: 0.85rem;
        color: #64748b;
        text-align: center;
        font-weight: 500;
    }
    
    .wizard-step.active .wizard-step-label {
        color: #1e293b;
        font-weight: 600;
    }
    
    /* Mode Cards */
    .mode-card {
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        background: #fff;
        height: 100%;
    }
    
    .mode-card:hover {
        border-color: #3b82f6;
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.15);
    }
    
    .mode-card.selected {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.2);
    }
    
    .mode-card-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 2rem;
        color: #64748b;
        transition: all 0.3s ease;
    }
    
    .mode-card.selected .mode-card-icon {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: #fff;
        transform: scale(1.1);
    }
    
    .mode-card-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    .mode-card-desc {
        font-size: 0.9rem;
        color: #64748b;
    }
    
    /* Periode Buttons */
    .periode-btn {
        padding: 15px 25px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .periode-btn:hover {
        border-color: #3b82f6;
    }
    
    .periode-btn.selected {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    
    .periode-btn i {
        font-size: 1.5rem;
    }
    
    .periode-btn.jour i { color: #f59e0b; }
    .periode-btn.nuit i { color: #6366f1; }
    .periode-btn.tous i { color: #3b82f6; }
    
    /* Date Input Modern */
    .date-input-modern {
        position: relative;
    }
    
    .date-input-modern input {
        padding: 15px 20px;
        font-size: 1.1rem;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    
    .date-input-modern input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    
    .date-input-modern label {
        position: absolute;
        top: -10px;
        left: 15px;
        background: #fff;
        padding: 0 8px;
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
    }
    
    /* Ligne Cards */
    .ligne-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fff;
        margin-bottom: 10px;
    }
    
    .ligne-card:hover {
        border-color: #3b82f6;
        transform: translateX(5px);
    }
    
    .ligne-card.selected {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    
    .ligne-card.aller {
        border-left: 4px solid #3b82f6;
    }
    
    .ligne-card.retour {
        border-left: 4px solid #10b981;
    }
    
    .ligne-card .check-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    
    .ligne-card.selected .check-icon {
        background: #3b82f6;
        border-color: #3b82f6;
        color: #fff;
    }
    
    /* Stats Cards */
    .stat-card {
        background: linear-gradient(135deg, #fff, #f8fafc);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    }
    
    .stat-card-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-size: 1.3rem;
        color: #fff;
    }
    
    .stat-card-icon.blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .stat-card-icon.green { background: linear-gradient(135deg, #10b981, #059669); }
    .stat-card-icon.purple { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
    .stat-card-icon.orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
    
    .stat-card-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .stat-card-label {
        font-size: 0.85rem;
        color: #64748b;
    }
    
    /* Buttons */
    .btn-generate {
        padding: 15px 40px;
        font-size: 1.1rem;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
    }
    
    .btn-preview {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        border: none;
        color: #fff;
    }
    
    .btn-direct {
        background: linear-gradient(135deg, #10b981, #059669);
        border: none;
        color: #fff;
    }
    
    /* Section Card */
    .section-card {
        background: #fff;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        margin-bottom: 20px;
    }
    
    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .section-title i {
        width: 35px;
        height: 35px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }
    
    .section-title i.blue { background: #eff6ff; color: #3b82f6; }
    .section-title i.green { background: #ecfdf5; color: #10b981; }
    .section-title i.purple { background: #f5f3ff; color: #8b5cf6; }
    
    /* Animation */
    .fade-in {
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Info Banner */
    .info-banner {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border-radius: 12px;
        padding: 20px;
        border-left: 4px solid #3b82f6;
    }
    
    .info-banner i {
        font-size: 1.5rem;
        color: #3b82f6;
    }
    
    /* Ville Badge */
    .ville-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #f1f5f9;
        padding: 8px 15px;
        border-radius: 20px;
        margin: 5px;
    }
    
    .ville-badge .count {
        background: #3b82f6;
        color: #fff;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    /* Search lignes */
    .search-lignes {
        position: relative;
        margin-bottom: 15px;
    }
    
    .search-lignes input {
        padding: 12px 20px 12px 45px;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        width: 100%;
        transition: all 0.2s ease;
    }
    
    .search-lignes input:focus {
        border-color: #3b82f6;
        outline: none;
    }
    
    .search-lignes i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold">Générer la Programmation</h2>
            <p class="text-muted mb-0">Créez automatiquement les voyages selon vos paramètres</p>
        </div>
        <a href="{{ route('voyages.historique') }}" class="btn btn-outline-secondary">
            <i class="fas fa-history me-2"></i> Voir l'historique
        </a>
    </div>

    <!-- Wizard Steps -->
    <div class="wizard-steps mb-4">
        <div class="wizard-step active" id="step1Indicator">
            <div class="wizard-step-number">1</div>
            <div class="wizard-step-label">Mode</div>
        </div>
        <div class="wizard-step" id="step2Indicator">
            <div class="wizard-step-number">2</div>
            <div class="wizard-step-label">Dates & Période</div>
        </div>
        <div class="wizard-step" id="step3Indicator">
            <div class="wizard-step-number">3</div>
            <div class="wizard-step-label">Lignes</div>
        </div>
        <div class="wizard-step" id="step4Indicator">
            <div class="wizard-step-number">4</div>
            <div class="wizard-step-label">Générer</div>
        </div>
    </div>

    <form action="{{ route('voyages.generer.preview') }}" method="POST" id="generateForm">
        @csrf
        <input type="hidden" name="mode" id="modeHidden" value="journalier">
        
        <!-- Step 1: Mode Selection -->
        <div class="step-content" id="step1Content">
            <div class="section-card fade-in">
                <div class="section-title">
                    <i class="fas fa-sliders-h blue"></i>
                    Choisissez le mode de programmation
                </div>
                
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mode-card selected" data-mode="journalier" id="modeJournalierCard">
                            <div class="mode-card-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="mode-card-title">Journalière</div>
                            <div class="mode-card-desc">
                                Générez la programmation pour une seule journée avec aperçu détaillé
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mode-card" data-mode="periode" id="modePeriodeCard">
                            <div class="mode-card-icon">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                            <div class="mode-card-title">Sur Période</div>
                            <div class="mode-card-desc">
                                Générez la programmation pour plusieurs jours en respectant l'alternance
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="button" class="btn btn-primary btn-generate" onclick="goToStep(2)">
                    Continuer <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
        
        <!-- Step 2: Dates & Période -->
        <div class="step-content" id="step2Content" style="display: none;">
            <div class="row g-4">
                <!-- Dates -->
                <div class="col-lg-8">
                    <div class="section-card fade-in">
                        <div class="section-title">
                            <i class="fas fa-calendar-alt blue"></i>
                            Sélectionnez les dates
                        </div>
                        
                        <!-- Mode Journalier -->
                        <div id="dateJournalierFields">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="date-input-modern">
                                        <label>Date de programmation</label>
                                        <input type="date" class="form-control" id="date" name="date" 
                                               value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-banner h-100 d-flex align-items-center">
                                        <i class="fas fa-lightbulb me-3"></i>
                                        <div>
                                            <strong>Conseil :</strong> Un aperçu vous permettra de valider les affectations avant la création.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Mode Période -->
                        <div id="datePeriodeFields" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <div class="date-input-modern">
                                        <label>Date de début</label>
                                        <input type="date" class="form-control" id="date_debut" name="date_debut" 
                                               value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-arrow-right text-muted fa-2x"></i>
                                </div>
                                <div class="col-md-5">
                                    <div class="date-input-modern">
                                        <label>Date de fin</label>
                                        <input type="date" class="form-control" id="date_fin" name="date_fin" 
                                               value="{{ date('Y-m-d', strtotime('+7 days')) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="info-banner mt-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-3"></i>
                                    <div>
                                        <strong id="periodeJours">8 jours</strong> de programmation. 
                                        Le système respectera l'alternance aller/retour et les repos des conducteurs.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="section-card fade-in mt-3">
                        <div class="section-title">
                            <i class="fas fa-sun purple"></i>
                            Période de la journée
                        </div>
                        
                        <div class="d-flex flex-wrap gap-3">
                            <label class="periode-btn jour" for="periode_jour">
                                <input type="radio" name="periode" id="periode_jour" value="Jour" class="d-none">
                                <i class="fas fa-sun"></i>
                                <div>
                                    <div class="fw-bold">Jour</div>
                                    <small class="text-muted">07h - 18h</small>
                                </div>
                            </label>
                            <label class="periode-btn nuit" for="periode_nuit">
                                <input type="radio" name="periode" id="periode_nuit" value="Nuit" class="d-none">
                                <i class="fas fa-moon"></i>
                                <div>
                                    <div class="fw-bold">Nuit</div>
                                    <small class="text-muted">19h - 06h</small>
                                </div>
                            </label>
                            <label class="periode-btn tous selected" for="periode_tous">
                                <input type="radio" name="periode" id="periode_tous" value="Les deux" class="d-none" checked>
                                <i class="fas fa-clock"></i>
                                <div>
                                    <div class="fw-bold">Les deux</div>
                                    <small class="text-muted">Jour & Nuit</small>
                                </div>
                            </label>
                            </div>

                            <!-- Forcer la nuit (exception opérateur) -->
                            <div class="form-check mt-3" id="forceNuitContainer" style="display: none;">
                                <input class="form-check-input" type="checkbox" id="force_nuit" name="force_nuit" value="1">
                                <label class="form-check-label" for="force_nuit">
                                    Forcer la nuit (exception opérateur)
                                </label>
                                <small class="text-muted d-block">À cocher uniquement si l'opérateur autorise un conducteur de jour à travailler la nuit exceptionnellement.</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats -->
                <div class="col-lg-4">
                    <div class="section-card fade-in h-100">
                        <div class="section-title">
                            <i class="fas fa-chart-pie green"></i>
                            Ressources disponibles
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-card-icon blue">
                                        <i class="fas fa-route"></i>
                                    </div>
                                    <div class="stat-card-value">{{ \App\Models\Ligne::where('type', 'Aller')->count() }}</div>
                                    <div class="stat-card-label">Lignes Aller</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-card-icon green">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <div class="stat-card-value">{{ \App\Models\Bus::where('disponible', true)->count() }}</div>
                                    <div class="stat-card-label">Bus dispo.</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-card-icon purple">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="stat-card-value">{{ \App\Models\Conducteur::where('actif', true)->count() }}</div>
                                    <div class="stat-card-label">Conducteurs</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card">
                                    <div class="stat-card-icon orange">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="stat-card-value" id="voyagesEstimate">-</div>
                                    <div class="stat-card-label">Voyages estimés</div>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-3">
                        
                        <div class="small text-muted mb-2">Conducteurs par ville :</div>
                        <div>
                            @php
                                $conducteursParVille = \App\Models\Conducteur::where('actif', true)
                                    ->selectRaw('ville_actuelle, COUNT(*) as total')
                                    ->groupBy('ville_actuelle')
                                    ->get();
                            @endphp
                            @foreach($conducteursParVille as $ville)
                            <span class="ville-badge">
                                <i class="fas fa-map-marker-alt text-primary"></i>
                                {{ $ville->ville_actuelle }}
                                <span class="count">{{ $ville->total }}</span>
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary btn-generate" onclick="goToStep(1)">
                    <i class="fas fa-arrow-left me-2"></i> Retour
                </button>
                <button type="button" class="btn btn-primary btn-generate" onclick="goToStep(3)">
                    Continuer <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
        
        <!-- Step 3: Lignes -->
        <div class="step-content" id="step3Content" style="display: none;">
            <div class="section-card fade-in">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="section-title mb-0">
                        <i class="fas fa-route blue"></i>
                        Sélectionnez les lignes à programmer
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">
                            <i class="fas fa-check-double"></i> Tout sélectionner
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                            <i class="fas fa-times"></i> Tout désélectionner
                        </button>
                    </div>
                </div>
                
                <div class="search-lignes">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchLignes" placeholder="Rechercher une ligne...">
                </div>
                
                <div class="row">
                    <!-- Lignes Aller -->
                    <div class="col-md-6">
                        <h6 class="mb-3 d-flex align-items-center">
                            <span class="badge bg-primary me-2">Aller</span>
                            <span class="text-muted small" id="countAller">{{ count($lignesAller) }} lignes</span>
                        </h6>
                        <div class="lignes-list" style="max-height: 400px; overflow-y: auto;">
                            @foreach($lignesAller as $ligne)
                            <div class="ligne-card aller selected" data-ligne-id="{{ $ligne->id }}" data-ligne-nom="{{ strtolower($ligne->nom) }}">
                                <div class="d-flex align-items-center">
                                    <div class="check-icon me-3">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <input type="checkbox" name="lignes[]" value="{{ $ligne->id }}" 
                                           id="ligne_{{ $ligne->id }}" class="d-none ligne-checkbox" checked>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ $ligne->nom }}</div>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ $ligne->horaire_formate }}
                                            @if($ligne->ligneRetourAssociee)
                                            <span class="ms-2"><i class="fas fa-exchange-alt"></i> {{ $ligne->ligneRetourAssociee->horaire_formate }}</span>
                                            @endif
                                        </small>
                                    </div>
                                    <span class="badge bg-light text-dark">{{ $ligne->distance_km ?? '?' }} km</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Lignes Retour -->
                    <div class="col-md-6">
                        <h6 class="mb-3 d-flex align-items-center">
                            <span class="badge bg-success me-2">Retour</span>
                            <span class="text-muted small">{{ count($lignesRetour) }} lignes (optionnel)</span>
                        </h6>
                        <div class="lignes-list" style="max-height: 400px; overflow-y: auto;">
                            @foreach($lignesRetour as $ligne)
                            <div class="ligne-card retour" data-ligne-id="{{ $ligne->id }}" data-ligne-nom="{{ strtolower($ligne->nom) }}">
                                <div class="d-flex align-items-center">
                                    <div class="check-icon me-3">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <input type="checkbox" name="lignes[]" value="{{ $ligne->id }}" 
                                           id="ligne_{{ $ligne->id }}" class="d-none ligne-checkbox">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">{{ $ligne->nom }}</div>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>{{ $ligne->horaire_formate }}
                                        </small>
                                    </div>
                                    <span class="badge bg-light text-dark">{{ $ligne->distance_km ?? '?' }} km</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="mt-3 text-center">
                    <span class="badge bg-primary fs-6 px-3 py-2" id="lignesCount">
                        <i class="fas fa-check-circle me-1"></i> {{ count($lignesAller) }} lignes sélectionnées
                    </span>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <button type="button" class="btn btn-outline-secondary btn-generate" onclick="goToStep(2)">
                    <i class="fas fa-arrow-left me-2"></i> Retour
                </button>
                <button type="button" class="btn btn-primary btn-generate" onclick="goToStep(4)">
                    Continuer <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
        
        <!-- Step 4: Generate -->
        <div class="step-content" id="step4Content" style="display: none;">
            <div class="section-card fade-in">
                <div class="text-center py-4">
                    <div style="width: 100px; height: 100px; margin: 0 auto 20px; background: linear-gradient(135deg, #10b981, #059669); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-magic fa-3x text-white"></i>
                    </div>
                    <h3 class="mb-3">Prêt à générer !</h3>
                    <p class="text-muted mb-4" id="summaryText">
                        Programmation journalière pour le <strong id="summaryDate">-</strong><br>
                        <span id="summaryPeriode">Jour & Nuit</span> · <span id="summaryLignes">- lignes</span>
                    </p>
                    
                    <div class="d-flex justify-content-center gap-3 flex-wrap" id="jourBtns">
                        <button type="submit" class="btn btn-preview btn-generate" formaction="{{ route('voyages.generer.preview') }}">
                            <i class="fas fa-eye me-2"></i> Aperçu avant génération
                        </button>
                        <button type="submit" class="btn btn-direct btn-generate" formaction="{{ route('voyages.generer') }}">
                            <i class="fas fa-rocket me-2"></i> Générer directement
                        </button>
                    </div>
                    
                    <!-- Boutons pour période (cachés par défaut) -->
                    <div class="justify-content-center gap-3 flex-wrap" id="periodeBtns" style="display: none;">
                        <button type="submit" class="btn btn-preview btn-generate" formaction="{{ route('voyages.generer.periode.preview') }}">
                            <i class="fas fa-eye me-2"></i> Aperçu de la période
                        </button>
                        <button type="submit" class="btn btn-direct btn-generate" formaction="{{ route('voyages.generer.periode') }}">
                            <i class="fas fa-calendar-check me-2"></i> Générer la période
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <button type="button" class="btn btn-outline-secondary btn-generate" onclick="goToStep(3)">
                    <i class="fas fa-arrow-left me-2"></i> Retour
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var currentStep = 1;
    var selectedMode = 'journalier';
    
    // Mode selection
    $('.mode-card').on('click', function() {
        $('.mode-card').removeClass('selected');
        $(this).addClass('selected');
        selectedMode = $(this).data('mode');
        $('#modeHidden').val(selectedMode);
        
        if (selectedMode === 'periode') {
            $('#dateJournalierFields').hide();
            $('#datePeriodeFields').show();
            $('#jourBtns').hide();
            $('#periodeBtns').css('display', 'flex');
        } else {
            $('#dateJournalierFields').show();
            $('#datePeriodeFields').hide();
            $('#jourBtns').css('display', 'flex');
            $('#periodeBtns').hide();
        }
        
        updateSummary();
    });
    
    // Periode buttons
    $('.periode-btn').on('click', function() {
        $('.periode-btn').removeClass('selected');
        $(this).addClass('selected');
        updateSummary();

        // Afficher la case à cocher "forcer la nuit" uniquement si "Nuit" est sélectionné
        var periode = $(this).find('input').val();
        if (periode === 'Nuit') {
            $('#forceNuitContainer').show();
        } else {
            $('#forceNuitContainer').hide();
            $('#force_nuit').prop('checked', false);
        }
    });
    
    // Ligne cards
    $('.ligne-card').on('click', function() {
        $(this).toggleClass('selected');
        var checkbox = $(this).find('.ligne-checkbox');
        checkbox.prop('checked', $(this).hasClass('selected'));
        updateLignesCount();
        updateSummary();
    });
    
    // Select/Deselect all
    $('#selectAll').on('click', function() {
        $('.ligne-card').addClass('selected');
        $('.ligne-checkbox').prop('checked', true);
        updateLignesCount();
        updateSummary();
    });
    
    $('#deselectAll').on('click', function() {
        $('.ligne-card').removeClass('selected');
        $('.ligne-checkbox').prop('checked', false);
        updateLignesCount();
        updateSummary();
    });
    
    // Search lignes
    $('#searchLignes').on('input', function() {
        var search = $(this).val().toLowerCase();
        $('.ligne-card').each(function() {
            var nom = $(this).data('ligne-nom');
            if (nom.includes(search)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Date validation
    $('#date_debut, #date_fin').on('change', function() {
        var dateDebut = $('#date_debut').val();
        var dateFin = $('#date_fin').val();
        
        if (dateDebut && dateFin) {
            if (dateFin < dateDebut) {
                alert('La date de fin doit être supérieure ou égale à la date de début');
                $('#date_fin').val(dateDebut);
                dateFin = dateDebut;
            }
            
            var debut = new Date(dateDebut);
            var fin = new Date(dateFin);
            var diff = Math.ceil((fin - debut) / (1000 * 60 * 60 * 24)) + 1;
            $('#periodeJours').text(diff + ' jour' + (diff > 1 ? 's' : ''));
        }
        
        updateSummary();
    });
    
    $('#date').on('change', function() {
        updateSummary();
    });
    
    function updateLignesCount() {
        var count = $('.ligne-checkbox:checked').length;
        var total = $('.ligne-checkbox').length;
        
        if (count === 0) {
            $('#lignesCount').html('<i class="fas fa-info-circle me-1"></i> Toutes les lignes seront utilisées');
        } else {
            $('#lignesCount').html('<i class="fas fa-check-circle me-1"></i> ' + count + ' ligne' + (count > 1 ? 's' : '') + ' sélectionnée' + (count > 1 ? 's' : ''));
        }
        
        // Update estimate
        var periode = $('input[name="periode"]:checked').val();
        var multiplier = periode === 'Les deux' ? 2 : 1;
        var estimate = count * multiplier;
        
        if (selectedMode === 'periode') {
            var dateDebut = $('#date_debut').val();
            var dateFin = $('#date_fin').val();
            if (dateDebut && dateFin) {
                var debut = new Date(dateDebut);
                var fin = new Date(dateFin);
                var days = Math.ceil((fin - debut) / (1000 * 60 * 60 * 24)) + 1;
                estimate = count * multiplier * days;
            }
        }
        
        $('#voyagesEstimate').text(estimate > 0 ? '~' + estimate : '-');
    }
    
    function updateSummary() {
        var count = $('.ligne-checkbox:checked').length;
        var periodeText = $('input[name="periode"]:checked').val();
        
        if (selectedMode === 'journalier') {
            var date = $('#date').val();
            var dateFormatted = date ? new Date(date).toLocaleDateString('fr-FR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) : '-';
            $('#summaryDate').text(dateFormatted);
            $('#summaryText').html('Programmation journalière pour le <strong>' + dateFormatted + '</strong><br><span id="summaryPeriode">' + (periodeText === 'Les deux' ? 'Jour & Nuit' : periodeText) + '</span> · <span id="summaryLignes">' + (count || 'Toutes les') + ' lignes</span>');
        } else {
            var dateDebut = $('#date_debut').val();
            var dateFin = $('#date_fin').val();
            var debutFormatted = dateDebut ? new Date(dateDebut).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) : '-';
            var finFormatted = dateFin ? new Date(dateFin).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';
            $('#summaryText').html('Programmation sur période du <strong>' + debutFormatted + '</strong> au <strong>' + finFormatted + '</strong><br><span id="summaryPeriode">' + (periodeText === 'Les deux' ? 'Jour & Nuit' : periodeText) + '</span> · <span id="summaryLignes">' + (count || 'Toutes les') + ' lignes</span>');
        }
        
        updateLignesCount();
    }
    
    // Initial updates
    updateLignesCount();
    updateSummary();
});

function goToStep(step) {
    // Hide all step contents
    $('.step-content').hide();
    
    // Show target step
    $('#step' + step + 'Content').show().addClass('fade-in');
    
    // Update indicators
    for (var i = 1; i <= 4; i++) {
        var indicator = $('#step' + i + 'Indicator');
        indicator.removeClass('active completed');
        
        if (i < step) {
            indicator.addClass('completed');
        } else if (i === step) {
            indicator.addClass('active');
        }
    }
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>
@endpush
