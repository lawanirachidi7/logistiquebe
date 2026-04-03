@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-bar"></i> Tableau de Bord Statistiques</h2>
                <div class="btn-group">
                    <a href="{{ route('statistiques.conducteurs') }}" class="btn btn-outline-primary">
                        <i class="fas fa-users"></i> Conducteurs
                    </a>
                    <a href="{{ route('statistiques.bus') }}" class="btn btn-outline-success">
                        <i class="fas fa-bus"></i> Bus
                    </a>
                    <a href="{{ route('statistiques.lignes') }}" class="btn btn-outline-info">
                        <i class="fas fa-route"></i> Lignes
                    </a>
                </div>
            </div>

            <!-- Filtre par période -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('statistiques.index') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="date_debut" class="form-label">Date de début</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ $dateDebut }}">
                        </div>
                        <div class="col-md-4">
                            <label for="date_fin" class="form-label">Date de fin</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin" value="{{ $dateFin }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Cartes de résumé -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $stats['total_voyages'] }}</h3>
                            <small>Voyages Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $stats['voyages_termines'] }}</h3>
                            <small>Terminés</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $stats['voyages_planifies'] }}</h3>
                            <small>Planifiés</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ number_format($stats['distance_totale'], 0, ',', ' ') }}</h3>
                            <small>Km Parcourus</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $stats['total_conducteurs'] }}</h3>
                            <small>Conducteurs Actifs</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-dark text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $stats['total_bus'] }}</h3>
                            <small>Bus Disponibles</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Top Conducteurs -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-trophy"></i> Top 5 Conducteurs</span>
                            <a href="{{ route('statistiques.conducteurs') }}" class="btn btn-sm btn-light">Voir tout</a>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Conducteur</th>
                                        <th class="text-center">Voyages</th>
                                        <th class="text-end">Distance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topConducteurs as $index => $conducteur)
                                    <tr>
                                        <td>
                                            @if($index == 0)
                                                <span class="badge bg-warning text-dark"><i class="fas fa-medal"></i> 1</span>
                                            @elseif($index == 1)
                                                <span class="badge bg-secondary">2</span>
                                            @elseif($index == 2)
                                                <span class="badge bg-danger">3</span>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('statistiques.conducteur.detail', $conducteur->id) }}">
                                                {{ $conducteur->prenom }} {{ $conducteur->nom }}
                                            </a>
                                            <br><small class="text-muted">{{ $conducteur->ville_actuelle }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $conducteur->nb_voyages }}</span>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($conducteur->distance_parcourue ?? 0, 0, ',', ' ') }} km
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Aucune donnée</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Top Bus -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-bus"></i> Top 5 Bus</span>
                            <a href="{{ route('statistiques.bus') }}" class="btn btn-sm btn-light">Voir tout</a>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Immatriculation</th>
                                        <th class="text-center">Voyages</th>
                                        <th class="text-end">Distance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topBus as $index => $bus)
                                    <tr>
                                        <td>
                                            @if($index == 0)
                                                <span class="badge bg-warning text-dark"><i class="fas fa-medal"></i> 1</span>
                                            @elseif($index == 1)
                                                <span class="badge bg-secondary">2</span>
                                            @elseif($index == 2)
                                                <span class="badge bg-danger">3</span>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('statistiques.bus.detail', $bus->id) }}">
                                                <strong>{{ $bus->immatriculation }}</strong>
                                            </a>
                                            <br><small class="text-muted">{{ $bus->ville_actuelle }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $bus->nb_voyages }}</span>
                                        </td>
                                        <td class="text-end">
                                            {{ number_format($bus->distance_parcourue ?? 0, 0, ',', ' ') }} km
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Aucune donnée</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Voyages par Période -->
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-sun"></i> Répartition Jour/Nuit
                        </div>
                        <div class="card-body">
                            @php
                                $totalPeriode = array_sum($voyagesParPeriode);
                                $pourcentageJour = $totalPeriode > 0 ? round(($voyagesParPeriode['Jour'] ?? 0) / $totalPeriode * 100) : 0;
                                $pourcentageNuit = $totalPeriode > 0 ? round(($voyagesParPeriode['Nuit'] ?? 0) / $totalPeriode * 100) : 0;
                            @endphp
                            
                            <div class="text-center mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="p-3 bg-warning bg-opacity-25 rounded">
                                            <h3 class="mb-0">{{ $voyagesParPeriode['Jour'] ?? 0 }}</h3>
                                            <small class="text-muted"><i class="fas fa-sun"></i> Jour</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-dark bg-opacity-25 rounded">
                                            <h3 class="mb-0">{{ $voyagesParPeriode['Nuit'] ?? 0 }}</h3>
                                            <small class="text-muted"><i class="fas fa-moon"></i> Nuit</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg-warning" style="width: {{ $pourcentageJour }}%">
                                    @if($pourcentageJour > 10) {{ $pourcentageJour }}% @endif
                                </div>
                                <div class="progress-bar bg-dark" style="width: {{ $pourcentageNuit }}%">
                                    @if($pourcentageNuit > 10) {{ $pourcentageNuit }}% @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Voyages par Ligne -->
                <div class="col-md-8 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-route"></i> Voyages par Ligne</span>
                            <a href="{{ route('statistiques.lignes') }}" class="btn btn-sm btn-light">Voir tout</a>
                        </div>
                        <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th>Ligne</th>
                                        <th>Type</th>
                                        <th class="text-center">Voyages</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $totalLignes = $voyagesParLigne->sum('total'); @endphp
                                    @forelse($voyagesParLigne as $ligne)
                                    <tr>
                                        <td>{{ $ligne->nom }}</td>
                                        <td>
                                            @if($ligne->type === 'Aller')
                                                <span class="badge bg-primary">Aller</span>
                                            @else
                                                <span class="badge bg-success">Retour</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $ligne->total }}</td>
                                        <td>
                                            @php $pct = $totalLignes > 0 ? round($ligne->total / $totalLignes * 100) : 0; @endphp
                                            <div class="progress" style="height: 20px; min-width: 100px;">
                                                <div class="progress-bar" style="width: {{ $pct }}%">{{ $pct }}%</div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">Aucune donnée</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
