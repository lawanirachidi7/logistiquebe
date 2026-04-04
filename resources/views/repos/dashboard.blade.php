@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-heartbeat text-danger me-2"></i>Dashboard Fatigue</h1>
            <p class="text-muted mb-0">Surveillance en temps réel de la fatigue des conducteurs</p>
        </div>
        <div>
            <a href="{{ route('repos.generer-tous') }}" class="btn btn-warning" onclick="return confirm('Générer automatiquement les repos pour tous les conducteurs à risque ?')">
                <i class="fas fa-magic me-1"></i> Générer repos automatiques
            </a>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-smile text-success fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ count($analyse['par_niveau']['vert']) }}</h2>
                            <span class="text-muted">En forme</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-meh text-warning fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ count($analyse['par_niveau']['jaune']) }}</h2>
                            <span class="text-muted">Attention</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle p-3" style="background-color: rgba(253, 126, 20, 0.1);">
                                <i class="fas fa-frown fa-2x" style="color: #fd7e14;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ count($analyse['par_niveau']['orange']) }}</h2>
                            <span class="text-muted">Fatigué</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-dizzy text-danger fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0">{{ count($analyse['par_niveau']['rouge']) }}</h2>
                            <span class="text-muted">Critique</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Indicateurs secondaires -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <i class="fas fa-bed text-primary fa-3x mb-3"></i>
                    <h3 class="mb-1">{{ $analyse['statistiques_globales']['conducteurs_en_repos'] }}</h3>
                    <span class="text-muted">En repos actuellement</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <i class="fas fa-chart-line text-info fa-3x mb-3"></i>
                    <h3 class="mb-1">{{ $analyse['statistiques_globales']['score_moyen'] }}%</h3>
                    <span class="text-muted">Score moyen de fatigue</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <i class="fas fa-exclamation-triangle text-danger fa-3x mb-3"></i>
                    <h3 class="mb-1">{{ count($analyse['alertes_critiques']) }}</h3>
                    <span class="text-muted">Alertes critiques</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertes critiques -->
    @if(count($analyse['alertes_critiques']) > 0)
    <div class="card border-0 shadow-sm mb-4 border-start border-danger border-4">
        <div class="card-header bg-danger text-white">
            <i class="fas fa-bell me-2"></i>Alertes Critiques - Action Requise
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @foreach($analyse['alertes_critiques'] as $alerte)
                <div class="list-group-item d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <span class="badge bg-{{ $alerte['niveau'] === 'critique' ? 'danger' : 'warning' }} rounded-pill">
                            <i class="fas {{ $alerte['icone'] ?? 'fa-exclamation' }}"></i>
                        </span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $alerte['titre'] }}</h6>
                                <p class="mb-1 text-muted">{{ $alerte['message'] }}</p>
                                <small class="text-muted">Conducteur: {{ $alerte['conducteur_nom'] }}</small>
                            </div>
                            <div>
                                <a href="{{ route('repos.detail-conducteur', $alerte['conducteur_id']) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Détails
                                </a>
                                <form action="{{ route('repos.generer', $alerte['conducteur_id']) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-bed"></i> Créer repos
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Conducteurs par niveau -->
    <div class="row g-4">
        <!-- Niveau Rouge -->
        @if(count($analyse['par_niveau']['rouge']) > 0)
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm border-start border-danger border-4">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-dizzy me-2"></i>Niveau Critique ({{ count($analyse['par_niveau']['rouge']) }})</span>
                    <span class="badge bg-white text-danger">REPOS OBLIGATOIRE</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($analyse['par_niveau']['rouge'] as $item)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    @include('repos.partials.jauge-fatigue', ['score' => $item['analyse']['score'], 'niveau' => 'rouge'])
                                    <div class="ms-3">
                                        <h6 class="mb-0">{{ $item['conducteur']->prenom }} {{ $item['conducteur']->nom }}</h6>
                                        <small class="text-muted">
                                            {{ $item['analyse']['statistiques']['nuits_consecutives'] }} nuits consec. | 
                                            {{ $item['analyse']['statistiques']['jours_travail_consecutifs'] }} jours sans repos
                                        </small>
                                    </div>
                                </div>
                                <div>
                                    <a href="{{ route('repos.detail-conducteur', $item['conducteur']->id) }}" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Niveau Orange -->
        @if(count($analyse['par_niveau']['orange']) > 0)
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm border-start border-4" style="border-color: #fd7e14!important;">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #fd7e14;">
                    <span><i class="fas fa-frown me-2"></i>Niveau Fatigué ({{ count($analyse['par_niveau']['orange']) }})</span>
                    <span class="badge bg-white" style="color: #fd7e14;">SURVEILLER</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($analyse['par_niveau']['orange'] as $item)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    @include('repos.partials.jauge-fatigue', ['score' => $item['analyse']['score'], 'niveau' => 'orange'])
                                    <div class="ms-3">
                                        <h6 class="mb-0">{{ $item['conducteur']->prenom }} {{ $item['conducteur']->nom }}</h6>
                                        <small class="text-muted">
                                            {{ $item['analyse']['statistiques']['nuits_consecutives'] }} nuits consec. | 
                                            {{ $item['analyse']['statistiques']['jours_travail_consecutifs'] }} jours sans repos
                                        </small>
                                    </div>
                                </div>
                                <div>
                                    <a href="{{ route('repos.detail-conducteur', $item['conducteur']->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Niveau Jaune -->
        @if(count($analyse['par_niveau']['jaune']) > 0)
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm border-start border-warning border-4">
                <div class="card-header bg-warning d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-meh me-2"></i>Niveau Attention ({{ count($analyse['par_niveau']['jaune']) }})</span>
                    <span class="badge bg-white text-warning">À PLANIFIER</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        @foreach($analyse['par_niveau']['jaune'] as $item)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    @include('repos.partials.jauge-fatigue', ['score' => $item['analyse']['score'], 'niveau' => 'jaune'])
                                    <div class="ms-3">
                                        <h6 class="mb-0">{{ $item['conducteur']->prenom }} {{ $item['conducteur']->nom }}</h6>
                                        <small class="text-muted">Score: {{ $item['analyse']['score'] }}%</small>
                                    </div>
                                </div>
                                <a href="{{ route('repos.detail-conducteur', $item['conducteur']->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Niveau Vert -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm border-start border-success border-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-smile me-2"></i>En Forme ({{ count($analyse['par_niveau']['vert']) }})</span>
                    <span class="badge bg-white text-success">DISPONIBLES</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        @foreach($analyse['par_niveau']['vert'] as $item)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    @include('repos.partials.jauge-fatigue', ['score' => $item['analyse']['score'], 'niveau' => 'vert'])
                                    <div class="ms-3">
                                        <h6 class="mb-0">{{ $item['conducteur']->prenom }} {{ $item['conducteur']->nom }}</h6>
                                        <small class="text-success"><i class="fas fa-check"></i> Programmable</small>
                                    </div>
                                </div>
                                <a href="{{ route('repos.detail-conducteur', $item['conducteur']->id) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Repos suggérés -->
    @if(count($analyse['repos_suggeres']) > 0)
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-info text-white">
            <i class="fas fa-lightbulb me-2"></i>Repos Suggérés par le Système
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Conducteur</th>
                            <th>Score</th>
                            <th>Type de repos</th>
                            <th>Durée</th>
                            <th>Dates suggérées</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analyse['repos_suggeres'] as $suggestion)
                        <tr>
                            <td>
                                <strong>{{ $suggestion['conducteur']->prenom }} {{ $suggestion['conducteur']->nom }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-{{ $suggestion['recommandation']['type'] === 'ok' ? 'success' : ($suggestion['score'] >= 85 ? 'danger' : ($suggestion['score'] >= 70 ? 'orange' : 'warning')) }}">
                                    {{ $suggestion['score'] }}%
                                </span>
                            </td>
                            <td>
                                @php
                                    $type = $suggestion['recommandation']['repos_suggere']['type'] ?? 'complet';
                                    $icone = match($type) {
                                        'nuit' => 'fa-moon',
                                        'jour' => 'fa-sun',
                                        default => 'fa-bed'
                                    };
                                @endphp
                                <i class="fas {{ $icone }} me-1"></i>
                                {{ ucfirst($type) }}
                            </td>
                            <td>{{ $suggestion['recommandation']['repos_suggere']['duree'] ?? 1 }} jour(s)</td>
                            <td>
                                {{ \Carbon\Carbon::parse($suggestion['recommandation']['repos_suggere']['date_debut'])->format('d/m/Y') }}
                                →
                                {{ \Carbon\Carbon::parse($suggestion['recommandation']['repos_suggere']['date_fin'])->format('d/m/Y') }}
                            </td>
                            <td>
                                <form action="{{ route('repos.generer', $suggestion['conducteur']->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus"></i> Créer
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .jauge-fatigue {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        font-size: 0.85rem;
    }
    .jauge-vert { background: linear-gradient(135deg, #28a745, #20c997); }
    .jauge-jaune { background: linear-gradient(135deg, #ffc107, #fd7e14); }
    .jauge-orange { background: linear-gradient(135deg, #fd7e14, #dc3545); }
    .jauge-rouge { background: linear-gradient(135deg, #dc3545, #a71d2a); }
</style>
@endpush
@endsection
