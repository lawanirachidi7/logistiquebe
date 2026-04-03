@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Programmation Générée - {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h4>
                </div>
                <div class="card-body">
                    
                    @if(count($voyagesCrees) > 0)
                    <div class="alert alert-success">
                        <h5>{{ count($voyagesCrees) }} voyage(s) créé(s) avec succès !</h5>
                    </div>

                    @php
                        $voyagesAller = array_filter($voyagesCrees, fn($v) => str_contains($v['type'], 'Aller'));
                        $voyagesRetour = array_filter($voyagesCrees, fn($v) => str_contains($v['type'], 'Retour'));
                    @endphp

                    <!-- Tableau des voyages ALLER -->
                    @if(count($voyagesAller) > 0)
                    <div class="mb-4">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-arrow-right"></i> Voyages ALLER 
                            <span class="badge bg-primary">{{ count($voyagesAller) }}</span>
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped datatable">
                                <thead class="table-primary">
                                    <tr>
                                        <th class="row-num">N°</th>
                                        <th>Ligne</th>
                                        <th>Horaire</th>
                                        <th>Bus</th>
                                        <th>Conducteur</th>
                                        <th>Origine</th>
                                        <th>Période</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($voyagesAller as $voyage)
                                    <tr>
                                        <td></td>
                                        <td>{{ $voyage['ligne'] }}</td>
                                        <td><strong>{{ $voyage['horaire'] ?? '-' }}</strong></td>
                                        <td><strong>{{ $voyage['bus'] }}</strong></td>
                                        <td>{{ $voyage['conducteur'] }}</td>
                                        <td>
                                            @if(str_contains($voyage['type'], 'suite'))
                                                <span class="badge bg-success">Suite retour hier</span>
                                            @else
                                                <span class="badge bg-primary">Nouveau</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($voyage['periode'] === 'Jour')
                                                <span class="badge bg-warning text-dark">Jour</span>
                                            @else
                                                <span class="badge bg-dark">Nuit</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- Tableau des voyages RETOUR -->
                    @if(count($voyagesRetour) > 0)
                    <div class="mb-4">
                        <h5 class="text-info mb-3">
                            <i class="fas fa-arrow-left"></i> Voyages RETOUR 
                            <span class="badge bg-info">{{ count($voyagesRetour) }}</span>
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped datatable">
                                <thead class="table-info">
                                    <tr>
                                        <th class="row-num">N°</th>
                                        <th>Ligne</th>
                                        <th>Horaire</th>
                                        <th>Bus</th>
                                        <th>Conducteur</th>
                                        <th>Origine</th>
                                        <th>Période</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($voyagesRetour as $voyage)
                                    <tr>
                                        <td></td>
                                        <td>{{ $voyage['ligne'] }}</td>
                                        <td><strong>{{ $voyage['horaire'] ?? '-' }}</strong></td>
                                        <td><strong>{{ $voyage['bus'] }}</strong></td>
                                        <td>{{ $voyage['conducteur'] }}</td>
                                        <td>
                                            <span class="badge bg-info">Suite aller hier</span>
                                        </td>
                                        <td>
                                            @if($voyage['periode'] === 'Jour')
                                                <span class="badge bg-warning text-dark">Jour</span>
                                            @else
                                                <span class="badge bg-dark">Nuit</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    @endif

                    @if(count($erreurs) > 0)
                    <div class="alert alert-warning mt-3">
                        <h6>Voyages non créés (ressources insuffisantes) :</h6>
                        <ul class="mb-0">
                            @foreach($erreurs as $erreur)
                            <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <hr>

                    <div class="d-flex gap-2">
                        <a href="{{ route('voyages.generer.form') }}" class="btn btn-primary">
                            Générer une autre programmation
                        </a>
                        <a href="{{ route('voyages.historique') }}" class="btn btn-info">
                            Voir l'historique
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                            Retour au dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Récapitulatif après génération -->
            <div class="card mt-4">
                <div class="card-header">
                    Nouvelle répartition des conducteurs
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <em>Note : La ville des conducteurs sera mise à jour automatiquement lorsque les voyages seront validés.</em>
                    </p>
                    <div class="row">
                        @php
                            $conducteursParVille = \App\Models\Conducteur::where('actif', true)
                                ->selectRaw('ville_actuelle, COUNT(*) as total')
                                ->groupBy('ville_actuelle')
                                ->get();
                        @endphp
                        @foreach($conducteursParVille as $ville)
                        <div class="col-md-3 mb-2">
                            <div class="border rounded p-2 text-center">
                                <strong>{{ $ville->ville_actuelle }}</strong>
                                <br>
                                <span class="badge bg-primary">{{ $ville->total }} conducteur(s)</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
