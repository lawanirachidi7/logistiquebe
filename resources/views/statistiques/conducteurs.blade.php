@php
    $dateDebut = request('date_debut');
    $dateFin = request('date_fin');
    if (!$dateDebut || !$dateFin) {
        $defaultDebut = $dateDebut ?: now()->startOfMonth()->format('Y-m-d');
        $defaultFin = $dateFin ?: now()->format('Y-m-d');
        header('Location: ' . url()->current() . '?date_debut=' . $defaultDebut . '&date_fin=' . $defaultFin);
        exit;
    }
@endphp
@extends('layouts.app')

@push('styles')
<style>
    .stats-table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        max-width: 100%;
    }
    .stats-table-wrapper::-webkit-scrollbar {
        height: 8px;
    }
    .stats-table-wrapper::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .stats-table-wrapper::-webkit-scrollbar-thumb {
        background: #6f42c1;
        border-radius: 4px;
    }
    .stats-table-wrapper::-webkit-scrollbar-thumb:hover {
        background: #5a32a3;
    }
    .stats-table {
        font-size: 0.85rem;
        min-width: 900px;
    }
    .stats-table th {
        white-space: nowrap;
        font-size: 0.75rem;
        padding: 10px 8px !important;
    }
    .stats-table td {
        padding: 8px !important;
        vertical-align: middle;
    }
    .stats-table .badge {
        font-size: 0.75rem;
        padding: 5px 8px;
        min-width: 40px;
    }
    .stats-card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .stats-card .card-body {
        padding: 20px 15px;
    }
    .stats-card h3 {
        font-size: 1.8rem;
        font-weight: 700;
    }
    @media (max-width: 1400px) {
        .stats-table {
            font-size: 0.8rem;
        }
        .stats-table th {
            font-size: 0.7rem;
            padding: 8px 6px !important;
        }
        .stats-table td {
            padding: 6px !important;
        }
        .stats-table .badge {
            font-size: 0.7rem;
            padding: 4px 6px;
            min-width: 35px;
        }
    }
    @media (max-width: 992px) {
        .stats-card h3 {
            font-size: 1.4rem;
        }
        .stats-card small {
            font-size: 0.7rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-users"></i>
                Statistiques des Conducteurs
            </h1>
            <p class="page-subtitle">Analyse des performances par conducteur</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('statistiques.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <a href="{{ route('statistiques.export.conducteurs', ['date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}" 
               class="btn btn-success">
                <i class="fas fa-download"></i> Exporter CSV
            </a>
        </div>
    </div>

    <!-- Filtre par période -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('statistiques.conducteurs') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label for="date_debut" class="form-label">Date de début</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut" value="{{ $dateDebut }}">
                </div>
                <div class="col-lg-4 col-md-6">
                    <label for="date_fin" class="form-label">Date de fin</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin" value="{{ $dateFin }}">
                </div>
                <div class="col-lg-4 col-md-12">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cartes de résumé -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2">
            <div class="card stats-card bg-primary text-white h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $statsGlobales['total_conducteurs'] }}</h3>
                    <small>Conducteurs Actifs</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card stats-card bg-success text-white h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $statsGlobales['total_voyages'] }}</h3>
                    <small>Voyages Total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card stats-card bg-info text-white h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ number_format($statsGlobales['moyenne_voyages'], 1, ',', ' ') }}</h3>
                    <small>Moyenne/Conducteur</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-2">
            <div class="card stats-card bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ $statsGlobales['max_voyages'] }}</h3>
                    <small>Max Voyages</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card stats-card bg-secondary text-white h-100">
                <div class="card-body text-center">
                    <h3 class="mb-1">{{ number_format($statsGlobales['distance_totale'] ?? 0, 0, ',', ' ') }} km</h3>
                    <small>Distance Totale Parcourue</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des conducteurs -->
    <div class="card">
        <div class="card-header">
            <i class="fas fa-table"></i> Liste Détaillée des Conducteurs
        </div>
        <div class="card-body p-0">
            <div class="stats-table-wrapper">
                <table class="table  table-hover datatable mb-0 stats-table">
                    <thead>
                        <tr>
                            <th class="row-num">#</th>
                            <th>Conducteur</th>
                            <th>Ville</th>
                            <th class="text-center">Principal</th>
                            <th class="text-center">Second</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Jour</th>
                            <th class="text-center">Nuit</th>
                            <th class="text-center">Aller</th>
                            <th class="text-center">Retour</th>
                            <th class="text-end">Distance</th>
                            <th class="no-sort no-export">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conducteurs as $conducteur)
                        <tr class="{{ !$conducteur->actif ? 'table-secondary' : '' }}">
                            <td></td>
                            <td class="text-nowrap">
                                <strong>{{ $conducteur->prenom }} {{ $conducteur->nom }}</strong>
                                @if($conducteur->specialiste_nuit)
                                    <span class="badge bg-dark" title="Spécialiste Nuit"><i class="fas fa-moon"></i></span>
                                @endif
                            </td>
                            <td>{{ $conducteur->ville_actuelle }}</td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $conducteur->nb_voyages_principal }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $conducteur->nb_voyages_second }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">{{ $conducteur->nb_voyages_total }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-oui">{{ $conducteur->voyages_jour ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-dark">{{ $conducteur->voyages_nuit ?? 0 }}</span>
                            </td>
                            <td class="text-center">{{ $conducteur->voyages_aller ?? 0 }}</td>
                            <td class="text-center">{{ $conducteur->voyages_retour ?? 0 }}</td>
                            <td class="text-end text-nowrap">
                                <strong>{{ number_format($conducteur->distance_totale ?? 0, 0, ',', ' ') }}</strong>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('statistiques.conducteur.detail', $conducteur->id)
                                        . (request('date_debut') && request('date_fin') ? ('?date_debut=' . request('date_debut') . '&date_fin=' . request('date_fin')) : '') }}"
                                       class="btn btn-sm btn-info" title="Détails statistiques">
                                        <i class="fas fa-chart-line"></i>
                                    </a>
                                    <a href="{{ route('repos.conducteur.detail', ['id' => $conducteur->id]) }}" class="btn btn-sm btn-outline-info" title="Fatigue">
                                        <i class="fas fa-bed"></i>
                                    </a>
                                    <a href="{{ route('voyages.historique') }}?conducteur_id={{ $conducteur->id }}" class="btn btn-sm btn-outline-primary" title="Voyages">
                                        <i class="fas fa-route"></i>
                                    </a>
                                    <a href="{{ route('repos.index') }}?conducteur_id={{ $conducteur->id }}" class="btn btn-sm btn-outline-warning" title="Repos">
                                        <i class="fas fa-bed"></i>
                                    </a>
                                    <a href="{{ route('repos.export', ['conducteur' => $conducteur->id]) }}" class="btn btn-sm btn-outline-success" title="Exporter">
                                        <i class="fas fa-file-excel"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">
                                Aucun conducteur trouvé
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


