@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-eye"></i> Aperçu de la Programmation sur Période
                    </h4>
                    <span class="badge bg-light text-dark fs-6">
                        Du {{ \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateFin)->format('d/m/Y') }}
                    </span>
                </div>
                <div class="card-body">
                    
                    <!-- Résumé global -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">{{ $totalPropositions }}</h2>
                                    <small>Voyages proposés</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">{{ count($aperçuParJour) }}</h2>
                                    <small>Jours à programmer</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card {{ $totalAlertes > 0 ? 'bg-warning' : 'bg-secondary' }} text-white">
                                <div class="card-body text-center">
                                    <h2 class="mb-0">{{ $totalAlertes }}</h2>
                                    <small>Alertes</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Aperçu de la programmation</h5>
                        <p class="mb-0">
                            Voici un aperçu des {{ $totalPropositions }} voyages qui seront créés sur {{ count($aperçuParJour) }} jour(s).
                            Le système appliquera la logique d'alternance (aller → retour → aller...) en respectant les repos et indisponibilités.
                        </p>
                    </div>

                    <!-- Détails par jour -->
                    <div class="accordion" id="accordionApercu">
                        @foreach($aperçuParJour as $date => $apercu)
                        @php
                            $dateFormatee = \Carbon\Carbon::parse($date)->format('d/m/Y');
                            $jourSemaine = \Carbon\Carbon::parse($date)->locale('fr')->isoFormat('dddd');
                            $nbPropositions = count($apercu['propositions']);
                            $nbPossibles = count(array_filter($apercu['propositions'], fn($p) => $p['possible']));
                            $nbAlertes = count($apercu['alertes']);
                        @endphp
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPreview{{ $loop->index }}">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePreview{{ $loop->index }}">
                                    <span class="me-3">
                                        <strong>{{ ucfirst($jourSemaine) }} {{ $dateFormatee }}</strong>
                                    </span>
                                    <span class="badge bg-info me-2">{{ $nbPropositions }} proposition(s)</span>
                                    <span class="badge bg-success me-2">{{ $nbPossibles }} possible(s)</span>
                                    @if($apercu['voyagesExistants'] > 0)
                                    <span class="badge bg-secondary me-2">{{ $apercu['voyagesExistants'] }} existant(s)</span>
                                    @endif
                                    @if($nbAlertes > 0)
                                    <span class="badge bg-warning">{{ $nbAlertes }} alerte(s)</span>
                                    @endif
                                </button>
                            </h2>
                            <div id="collapsePreview{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#accordionApercu">
                                <div class="accordion-body">
                                    @if($nbPropositions > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover">
                                            <thead class="table-info">
                                                <tr>
                                                    <th>Ligne</th>
                                                    <th>Horaire</th>
                                                    <th>Bus</th>
                                                    <th>Conducteur</th>
                                                    <th>Conducteur 2</th>
                                                    <th>Période</th>
                                                    <th>Source</th>
                                                    <th>Statut</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($apercu['propositions'] as $proposition)
                                                <tr class="{{ $proposition['possible'] ? '' : 'table-warning' }}">
                                                    <td>
                                                        <strong>{{ $proposition['ligne']->nom }}</strong>
                                                        @if($proposition['ligne']->type === 'Aller')
                                                            <span class="badge bg-primary">Aller</span>
                                                        @else
                                                            <span class="badge bg-success">Retour</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $proposition['ligne']->horaire_formate }}</td>
                                                    <td>
                                                        @if($proposition['bus'])
                                                            <span class="text-success">{{ $proposition['bus']->immatriculation }}</span>
                                                        @else
                                                            <span class="text-danger"><i class="fas fa-times"></i> Aucun</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($proposition['conducteur'])
                                                            <span class="text-success">{{ $proposition['conducteur']->prenom }} {{ $proposition['conducteur']->nom }}</span>
                                                        @else
                                                            <span class="text-danger"><i class="fas fa-times"></i> Aucun</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($proposition['conducteur2'])
                                                            <span class="text-success">{{ $proposition['conducteur2']->prenom }} {{ $proposition['conducteur2']->nom }}</span>
                                                        @elseif($proposition['periode'] === 'Nuit')
                                                            <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> Manquant</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($proposition['periode'] === 'Jour')
                                                            <span class="badge bg-warning text-dark">Jour</span>
                                                        @else
                                                            <span class="badge bg-dark">Nuit</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($proposition['source'] === 'retour_suite_aller')
                                                            <span class="badge bg-info">Suite aller hier</span>
                                                        @elseif($proposition['source'] === 'aller_suite_retour')
                                                            <span class="badge bg-info">Suite retour hier</span>
                                                        @else
                                                            <span class="badge bg-secondary">Nouveau</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($proposition['possible'])
                                                            <span class="badge bg-success"><i class="fas fa-check"></i> OK</span>
                                                        @else
                                                            <span class="badge bg-danger"><i class="fas fa-times"></i> Incomplet</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @else
                                        <div class="alert alert-secondary mb-0">
                                            <i class="fas fa-info-circle"></i> Aucune proposition pour ce jour (voyages existants ou pas de ressources).
                                        </div>
                                    @endif

                                    @if($nbAlertes > 0)
                                    <div class="alert alert-warning mb-0 mt-2">
                                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Alertes</h6>
                                        <ul class="mb-0 small">
                                            @foreach($apercu['alertes'] as $alerte)
                                            <li>{{ $alerte }}</li>
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

                    <!-- Formulaire de confirmation -->
                    <form action="{{ route('voyages.generer.periode') }}" method="POST">
                        @csrf
                        <input type="hidden" name="date_debut" value="{{ $dateDebut }}">
                        <input type="hidden" name="date_fin" value="{{ $dateFin }}">
                        <input type="hidden" name="periode_range" value="{{ implode(',', $periodes) === 'Jour,Nuit' ? 'Les deux' : $periodes[0] }}">
                        @foreach($lignesSelectionnees as $ligne)
                        <input type="hidden" name="lignes[]" value="{{ $ligne->id }}">
                        @endforeach

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-calendar-check"></i> Confirmer et générer les {{ $totalPropositions }} voyages
                            </button>
                            <a href="{{ route('voyages.generer.form') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left"></i> Retour
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
