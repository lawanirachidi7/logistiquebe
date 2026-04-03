@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Détails du Conducteur</div>

                <div class="card-body">
                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Nom</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $conducteur->nom }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Prénom</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $conducteur->prenom }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Spécialiste Nuit</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                @if($conducteur->specialiste_nuit)
                                    <span class="badge bg-secondary">Oui</span>
                                @else
                                    <span class="badge bg-light text-dark">Non</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Remplaçant Nuit</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                @if($conducteur->remplacant_nuit)
                                    <span class="badge bg-secondary">Oui</span>
                                @else
                                    <span class="badge bg-light text-dark">Non</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Famille hors Parakou</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                @if($conducteur->famille_hors_parakou)
                                    <span class="badge bg-secondary">Oui</span>
                                @else
                                    <span class="badge bg-light text-dark">Non</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Actif</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                @if($conducteur->actif)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-danger">Inactif</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Types de Bus Autorisés</label>
                        <div class="col-sm-8">
                            @if($conducteur->typesBus->count() > 0)
                                @foreach($conducteur->typesBus as $typeBus)
                                    <span class="badge bg-primary">{{ $typeBus->libelle }}</span>
                                @endforeach
                            @else
                                <p class="form-control-plaintext text-muted">Aucun type de bus spécifique assigné</p>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('conducteurs.index') }}" class="btn btn-secondary">Retour</a>
                        <a href="{{ route('conducteurs.edit', $conducteur->id) }}" class="btn btn-primary">Modifier</a>
                    </div>
                </div>
            </div>
            
            @if($conducteur->voyages && $conducteur->voyages->count() > 0)
            <div class="card mt-4">
                <div class="card-header">Historique des voyages</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @foreach($conducteur->voyages as $voyage)
                            <li class="list-group-item">
                                {{ $voyage->date_depart }} - {{ $voyage->ligne->nom }} ({{ $voyage->periode }}, {{ $voyage->sens }})
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
