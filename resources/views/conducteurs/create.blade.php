@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-user-plus"></i>
                Ajouter un conducteur
            </h1>
            <p class="page-subtitle">Remplissez les informations du nouveau conducteur</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('conducteurs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary">
                    <i class="fas fa-id-card me-2"></i>Informations du conducteur
                </div>
                <div class="card-body">
                    <form action="{{ route('conducteurs.store') }}" method="POST">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom') }}" required>
                                @error('nom')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                                @error('prenom')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                        </div>

                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title mb-3"><i class="fas fa-cog me-2"></i>Options</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="specialiste_nuit" name="specialiste_nuit" {{ old('specialiste_nuit') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="specialiste_nuit">
                                                <i class="fas fa-moon text-info me-1"></i>Spécialiste nuit
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="remplacant_nuit" name="remplacant_nuit" {{ old('remplacant_nuit') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="remplacant_nuit">
                                                <i class="fas fa-user-clock text-warning me-1"></i>Remplaçant nuit
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="famille_hors_parakou" name="famille_hors_parakou" {{ old('famille_hors_parakou') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="famille_hors_parakou">
                                                <i class="fas fa-home text-danger me-1"></i>Famille hors Parakou
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="actif" name="actif" checked>
                                            <label class="form-check-label" for="actif">
                                                <i class="fas fa-check-circle text-success me-1"></i>Actif
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="types_bus" class="form-label">
                                <i class="fas fa-bus me-1"></i>Types de bus autorisés
                            </label>
                            <select class="form-select select2" id="types_bus" name="types_bus[]" multiple>
                                @foreach($typesBus as $type)
                                    <option value="{{ $type->id }}">{{ $type->libelle }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Vous pouvez rechercher et sélectionner plusieurs types.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Enregistrer
                            </button>
                            <a href="{{ route('conducteurs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
