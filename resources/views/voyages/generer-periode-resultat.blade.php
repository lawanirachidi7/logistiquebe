@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-calendar-check"></i> Programmation Générée sur Période
                    </h4>
                    <span class="badge bg-light text-dark fs-6">
                        Du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
                    </span>
                </div>
                <div class="card-body">
                    
                    <!-- Résumé global -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">{{ $totalVoyagesCrees }}</h2>
                                    <small>Voyages créés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">{{ count($resultatsParJour) }}</h2>
                                    <small>Jours programmés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card {{ $totalErreurs > 0 ? 'bg-warning' : 'bg-secondary' }} text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">{{ $totalErreurs }}</h2>
                                    <small>Alertes/Erreurs</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($totalVoyagesCrees > 0)
                    <div class="alert alert-success">
                        <h5><i class="fas fa-check-circle"></i> Programmation réussie !</h5>
                        <p class="mb-0">{{ $totalVoyagesCrees }} voyage(s) ont été créés sur {{ count($resultatsParJour) }} jour(s).</p>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Aucun voyage créé</h5>
                        <p class="mb-0">Tous les voyages de cette période existaient déjà ou aucune ressource n'était disponible.</p>
                    </div>
                    @endif

                    <!-- Détails par jour -->
                    <div class="accordion" id="accordionJours">
                        @foreach($resultatsParJour as $date => $resultat)
                        @php
                            $dateFormatee = \Carbon\Carbon::parse($date)->format('d/m/Y');
                            $jourSemaine = \Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd');
                            $nbVoyages = count($resultat['voyages']);
                            $nbErreurs = count($resultat['erreurs']);
                            $voyagesAller = array_filter($resultat['voyages'], fn($v) => str_contains($v['type'], 'Aller'));
                            $voyagesRetour = array_filter($resultat['voyages'], fn($v) => str_contains($v['type'], 'Retour'));
                        @endphp
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ $loop->index }}">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $loop->index }}">
                                    <span class="me-3">
                                        <strong>{{ ucfirst($jourSemaine) }} {{ $dateFormatee }}</strong>
                                    </span>
                                    <span class="badge bg-success me-2">{{ $nbVoyages }} voyage(s)</span>
                                    @if($nbErreurs > 0)
                                    <span class="badge bg-warning">{{ $nbErreurs }} alerte(s)</span>
                                    @endif
                                </button>
                            </h2>
                            <div id="collapse{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#accordionJours">
                                <div class="accordion-body">
                                    @if($nbVoyages > 0)
                                        <!-- Voyages ALLER -->
                                        @if(count($voyagesAller) > 0)
                                        <h6 class="text-primary mb-2">
                                            <i class="fas fa-arrow-right"></i> Aller ({{ count($voyagesAller) }})
                                        </h6>
                                        <div class="table-responsive mb-3">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-primary">
                                                    <tr>
                                                        <th>Ligne</th>
                                                        <th>Horaire</th>
                                                        <th>Bus</th>
                                                        <th>Conducteur</th>
                                                        <th>Période</th>
                                                        <th>Origine</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($voyagesAller as $voyage)
                                                    <tr>
                                                        <td>{{ $voyage['ligne'] }}</td>
                                                        <td><strong>{{ $voyage['horaire'] ?? '-' }}</strong></td>
                                                        <td>{{ $voyage['bus'] }}</td>
                                                        <td>{{ $voyage['conducteur'] }}</td>
                                                        <td>
                                                            @if($voyage['periode'] === 'Jour')
                                                                <span class="badge bg-warning text-dark">Jour</span>
                                                            @else
                                                                <span class="badge bg-dark">Nuit</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if(str_contains($voyage['type'], 'suite'))
                                                                <span class="badge bg-info">Suite</span>
                                                            @else
                                                                <span class="badge bg-secondary">Nouveau</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif

                                        <!-- Voyages RETOUR -->
                                        @if(count($voyagesRetour) > 0)
                                        <h6 class="text-success mb-2">
                                            <i class="fas fa-arrow-left"></i> Retour ({{ count($voyagesRetour) }})
                                        </h6>
                                        <div class="table-responsive mb-3">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-success">
                                                    <tr>
                                                        <th>Ligne</th>
                                                        <th>Horaire</th>
                                                        <th>Bus</th>
                                                        <th>Conducteur</th>
                                                        <th>Période</th>
                                                        <th>Origine</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($voyagesRetour as $voyage)
                                                    <tr>
                                                        <td>{{ $voyage['ligne'] }}</td>
                                                        <td><strong>{{ $voyage['horaire'] ?? '-' }}</strong></td>
                                                        <td>{{ $voyage['bus'] }}</td>
                                                        <td>{{ $voyage['conducteur'] }}</td>
                                                        <td>
                                                            @if($voyage['periode'] === 'Jour')
                                                                <span class="badge bg-warning text-dark">Jour</span>
                                                            @else
                                                                <span class="badge bg-dark">Nuit</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if(str_contains($voyage['type'], 'suite'))
                                                                <span class="badge bg-info">Suite</span>
                                                            @else
                                                                <span class="badge bg-secondary">Nouveau</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @endif
                                    @else
                                        <div class="alert alert-secondary mb-0">
                                            <i class="fas fa-info-circle"></i> Aucun voyage programmé pour ce jour.
                                        </div>
                                    @endif

                                    <!-- Erreurs du jour -->
                                    @if($nbErreurs > 0)
                                    <div class="alert alert-warning mb-0 mt-2">
                                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Alertes</h6>
                                        <ul class="mb-0 small">
                                            @foreach($resultat['erreurs'] as $erreur)
                                            <li>{{ $erreur }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <hr>

                    <div class="d-flex gap-2">
                        <a href="{{ route('voyages.historique') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-list"></i> Voir l'historique
                        </a>
                        <a href="{{ route('voyages.generer.form') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-redo"></i> Nouvelle génération
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
