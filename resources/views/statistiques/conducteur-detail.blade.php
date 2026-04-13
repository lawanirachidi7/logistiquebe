@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>
                        <i class="fas fa-user"></i> {{ $conducteur->prenom }} {{ $conducteur->nom }}
                        @if($conducteur->specialiste_nuit)
                            <span class="badge bg-dark"><i class="fas fa-moon"></i> Spécialiste Nuit</span>
                        @endif
                        @if(!$conducteur->actif)
                            <span class="badge bg-secondary">Inactif</span>
                        @endif
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="fas fa-map-marker-alt"></i> {{ $conducteur->ville_actuelle }}
                    </p>
                </div>
                <div>
                    <div class="btn-toolbar gap-2" role="toolbar" aria-label="Barre d'actions">
                        <a href="{{ route('statistiques.conducteurs') }}" class="btn btn-outline-secondary" title="Retour aux conducteurs">
                            <i class="fas fa-arrow-left me-1"></i> Retour
                        </a>
                        <a href="{{ route('repos.conducteur.detail', ['id' => $conducteur->id]) }}" class="btn btn-outline-info" title="Statistiques fatigue">
                            <i class="fas fa-bed me-1"></i> Fatigue
                        </a>
                        <a href="{{ route('voyages.historique') }}?conducteur_id={{ $conducteur->id }}" class="btn btn-outline-primary" title="Historique des voyages">
                            <i class="fas fa-route me-1"></i> Voyages
                        </a>
                        <a href="{{ route('repos.index') }}?conducteur_id={{ $conducteur->id }}" class="btn btn-outline-warning" title="Historique des repos">
                            <i class="fas fa-bed me-1"></i> Repos
                        </a>
                        <a href="{{ route('repos.export', ['conducteur' => $conducteur->id]) }}" class="btn btn-outline-success" title="Exporter les données">
                            <i class="fas fa-file-excel me-1"></i> Exporter
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filtre par période -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('statistiques.conducteur.detail', $conducteur->id) }}" method="GET" class="row g-3 align-items-end">
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
                    <div class="card bg-info text-white h-100">
                        <div class="card-body text-center">
                            <h2 class="mb-0">{{ $stats['voyages_principal'] }}</h2>
                            <small>En Principal</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-secondary text-white h-100">
                        <div class="card-body text-center">
                            <h2 class="mb-0">{{ $stats['voyages_second'] }}</h2>
                            <small>En Second</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark h-100">
                        <div class="card-body text-center">
                            <h2 class="mb-0">{{ number_format($stats['distance_totale'] ?? 0, 0, ',', ' ') }} km</h2>
                            <small>Distance Parcourue</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Répartition Jour/Nuit et Aller/Retour -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-info text-black">
                            <i class="fas fa-chart-pie"></i> Répartition
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Jour / Nuit</strong>
                                <div class="progress" style="height: 25px;">
                                    @php 
                                        $totalJN = $stats['voyages_jour'] + $stats['voyages_nuit'];
                                        $pctJour = $totalJN > 0 ? round($stats['voyages_jour'] / $totalJN * 100) : 0;
                                        $pctNuit = 100 - $pctJour;
                                    @endphp
                                    <div class="progress-bar bg-warning" style="width: {{ $pctJour }}%">
                                        <i class="fas fa-sun"></i> {{ $stats['voyages_jour'] }}
                                    </div>
                                    <div class="progress-bar bg-dark" style="width: {{ $pctNuit }}%">
                                        <i class="fas fa-moon"></i> {{ $stats['voyages_nuit'] }}
                                    </div>
                                </div>
                            </div>
                            <div>
                                <strong>Aller / Retour</strong>
                                <div class="progress" style="height: 25px;">
                                    @php 
                                        $totalAR = $stats['voyages_aller'] + $stats['voyages_retour'];
                                        $pctAller = $totalAR > 0 ? round($stats['voyages_aller'] / $totalAR * 100) : 0;
                                        $pctRetour = 100 - $pctAller;
                                    @endphp
                                    <div class="progress-bar bg-primary" style="width: {{ $pctAller }}%">
                                        <i class="fas fa-arrow-right"></i> {{ $stats['voyages_aller'] }}
                                    </div>
                                    <div class="progress-bar bg-success" style="width: {{ $pctRetour }}%">
                                        <i class="fas fa-arrow-left"></i> {{ $stats['voyages_retour'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lignes les plus fréquentes -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-black d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-route"></i> Lignes Fréquentes
                                <span class="badge bg-light text-primary ms-2" title="Nombre de lignes différentes">
                                    {{ count($lignesFrequentes) }}
                                </span>
                            </span>
                            <button type="button" class="btn btn-outline-dark btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#modalLignesConducteur" title="Voir toutes les lignes fréquentées">
                                <i class="fas fa-list"></i> Voir tout
                            </button>
                        <!-- Modal Lignes Fréquentées -->
                        <div class="modal fade" id="modalLignesConducteur" tabindex="-1" aria-labelledby="modalLignesConducteurLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="modalLignesConducteurLabel">
                                            <i class="fas fa-route"></i> Lignes fréquentées par {{ $conducteur->prenom }} {{ $conducteur->nom }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body p-0">
                                        <ul class="list-group list-group-flush">
                                            @forelse($lignesFrequentesAll as  $item)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    {{ $item['ligne']->nom ?? 'Inconnu' }}
                                                    <span class="badge bg-primary rounded-pill">{{ $item['count'] }}</span>
                                                </li>
                                            @empty
                                                <li class="list-group-item text-muted text-center">Aucune donnée</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($lignesFrequentes as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $item['ligne']->nom ?? 'Inconnu' }}
                                    <span class="badge bg-primary rounded-pill">{{ $item['count'] }}</span>
                                </li>
                                @empty
                                <li class="list-group-item text-muted text-center">Aucune donnée</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Bus utilisés -->
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-header bg-success text-black d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-bus"></i> Bus Utilisés
                                <span class="badge bg-light text-success ms-2" title="Nombre de bus différents">
                                    {{ count($busUtilises) }}
                                </span>
                            </span>
                            <button type="button" class="btn btn-outline-dark btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#modalBusUtilisesConducteur" title="Voir tous les bus utilisés">
                                <i class="fas fa-list"></i> Voir tout
                            </button>
                            <!-- Modal Bus Utilisés -->
                            <div class="modal fade" id="modalBusUtilisesConducteur" tabindex="-1" aria-labelledby="modalBusUtilisesConducteurLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title" id="modalBusUtilisesConducteurLabel">
                                                <i class="fas fa-bus"></i> Bus utilisés par {{ $conducteur->prenom }} {{ $conducteur->nom }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <ul class="list-group list-group-flush">
                                                @forelse($busUtilisesAll as $item)
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        {{ $item['bus']->immatriculation ?? 'Inconnu' }}
                                                        <span class="badge bg-success rounded-pill">{{ $item['count'] }}</span>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item text-muted text-center">Aucune donnée</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($busUtilises as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $item['bus']->immatriculation ?? 'Inconnu' }}
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
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th>Date</th>
                                    <th>Ligne</th>
                                    <th>Bus</th>
                                    <th>Rôle</th>
                                    <th>Période</th>
                                    <th>Sens</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($voyages as $voyage)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($voyage->date_depart)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $voyage->ligne->nom ?? '-' }}</td>
                                    <td>{{ $voyage->bus->immatriculation ?? '-' }}</td>
                                    <td>
                                        @if($voyage->conducteur_id == $conducteur->id)
                                            <span class="badge bg-primary">Principal</span>
                                        @else
                                            <span class="badge bg-secondary">Second</span>
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
                                    <td>
                                        @if($voyage->statut === 'Terminé')
                                            <span class="badge bg-success">Terminé</span>
                                        @elseif($voyage->statut === 'Planifié')
                                            <span class="badge bg-warning text-dark">Planifié</span>
                                        @else
                                            <span class="badge bg-info">{{ $voyage->statut }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Aucun voyage trouvé</td>
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
