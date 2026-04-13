@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="{{ route('statistiques.bus') }}" class="btn btn-outline-secondary btn-sm mb-2">
                        <i class="fas fa-arrow-left"></i> Retour aux bus
                    </a>
                    <h2>
                        <i class="fas fa-bus"></i> {{ $bus->immatriculation }}
                        @if($bus->typeBus)
                            <span class="badge bg-info">{{ $bus->typeBus->nom }}</span>
                        @endif
                        @if(!$bus->disponible)
                            <span class="badge bg-danger">Indisponible</span>
                        @endif
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-map-marker-alt"></i> Position actuelle: {{ $bus->ville_actuelle ?? 'Non définie' }}
                        @if($bus->ligne_nord)
                            | <span class="badge bg-secondary">Ligne Nord</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Filtre par période -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('statistiques.bus.detail', $bus->id) }}" method="GET" class="row g-3 align-items-end">
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

            <!-- Statistiques résumées -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-primary text-white h-100">
                        <div class="card-body text-center">
                            <h2 class="mb-0">{{ $stats['total_voyages'] }}</h2>
                            <small>Total Voyages</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white h-100">
                        <div class="card-body text-center">
                            <h2 class="mb-0">{{ $stats['voyages_termines'] }}</h2>
                            <small>Terminés</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-dark h-100">
                        <div class="card-body text-center">
                            <h2 class="mb-0">{{ $stats['voyages_jour'] }}</h2>
                            <small><i class="fas fa-sun"></i> Jour</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-dark text-white h-100">
                        <div class="card-body text-center">
                            <h2 class="mb-0">{{ $stats['voyages_nuit'] }}</h2>
                            <small><i class="fas fa-moon"></i> Nuit</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-info text-white h-100">
                        <div class="card-body text-center">
                            <h2 class="mb-0">{{ number_format($stats['distance_totale'] ?? 0, 0, ',', ' ') }} km</h2>
                            <small>Distance Parcourue</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Répartition Aller/Retour -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-info text-black">
                            <i class="fas fa-chart-pie"></i> Répartition Aller/Retour
                        </div>
                        <div class="card-body">
                            <div class="row text-center mb-3">
                                <div class="col-6">
                                    <div class="p-3 bg-primary bg-opacity-25 rounded">
                                        <h3 class="mb-0">{{ $stats['voyages_aller'] }}</h3>
                                        <small class="text-muted"><i class="fas fa-arrow-right"></i> Aller</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-success bg-opacity-25 rounded">
                                        <h3 class="mb-0">{{ $stats['voyages_retour'] }}</h3>
                                        <small class="text-muted"><i class="fas fa-arrow-left"></i> Retour</small>
                                    </div>
                                </div>
                            </div>
                            @php 
                                $totalAR = $stats['voyages_aller'] + $stats['voyages_retour'];
                                $pctAller = $totalAR > 0 ? round($stats['voyages_aller'] / $totalAR * 100) : 0;
                                $pctRetour = 100 - $pctAller;
                            @endphp
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-primary" style="width: {{ $pctAller }}%">{{ $pctAller }}%</div>
                                <div class="progress-bar bg-success" style="width: {{ $pctRetour }}%">{{ $pctRetour }}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conducteurs ayant utilisé ce bus -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-black">
                            <i class="fas fa-users"></i> Top Conducteurs
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($conducteursUtilises as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('statistiques.conducteur.detail', $item['conducteur']->id ?? 0) }}">
                                        {{ $item['conducteur']->prenom ?? '' }} {{ $item['conducteur']->nom ?? 'Inconnu' }}
                                    </a>
                                    <span class="badge bg-primary rounded-pill">{{ $item['count'] }}</span>
                                </li>
                                @empty
                                <li class="list-group-item text-muted text-center">Aucune donnée</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Lignes parcourues -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-black">
                            <i class="fas fa-route"></i> Lignes Parcourues
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($lignesParcourues as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        {{ $item['ligne']->nom ?? 'Inconnu' }}
                                        <br><small class="text-muted">{{ number_format($item['distance'] ?? 0, 0, ',', ' ') }} km</small>
                                    </span>
                                    <span class="badge bg-success rounded-pill">{{ $item['count'] }}</span>
                                </li>
                                @empty
                                <li class="list-group-item text-muted text-center">Aucune donnée</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique des voyages -->
            <div class="card">
                <div class="card-header bg-secondary text-black d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-history"></i> Historique des Voyages ({{ $voyages->count() }})</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-dark align-middle">
                                <tr>
                                    <th class="row-num">N°</th>
                                    <th>Date départ</th>
                                    <th>Conducteur</th>
                                    <th>Bus</th>
                                    <th>Ligne</th>
                                    <th>Période</th>
                                    <th>Sens</th>
                                    <th>Statut</th>
                                    <th class="no-sort no-export d-none d-md-table-cell">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($voyages as $voyage)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($voyage->date_depart)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $voyage->ligne->nom ?? '-' }}</td>
                                    <td>
                                        @if($voyage->conducteur)
                                            <a href="{{ route('statistiques.conducteur.detail', $voyage->conducteur->id) }}">
                                                {{ $voyage->conducteur->prenom }} {{ $voyage->conducteur->nom }}
                                            </a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($voyage->conducteur2)
                                            <a href="{{ route('statistiques.conducteur.detail', $voyage->conducteur2->id) }}">
                                                {{ $voyage->conducteur2->prenom }} {{ $voyage->conducteur2->nom }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($voyage->periode === 'Jour')
                                            <span class="badge bg-warning text-dark"><i class="fas fa-sun"></i></span>
                                        @else
                                            <span class="badge bg-dark"><i class="fas fa-moon"></i></span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($voyage->sens === 'Aller')
                                            <span class="badge bg-primary">Aller</span>
                                        @else
                                            <span class="badge bg-success">Retour</span>
                                        @endif
                                    </td>
                                    <td>{{ $voyage->ligne->distance_km ?? 0 }} km</td>
                                    <td>
                                        @if($voyage->statut === 'Terminé')
                                            <span class="badge bg-success">Terminé</span>
                                        @elseif($voyage->statut === 'Planifié')
                                            <span class="badge bg-warning text-dark">Planifié</span>
                                        @else
                                            <span class="badge bg-info">{{ $voyage->statut }}</span>
                                        @endif
                                    </td>
                                    <td class="no-sort no-export d-none d-md-table-cell">
                                        <a href="{{ route('statistiques.voyage.detail', $voyage->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Aucun voyage trouvé</td>
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
