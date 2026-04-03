@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Détails du Bus</div>

                <div class="card-body">
                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Immatriculation</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $bus->immatriculation }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Type</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">{{ $bus->typeBus->libelle ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Ligne Nord</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                @if($bus->ligne_nord)
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-secondary">Non</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Disponibilité</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                @if($bus->disponible)
                                    <span class="badge bg-primary">Disponible</span>
                                @else
                                    <span class="badge bg-danger">Indisponible</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="mb-3 row">
                        <label class="col-sm-4 col-form-label fw-bold">Position actuelle</label>
                        <div class="col-sm-8">
                            <p class="form-control-plaintext">
                                <span class="badge bg-info">{{ $bus->ville_actuelle ?? 'Parakou' }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('bus.index') }}" class="btn btn-secondary">Retour</a>
                        <a href="{{ route('bus.edit', $bus->id) }}" class="btn btn-primary">Modifier</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
