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
                    <h2><i class="fas fa-bus"></i> Statistiques des Bus</h2>
                </div>
                <a href="{{ route('statistiques.export.bus', ['date_debut' => $dateDebut, 'date_fin' => $dateFin]) }}" 
                   class="btn btn-success">
                    <i class="fas fa-download"></i> Exporter CSV
                </a>
            </div>

            <!-- Filtre par période -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('statistiques.bus') }}" method="GET" class="row g-3 align-items-end">
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
                            <h3 class="mb-0">{{ $statsGlobales['total_bus'] }}</h3>
                            <small>Bus Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $statsGlobales['bus_disponibles'] }}</h3>
                            <small>Disponibles</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ $statsGlobales['total_voyages'] }}</h3>
                            <small>Voyages Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ number_format($statsGlobales['distance_totale'] ?? 0, 0, ',', ' ') }} km</h3>
                            <small>Distance Totale</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h3 class="mb-0">{{ number_format($statsGlobales['moyenne_distance'] ?? 0, 0, ',', ' ') }} km</h3>
                            <small>Moyenne par Bus</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Répartition par type de bus -->
            @if($parTypeBus->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-chart-pie"></i> Répartition par Type de Bus
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($parTypeBus as $type => $data)
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <h5 class="mb-2">{{ $type ?? 'Non défini' }}</h5>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <strong>{{ $data['count'] }}</strong><br>
                                        <small class="text-muted">Bus</small>
                                    </div>
                                    <div class="col-4">
                                        <strong>{{ $data['voyages'] }}</strong><br>
                                        <small class="text-muted">Voyages</small>
                                    </div>
                                    <div class="col-4">
                                        <strong>{{ number_format($data['distance'] ?? 0, 0, ',', ' ') }}</strong><br>
                                        <small class="text-muted">km</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Tableau des bus -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-table"></i> Liste Détaillée des Bus
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover datatable mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="row-num">#</th>
                                    <th>Immatriculation</th>
                                    <th>Type</th>
                                    <th>Ville Actuelle</th>
                                    <th class="text-center">Total<br>Voyages</th>
                                    <th class="text-center">Jour</th>
                                    <th class="text-center">Nuit</th>
                                    <th class="text-center">Aller</th>
                                    <th class="text-center">Retour</th>
                                    <th class="text-end">Distance<br>Parcourue</th>
                                    <th>Dernier Voyage</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bus as $b)
                                <tr class="{{ !$b->disponible ? 'table-secondary' : '' }}">
                                    <td></td>
                                    <td><strong>{{ $b->immatriculation }}</strong></td>
                                    <td>{{ $b->type_bus_nom ?? '-' }}</td>
                                    <td>{{ $b->ville_actuelle ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success fs-6">{{ $b->nb_voyages }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">{{ $b->voyages_jour ?? 0 }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-dark">{{ $b->voyages_nuit ?? 0 }}</span>
                                    </td>
                                    <td class="text-center">{{ $b->voyages_aller ?? 0 }}</td>
                                    <td class="text-center">{{ $b->voyages_retour ?? 0 }}</td>
                                    <td class="text-end">
                                        <strong>{{ number_format($b->distance_parcourue ?? 0, 0, ',', ' ') }} km</strong>
                                    </td>
                                    <td>
                                        @if($b->dernier_voyage)
                                            {{ \Carbon\Carbon::parse($b->dernier_voyage)->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($b->disponible)
                                            <span class="badge bg-success">Disponible</span>
                                        @else
                                            <span class="badge bg-danger">Indisponible</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('statistiques.bus.detail', $b->id) }}" 
                                           class="btn btn-sm btn-outline-success" title="Détails">
                                            <i class="fas fa-chart-line"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-4">
                                        Aucun bus trouvé
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
