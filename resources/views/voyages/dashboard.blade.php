@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-tachometer-alt"></i>
                Tableau de Bord
            </h1>
            <p class="page-subtitle">Vue d'ensemble de votre système de gestion</p>
        </div>
        @canaction
        <div class="page-header-actions">
            <a href="{{ route('voyages.generer.form') }}" class="btn btn-success">
                <i class="fas fa-magic"></i> Générer Programmation
            </a>
        </div>
        @endcanaction
    </div>

    <!-- Statistiques Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card primary">
                <i class="fas fa-id-card stat-icon"></i>
                <div class="stat-label">Conducteurs</div>
                <div class="stat-value">{{ $conducteursActifs }}/{{ $totalConducteurs }}</div>
                <a href="{{ route('conducteurs.index') }}" class="stat-link">
                    Gérer <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card success">
                <i class="fas fa-bus stat-icon"></i>
                <div class="stat-label">Bus Disponibles</div>
                <div class="stat-value">{{ $busDisponibles }}/{{ $totalBus }}</div>
                <a href="{{ route('bus.index') }}" class="stat-link">
                    Gérer <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card info">
                <i class="fas fa-route stat-icon"></i>
                <div class="stat-label">Lignes Actives</div>
                <div class="stat-value">{{ $totalLignes }}</div>
                <a href="{{ route('lignes.index') }}" class="stat-link">
                    Gérer <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card warning">
                <i class="fas fa-calendar-check stat-icon"></i>
                <div class="stat-label">Voyages Programmés</div>
                <div class="stat-value">{{ $voyagesProgrammes }}</div>
                <a href="{{ route('voyages.historique') }}" class="stat-link">
                    Voir l'historique <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-bolt"></i> Actions Rapides
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        @canaction
                        <a href="{{ route('voyages.generer.form') }}" class="quick-action-btn success">
                            <i class="fas fa-magic"></i>
                            <span>Générer Programmation</span>
                        </a>
                        <a href="{{ route('voyages.planification') }}" class="quick-action-btn primary">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Planifier un Voyage</span>
                        </a>
                        @endcanaction
                        <a href="{{ route('conducteurs.statut') }}" class="quick-action-btn info">
                            <i class="fas fa-user-check"></i>
                            <span>Statut Conducteurs</span>
                        </a>
                        <a href="{{ route('bus.disponibilite') }}" class="quick-action-btn secondary">
                            <i class="fas fa-bus"></i>
                            <span>Disponibilité Bus</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Data Injection for Dashboard (last 30 days) -->
    @php
        // Use last 30 days for dashboard quick stats
        $dateDebut = now()->subDays(30)->format('Y-m-d');
        $dateFin = now()->format('Y-m-d');
        $topBus = \App\Models\Bus::select('bus.*')
            ->selectRaw('COUNT(voyages.id) as nb_voyages')
            ->leftJoin('voyages', function($join) use ($dateDebut, $dateFin) {
                $join->on('bus.id', '=', 'voyages.bus_id')
                    ->whereRaw("DATE(voyages.date_depart) >= ?", [$dateDebut])
                    ->whereRaw("DATE(voyages.date_depart) <= ?", [$dateFin]);
            })
            ->groupBy('bus.id')
            ->orderByDesc('nb_voyages')
            ->limit(5)
            ->get();
        $topConducteurs = \App\Models\Conducteur::select('conducteurs.*')
            ->selectRaw('COUNT(voyages.id) as nb_voyages')
            ->leftJoin('voyages', function($join) use ($dateDebut, $dateFin) {
                $join->on('conducteurs.id', '=', 'voyages.conducteur_id')
                    ->whereRaw("DATE(voyages.date_depart) >= ?", [$dateDebut])
                    ->whereRaw("DATE(voyages.date_depart) <= ?", [$dateFin]);
            })
            ->groupBy('conducteurs.id')
            ->orderByDesc('nb_voyages')
            ->limit(5)
            ->get();
    @endphp
    <script>
        window.topBusLabels = @json(collect($topBus)->pluck('immatriculation'));
        window.topBusData = @json(collect($topBus)->pluck('nb_voyages'));
        window.topConducteurLabels = @json(collect($topConducteurs)->map(function($c){ return $c->prenom.' '.$c->nom; }));
        window.topConducteurData = @json(collect($topConducteurs)->pluck('nb_voyages'));
    </script>

    <!-- Graphiques Stat -->
    <div class="row mb-4">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-black">
                    <i class="fas fa-chart-bar"></i> Bus les plus utilisés (Top 5)
                </div>
                <div class="card-body">
                    <canvas id="topBusChart" height="491" style="display: block; box-sizing: border-box; height: 434.4px; width: 592.3px;" width="670"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-black">
                    <i class="fas fa-chart-pie"></i> Répartition des voyages par conducteur (Top 5)
                </div>
                <div class="card-body">
                    <canvas id="topConducteurChart" height="670" style="display: block; box-sizing: border-box; height: 592.3px; width: 592px;" width="670"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Derniers Voyages -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-history"></i> Derniers Voyages Programmés
                </div>
                <div class="card-body">
                    @if($derniersVoyages->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped datatable">
                                <thead>
                                    <tr>
                                        <th class="row-num">N°</th>
                                        <th>Date Départ</th>
                                        <th>Ligne</th>
                                        <th>Bus</th>
                                        <th>Conducteur</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($derniersVoyages as $voyage)
                                        <tr>
                                            <td></td>
                                            <td>
                                                <span class="fw-semibold">{{ \Carbon\Carbon::parse($voyage->date_depart)->format('d/m/Y') }}</span>
                                            </td>
                                            <td>{{ optional($voyage->ligne)->nom ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ optional($voyage->bus)->immatriculation ?? 'N/A' }}</span>
                                            </td>
                                            <td>{{ optional($voyage->conducteur)->nom ?? '' }} {{ optional($voyage->conducteur)->prenom ?? 'N/A' }}</td>
                                            <td>
                                                @if($voyage->statut === 'programmé')
                                                    <span class="badge bg-info">{{ ucfirst($voyage->statut) }}</span>
                                                @elseif($voyage->statut === 'terminé')
                                                    <span class="badge bg-success">{{ ucfirst($voyage->statut) }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($voyage->statut) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h4>Aucun voyage récent</h4>
                            <p>Commencez par générer une programmation ou planifier un voyage.</p>
                            <a href="{{ route('voyages.generer.form') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-magic"></i> Générer une programmation
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
