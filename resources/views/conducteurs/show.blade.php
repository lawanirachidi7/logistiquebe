   

@extends('layouts.app')

@section('content')
 
@php
    // Récupération du score de fatigue et stats récentes
    $fatigue = app(\App\Services\FatigueService::class)->calculerScoreFatigue($conducteur);
    $reposRecent = $conducteur->repos()->orderByDesc('date_debut')->first();
    $voyagesRecent = $conducteur->voyages()->orderByDesc('date_depart')->take(5)->get();
@endphp
<div class="container-fluid">
  
    <div class="row g-4">
        <!-- Score principal -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-5">
                    <div class="position-relative d-inline-block mb-4">
                        <!-- Cercle de progression SVG -->
                        <svg width="200" height="200" class="circular-progress">
                            <circle cx="100" cy="100" r="90" fill="none" stroke="#e9ecef" stroke-width="12"></circle>
                            <circle cx="100" cy="100" r="90" fill="none" 
                                stroke="{{ $fatigue['couleur'] }}" 
                                stroke-width="12" stroke-linecap="round"
                                stroke-dasharray="{{ 565.5 * ($fatigue['score'] / 100) }} 565.5"
                                transform="rotate(-90 100 100)">
                            </circle>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h1 class="display-4 fw-bold mb-0" style="color: {{ $fatigue['couleur'] }}">{{ $fatigue['score'] }}%</h1>
                            <span class="text-muted">Fatigue</span>
                        </div>
                    </div>
                    <h4 class="mb-2">
                        @php
                            $niveauLabel = match($fatigue['niveau']) {
                                'vert' => 'En forme',
                                'jaune' => 'Attention',
                                'orange' => 'Fatigué',
                                'rouge' => 'Critique',
                                default => 'Inconnu'
                            };
                        @endphp
                        <span class="badge" style="background-color: {{ $fatigue['couleur'] }}; font-size: 1rem;">
                            {{ $niveauLabel }}
                        </span>
                    </h4>
                    <p class="text-muted mb-0">{{ $fatigue['recommandation']['message'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Détails du Conducteur</div>
                <div class="card-body">
                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Nom</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $conducteur->nom }}</p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Prénom</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $conducteur->prenom }}</p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Spécialiste Nuit</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                @if($conducteur->specialiste_nuit)
                                    <span class="badge bg-secondary">Oui</span>
                                @else
                                    <span class="badge bg-light text-dark">Non</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Remplaçant Nuit</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                @if($conducteur->remplacant_nuit)
                                    <span class="badge bg-secondary">Oui</span>
                                @else
                                    <span class="badge bg-light text-dark">Non</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Famille hors Parakou</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                @if($conducteur->famille_hors_parakou)
                                    <span class="badge bg-secondary">Oui</span>
                                @else
                                    <span class="badge bg-light text-dark">Non</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Actif</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                @if($conducteur->actif)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Types de Bus Autorisés</label>
                        <div class="col-sm-8">
                            @if($conducteur->typesBus->count() > 0)
                                @foreach($conducteur->typesBus as $typeBus)
                                    <span class="badge bg-primary">{{ $typeBus->libelle }}</span>
                                @endforeach
                            @else
                                <p class="form-control-plaintext text-muted">Aucun type de bus spécifique assigné</p>
                            @endif
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('conducteurs.index') }}" class="btn btn-secondary">Retour</a>
                        <a href="{{ route('conducteurs.edit', $conducteur->id) }}" class="btn btn-primary">Modifier</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
         @if($conducteur->voyages && $conducteur->voyages->count() > 0)
            <div class="card ">
                <div class="card-header">Historique des voyages</div>
                <div class="card-body p-0">
                    <div style="max-height: 450px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Ligne</th>
                                    <th>Bus</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($conducteur->voyages as $voyage)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($voyage->date_depart)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $voyage->ligne->nom ?? '-' }}</td>
                                    <td>{{ $voyage->bus->immatriculation ?? '-' }}</td>
                                
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
        <!-- Statistiques détaillées -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Détails de la fatigue</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Nuits consécutives -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-dark text-white p-3">
                                        <i class="fas fa-moon fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0">{{ $fatigue['statistiques']['nuits_consecutives'] ?? 0 }}</h3>
                                    <span class="text-muted">Nuits consécutives</span>
                                    <div class="progress mt-2" style="height: 6px;">
                                        @php $maxNuits = 3; @endphp
                                        <div class="progress-bar {{ $fatigue['statistiques']['nuits_consecutives'] >= $maxNuits ? 'bg-danger' : 'bg-primary' }}" 
                                             style="width: {{ min(100, ($fatigue['statistiques']['nuits_consecutives'] / $maxNuits) * 100) }}%"></div>
                                    </div>
                                    <small class="text-muted">Max: {{ $maxNuits }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Jours consécutifs -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-warning text-white p-3">
                                        <i class="fas fa-sun fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0">{{ $fatigue['statistiques']['jours_consecutifs'] ?? 0 }}</h3>
                                    <span class="text-muted">Jours consécutifs</span>
                                </div>
                            </div>
                        </div>

                        <!-- Jours sans repos -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-info text-white p-3">
                                        <i class="fas fa-calendar-alt fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0">{{ $fatigue['statistiques']['jours_travail_consecutifs'] ?? 0 }}</h3>
                                    <span class="text-muted">Jours sans repos</span>
                                    <div class="progress mt-2" style="height: 6px;">
                                        @php $maxJours = 6; @endphp
                                        <div class="progress-bar {{ $fatigue['statistiques']['jours_travail_consecutifs'] >= $maxJours ? 'bg-danger' : 'bg-info' }}" 
                                             style="width: {{ min(100, ($fatigue['statistiques']['jours_travail_consecutifs'] / $maxJours) * 100) }}%"></div>
                                    </div>
                                    <small class="text-muted">Max: {{ $maxJours }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Heures semaine -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-success text-white p-3">
                                        <i class="fas fa-clock fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0">{{ $fatigue['statistiques']['heures_semaine'] ?? 0 }}h</h3>
                                    <span class="text-muted">Cette semaine</span>
                                    <div class="progress mt-2" style="height: 6px;">
                                        @php $maxHeures = 48; @endphp
                                        <div class="progress-bar {{ $fatigue['statistiques']['heures_semaine'] >= $maxHeures ? 'bg-danger' : 'bg-success' }}" 
                                             style="width: {{ min(100, ($fatigue['statistiques']['heures_semaine'] / $maxHeures) * 100) }}%"></div>
                                    </div>
                                    <small class="text-muted">Max: {{ $maxHeures }}h</small>
                                </div>
                            </div>
                        </div>

                        <!-- Dernier repos -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-secondary text-white p-3">
                                        <i class="fas fa-bed fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    @if($fatigue['statistiques']['dernier_repos'])
                                        <h5 class="mb-0">{{ \Carbon\Carbon::parse($fatigue['statistiques']['dernier_repos'])->format('d/m/Y') }}</h5>
                                        <span class="text-muted">Il y a {{ $fatigue['statistiques']['jours_depuis_dernier_repos'] }} jour(s)</span>
                                    @else
                                        <h5 class="mb-0 text-danger">Aucun</h5>
                                        <span class="text-muted">Pas de repos enregistré</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Total voyages -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="flex-shrink-0">
                                    <div class="rounded-circle bg-primary text-white p-3">
                                        <i class="fas fa-route fa-lg"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h3 class="mb-0">{{ $fatigue['statistiques']['total_voyages'] ?? 0 }}</h3>
                                    <span class="text-muted">Voyages (7 derniers jours)</span>
                                    <small class="d-block">
                                        <span class="text-dark">{{ $fatigue['statistiques']['voyages_nuit'] ?? 0 }} nuits</span> / 
                                        <span class="text-warning">{{ $fatigue['statistiques']['voyages_jour'] ?? 0 }} jours</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

         
    </div>
   
</div>
@endsection
