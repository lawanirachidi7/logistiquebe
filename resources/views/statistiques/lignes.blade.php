@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ route('statistiques.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    <h2><i class="fas fa-route"></i> Statistiques des Lignes</h2>
                </div>
            </div>

            <!-- Filtre par période -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('statistiques.lignes') }}" method="GET" class="row g-3 align-items-end">
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
                            <h3 class="mb-0">{{ $statsGlobales['total_lignes'] }}</h3>
                            <small>Lignes Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $statsGlobales['lignes_aller'] }}</h3>
                            <small>Lignes Aller</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $statsGlobales['lignes_retour'] }}</h3>
                            <small>Lignes Retour</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $statsGlobales['total_voyages'] }}</h3>
                            <small>Voyages Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ number_format($statsGlobales['distance_totale'] ?? 0, 0, ',', ' ') }} km</h3>
                            <small>Distance Totale</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tableau des lignes -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-table"></i> Liste Détaillée des Lignes
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover datatable mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="row-num">#</th>
                                    <th>Ligne</th>
                                    <th>Type</th>
                                    <th>Horaire</th>
                                    <th>Trajet</th>
                                    <th class="text-end">Distance</th>
                                    <th class="text-center">Voyages<br>Total</th>
                                    <th class="text-center">Terminés</th>
                                    <th class="text-center">Planifiés</th>
                                    <th class="text-center">Jour</th>
                                    <th class="text-center">Nuit</th>
                                    <th class="text-center">Bus<br>Utilisés</th>
                                    <th class="text-center">Conducteurs<br>Utilisés</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lignes as $ligne)
                                <tr>
                                    <td></td>
                                    <td><strong>{{ $ligne->nom }}</strong></td>
                                    <td>
                                        @if($ligne->type === 'Aller')
                                            <span class="badge bg-primary">Aller</span>
                                        @else
                                            <span class="badge bg-success">Retour</span>
                                        @endif
                                    </td>
                                    <td>{{ $ligne->horaire_formate }}</td>
                                    <td>
                                        <small>{{ $ligne->ville_depart }} → {{ $ligne->ville_arrivee }}</small>
                                    </td>
                                    <td class="text-end">{{ $ligne->distance_km ?? '-' }} km</td>
                                    <td class="text-center">
                                        <span class="badge bg-info fs-6">{{ $ligne->nb_voyages }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $ligne->voyages_termines ?? 0 }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">{{ $ligne->voyages_planifies ?? 0 }}</span>
                                    </td>
                                    <td class="text-center">{{ $ligne->voyages_jour ?? 0 }}</td>
                                    <td class="text-center">{{ $ligne->voyages_nuit ?? 0 }}</td>
                                    <td class="text-center">{{ $ligne->bus_utilises ?? 0 }}</td>
                                    <td class="text-center">{{ $ligne->conducteurs_utilises ?? 0 }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-4">
                                        Aucune ligne trouvée
                                    </td>
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
@endsection
