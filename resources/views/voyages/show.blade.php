@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Détails du Voyage #{{ $voyage->id }}</div>

                <div class="card-body">
                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Date de départ</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $voyage->date_depart }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Ligne</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                <a href="{{ route('lignes.show', $voyage->ligne->id ?? '#') }}" class="text-decoration-none">
                                    {{ $voyage->ligne->nom }}
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Bus</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                <a href="{{ route('bus.show', $voyage->bus->id ?? '#') }}" class="text-decoration-none">
                                    {{ $voyage->bus->immatriculation }}
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Conducteur</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                <a href="{{ route('conducteurs.show', $voyage->conducteur->id ?? '#') }}" class="text-decoration-none">
                                    {{ $voyage->conducteur->nom }} {{ $voyage->conducteur->prenom }}
                                </a>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Période</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $voyage->periode }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Sens</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $voyage->sens }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Statut</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $voyage->statut ?? 'Planifié' }}</p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('voyages.historique') }}" class="btn btn-secondary">Retour</a>
                        
                        <div>
                            <a href="{{ route('voyages.edit', $voyage->id) }}" class="btn btn-warning">Modifier</a>
                            
                            <form action="{{ route('voyages.destroy', $voyage->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce voyage ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
