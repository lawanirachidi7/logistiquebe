@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Détails de la Ligne</div>

                <div class="card-body">
                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Nom de la Ligne</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $ligne->nom }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Ville de départ</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $ligne->ville_depart ?? 'Non spécifié' }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Ville d'arrivée</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $ligne->ville_arrivee ?? 'Non spécifié' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('lignes.index') }}" class="btn btn-secondary">Retour</a>
                <a href="{{ route('lignes.edit', $ligne->id) }}" class="btn btn-primary">Modifier</a>
            </div>
        </div>
    </div>
</div>
@endsection
