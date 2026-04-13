@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('repos.dashboard') }}">Dashboard Fatigue</a></li>
                    <li class="breadcrumb-item active">{{ $conducteur->prenom }} {{ $conducteur->nom }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">Analyse de fatigue : {{ $conducteur->prenom }} {{ $conducteur->nom }}</h1>
        </div>
        <div>
            <form action="{{ route('repos.generer', $conducteur->id) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-bed me-1"></i> Suggérer un repos
                </button>
            </form>
            <a href="{{ route('repos.create') }}?conducteur_id={{ $conducteur->id }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Créer repos manuel
            </a>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="mb-3">
        <div class="btn-toolbar gap-2" role="toolbar" aria-label="Barre d'actions">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary" title="Retour">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
            <a href="{{ route('conducteurs.show', ['conducteur' => $conducteur->id]) }}" class="btn btn-outline-info" title="Statistiques détaillées">
                <i class="fas fa-chart-bar me-1"></i> Statistiques
            </a>
            <a href="{{ route('voyages.historique') }}?conducteur_id={{ $conducteur->id }}" class="btn btn-outline-primary" title="Historique des voyages">
                <i class="fas fa-route me-1"></i> Historique voyages
            </a>
            <a href="{{ route('repos.index') }}?conducteur_id={{ $conducteur->id }}" class="btn btn-outline-warning" title="Historique des repos">
                <i class="fas fa-bed me-1"></i> Historique repos
            </a>
            <a href="{{ route('statistiques.export.conducteurs', ['conducteur_id' => $conducteur->id]) }}" class="btn btn-outline-success" title="Exporter les données">
                <i class="fas fa-file-excel me-1"></i> Exporter
            </a>
        </div>
    </div>
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
                                stroke="{{ $analyse['couleur'] }}" 
                                stroke-width="12"
                                stroke-linecap="round"
                                stroke-dasharray="{{ 565.5 * ($analyse['score'] / 100) }} 565.5"
                                transform="rotate(-90 100 100)">
                            </circle>
                        </svg>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h1 class="display-4 fw-bold mb-0" style="color: {{ $analyse['couleur'] }}">{{ $analyse['score'] }}%</h1>
                            <span class="text-muted">Fatigue</span>
                        </div>
                    </div>
                    
                    <h4 class="mb-2">
                        @php
                            $niveauLabel = match($analyse['niveau']) {
                                'vert' => 'En forme',
                                'jaune' => 'Attention',
                                'orange' => 'Fatigué',
                                'rouge' => 'Critique',
                                default => 'Inconnu'
                            };
                        @endphp
                        <span class="badge" style="background-color: {{ $analyse['couleur'] }}; font-size: 1rem;">
                            {{ $niveauLabel }}
                        </span>
                    </h4>
                    
                    <p class="text-muted mb-0">{{ $analyse['recommandation']['message'] }}</p>
                </div>
            </div>
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
                                    <h3 class="mb-0">{{ $analyse['statistiques']['nuits_consecutives'] }}</h3>
                                    <span class="text-muted">Nuits consécutives</span>
                                    <div class="progress mt-2" style="height: 6px;">
                                        @php $maxNuits = 3; @endphp
                                        <div class="progress-bar {{ $analyse['statistiques']['nuits_consecutives'] >= $maxNuits ? 'bg-danger' : 'bg-primary' }}" 
                                             style="width: {{ min(100, ($analyse['statistiques']['nuits_consecutives'] / $maxNuits) * 100) }}%"></div>
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
                                    <h3 class="mb-0">{{ $analyse['statistiques']['jours_consecutifs'] }}</h3>
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
                                    <h3 class="mb-0">{{ $analyse['statistiques']['jours_travail_consecutifs'] }}</h3>
                                    <span class="text-muted">Jours sans repos</span>
                                    <div class="progress mt-2" style="height: 6px;">
                                        @php $maxJours = 6; @endphp
                                        <div class="progress-bar {{ $analyse['statistiques']['jours_travail_consecutifs'] >= $maxJours ? 'bg-danger' : 'bg-info' }}" 
                                             style="width: {{ min(100, ($analyse['statistiques']['jours_travail_consecutifs'] / $maxJours) * 100) }}%"></div>
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
                                    <h3 class="mb-0">{{ $analyse['statistiques']['heures_semaine'] }}h</h3>
                                    <span class="text-muted">Cette semaine</span>
                                    <div class="progress mt-2" style="height: 6px;">
                                        @php $maxHeures = 48; @endphp
                                        <div class="progress-bar {{ $analyse['statistiques']['heures_semaine'] >= $maxHeures ? 'bg-danger' : 'bg-success' }}" 
                                             style="width: {{ min(100, ($analyse['statistiques']['heures_semaine'] / $maxHeures) * 100) }}%"></div>
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
                                    @if($analyse['statistiques']['dernier_repos'])
                                        <h5 class="mb-0">{{ \Carbon\Carbon::parse($analyse['statistiques']['dernier_repos'])->format('d/m/Y') }}</h5>
                                        <span class="text-muted">Il y a {{ $analyse['statistiques']['jours_depuis_dernier_repos'] }} jour(s)</span>
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
                                    <h3 class="mb-0">{{ $analyse['statistiques']['total_voyages'] }}</h3>
                                    <span class="text-muted">Voyages (7 derniers jours)</span>
                                    <small class="d-block">
                                        <span class="text-dark">{{ $analyse['statistiques']['voyages_nuit'] }} nuits</span> / 
                                        <span class="text-warning">{{ $analyse['statistiques']['voyages_jour'] }} jours</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Détail des contributions au score -->
    <div class="row g-4 mt-2">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Décomposition du score</h5>
                </div>
                <div class="card-body">
                    @foreach($analyse['details'] as $key => $detail)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <strong>{{ $detail['description'] }}</strong>
                        </div>
                        <div>
                            <span class="badge {{ $detail['contribution'] > 0 ? 'bg-danger' : 'bg-success' }} fs-6">
                                {{ $detail['contribution'] > 0 ? '+' : '' }}{{ $detail['contribution'] }} pts
                            </span>
                        </div>
                    </div>
                    @endforeach
                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <strong class="fs-5">Score total</strong>
                        <span class="badge fs-5" style="background-color: {{ $analyse['couleur'] }}">{{ $analyse['score'] }}%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertes -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Alertes actives</h5>
                </div>
                <div class="card-body">
                    @if(count($analyse['alertes']) > 0)
                        @foreach($analyse['alertes'] as $alerte)
                        <div class="alert alert-{{ $alerte['niveau'] === 'critique' ? 'danger' : ($alerte['niveau'] === 'urgent' ? 'warning' : 'info') }} d-flex align-items-start">
                            <i class="fas {{ $alerte['icone'] ?? 'fa-info-circle' }} me-3 mt-1 fa-lg"></i>
                            <div>
                                <strong>{{ $alerte['titre'] }}</strong>
                                <p class="mb-0 small">{{ $alerte['message'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="mb-0">Aucune alerte active</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Historique des voyages et repos -->
    <div class="row g-4 mt-2">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bus me-2"></i>Voyages récents</h5>
                    <a href="{{ route('voyages.historique') }}?conducteur_id={{ $conducteur->id }}" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($voyagesRecents->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($voyagesRecents as $voyage)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge {{ $voyage->periode === 'Nuit' ? 'bg-dark' : 'bg-warning text-dark' }} me-2">
                                        <i class="fas {{ $voyage->periode === 'Nuit' ? 'fa-moon' : 'fa-sun' }}"></i>
                                        {{ $voyage->periode }}
                                    </span>
                                    <strong>{{ $voyage->ligne->nom ?? 'Ligne supprimée' }}</strong>
                                </div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($voyage->date_depart)->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-route fa-3x mb-3"></i>
                        <p class="mb-0">Aucun voyage récent</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bed me-2"></i>Repos récents</h5>
                    <a href="{{ route('repos.index') }}?conducteur_id={{ $conducteur->id }}" class="btn btn-sm btn-outline-primary">
                        Voir tout
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($reposRecents->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($reposRecents as $repos)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-{{ $repos->badge_couleur }} me-2">
                                        <i class="fas {{ $repos->icone }}"></i>
                                    </span>
                                    <strong>{{ $repos->motif }}</strong>
                                    <small class="text-muted ms-2">({{ $repos->duree }} jour(s))</small>
                                </div>
                                <small class="text-muted">
                                    {{ $repos->date_debut->format('d/m') }} - {{ $repos->date_fin->format('d/m/Y') }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-bed fa-3x mb-3"></i>
                        <p class="mb-0">Aucun repos enregistré</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .circular-progress {
        transform: rotate(-90deg);
    }
</style>
@endpush
@endsection
