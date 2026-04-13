@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Planifier un Voyage</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('voyages.planifier') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="date_depart" class="form-label">Date de départ</label>
                            <input type="datetime-local" class="form-control @error('date_depart') is-invalid @enderror" id="date_depart" name="date_depart" value="{{ old('date_depart') }}" required>
                            @error('date_depart')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ligne_id" class="form-label">Ligne</label>
                            <select class="form-select select2 @error('ligne_id') is-invalid @enderror" id="ligne_id" name="ligne_id" required>
                                <option value="">Sélectionner une ligne</option>
                                @foreach($lignes as $ligne)
                                    <option value="{{ $ligne->id }}" {{ old('ligne_id') == $ligne->id ? 'selected' : '' }}>{{ $ligne->nom }}</option>
                                @endforeach
                            </select>
                            @error('ligne_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bus_id" class="form-label">Bus</label>
                            <select class="form-select select2 @error('bus_id') is-invalid @enderror" id="bus_id" name="bus_id" required>
                                <option value="">Sélectionner un bus</option>
                                @foreach($bus as $b)
                                    <option value="{{ $b->id }}" {{ old('bus_id') == $b->id ? 'selected' : '' }}>
                                        {{ $b->immatriculation }} ({{ $b->typeBus->libelle ?? 'Type inconnu' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('bus_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="conducteur_id" class="form-label">Conducteur</label>
                            <select class="form-select select2 @error('conducteur_id') is-invalid @enderror" id="conducteur_id" name="conducteur_id" required>
                                <option value="">Sélectionner un conducteur</option>
                                @foreach($conducteurs as $c)
                                    <option value="{{ $c->id }}" {{ old('conducteur_id') == $c->id ? 'selected' : '' }}>
                                        {{ $c->nom }} {{ $c->prenom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('conducteur_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="periode" class="form-label">Période</label>
                            <select class="form-select @error('periode') is-invalid @enderror" id="periode" name="periode" required>
                                <option value="Jour" {{ old('periode') == 'Jour' ? 'selected' : '' }}>Jour</option>
                                <option value="Nuit" {{ old('periode') == 'Nuit' ? 'selected' : '' }}>Nuit</option>
                            </select>
                            @error('periode')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="sens" class="form-label">Sens</label>
                            <select class="form-select @error('sens') is-invalid @enderror" id="sens" name="sens" required>
                                <option value="Aller" {{ old('sens') == 'Aller' ? 'selected' : '' }}>Aller</option>
                                <option value="Retour" {{ old('sens') == 'Retour' ? 'selected' : '' }}>Retour</option>
                            </select>
                            @error('sens')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>


                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes">{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="forcer_consecutif" name="forcer_consecutif" {{ old('forcer_consecutif') ? 'checked' : '' }}>
                            <label class="form-check-label" for="forcer_consecutif">
                                Forcer la programmation même si deux aller-retour consécutifs sur la même ligne
                            </label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Planifier</button>
                            <a href="{{ route('voyages.historique') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
