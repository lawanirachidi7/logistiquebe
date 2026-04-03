@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Modifier le conducteur : {{ $conducteur->nom }} {{ $conducteur->prenom }}</div>

                <div class="card-body">
                    <form action="{{ route('conducteurs.update', $conducteur->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom', $conducteur->nom) }}" required>
                            @error('nom')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom', $conducteur->prenom) }}" required>
                            @error('prenom')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="specialiste_nuit" name="specialiste_nuit" {{ old('specialiste_nuit', $conducteur->specialiste_nuit) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="specialiste_nuit">Spécialiste nuit</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remplacant_nuit" name="remplacant_nuit" {{ old('remplacant_nuit', $conducteur->remplacant_nuit) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remplacant_nuit">Remplaçant nuit</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="famille_hors_parakou" name="famille_hors_parakou" {{ old('famille_hors_parakou', $conducteur->famille_hors_parakou) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="famille_hors_parakou">Famille hors Parakou</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="actif" name="actif" {{ old('actif', $conducteur->actif) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="actif">Actif</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="types_bus" class="form-label">Types de bus autorisés</label>
                            <select class="form-select select2" id="types_bus" name="types_bus[]" multiple>
                                @foreach($typesBus as $type)
                                    <option value="{{ $type->id }}" {{ $conducteur->typesBus->contains($type->id) ? 'selected' : '' }}>{{ $type->libelle }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">Vous pouvez rechercher et sélectionner plusieurs types.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        <a href="{{ route('conducteurs.index') }}" class="btn btn-secondary">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
