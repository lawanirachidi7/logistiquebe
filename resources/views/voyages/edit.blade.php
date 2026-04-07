@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Modifier le Voyage #{{ $voyage->id }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('voyages.update', $voyage->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                                                    @if(old('periode', $voyage->periode) === 'Nuit')
                                                    <div class="mb-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="force_nuit" name="force_nuit" value="1" {{ old('force_nuit', $voyage->force_nuit) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="force_nuit">
                                                                Forcer la nuit (exception opérateur)
                                                            </label>
                                                        </div>
                                                        <small class="text-muted">À cocher uniquement si l'opérateur autorise un conducteur de jour à travailler la nuit exceptionnellement.</small>
                                                    </div>
                                                    @endif
                            <label for="date_depart" class="form-label">Date de départ</label>
                            <input type="datetime-local" class="form-control @error('date_depart') is-invalid @enderror" id="date_depart" name="date_depart" value="{{ old('date_depart', $voyage->date_depart) }}" required>
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
                                    <option value="{{ $ligne->id }}" {{ old('ligne_id', $voyage->ligne_id) == $ligne->id ? 'selected' : '' }}>
                                        {{ $ligne->nom }} ({{ $ligne->ville_depart }} - {{ $ligne->ville_arrivee }})
                                    </option>
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
                                    <option value="{{ $b->id }}" {{ old('bus_id', $voyage->bus_id) == $b->id ? 'selected' : '' }}>
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
                                    <option value="{{ $c->id }}" {{ old('conducteur_id', $voyage->conducteur_id) == $c->id ? 'selected' : '' }}>
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
                                <option value="Jour" {{ old('periode', $voyage->periode) == 'Jour' ? 'selected' : '' }}>Jour</option>
                                <option value="Nuit" {{ old('periode', $voyage->periode) == 'Nuit' ? 'selected' : '' }}>Nuit</option>
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
                                <option value="Aller" {{ old('sens', $voyage->sens) == 'Aller' ? 'selected' : '' }}>Aller</option>
                                <option value="Retour" {{ old('sens', $voyage->sens) == 'Retour' ? 'selected' : '' }}>Retour</option>
                            </select>
                            @error('sens')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3" id="div-conducteur2">
                            <label for="conducteur_2_id" class="form-label">Conducteur Relais (Nuit)</label>
                            <select class="form-select select2 @error('conducteur_2_id') is-invalid @enderror" id="conducteur_2_id" name="conducteur_2_id">
                                <option value="">-- Aucun (optionnel) --</option>
                                @foreach($conducteurs as $c)
                                    <option value="{{ $c->id }}" {{ old('conducteur_2_id', $voyage->conducteur_2_id) == $c->id ? 'selected' : '' }}>
                                        {{ $c->nom }} {{ $c->prenom }}
                                        @if($c->specialiste_nuit) - Spéc. Nuit @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('conducteur_2_id')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="statut" class="form-label">Statut</label>
                            <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut">
                                <option value="Planifié" {{ old('statut', $voyage->statut) == 'Planifié' ? 'selected' : '' }}>Planifié</option>
                                <option value="En cours" {{ old('statut', $voyage->statut) == 'En cours' ? 'selected' : '' }}>En cours</option>
                                <option value="Terminé" {{ old('statut', $voyage->statut) == 'Terminé' ? 'selected' : '' }}>Terminé</option>
                                <option value="Annulé" {{ old('statut', $voyage->statut) == 'Annulé' ? 'selected' : '' }}>Annulé</option>
                            </select>
                            @error('statut')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes">{{ old('notes', $voyage->notes) }}</textarea>
                            @error('notes')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="{{ route('voyages.historique') }}" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Afficher/masquer conducteur relais selon la période
    function toggleConducteur2() {
        var periode = $('#periode').val();
        if (periode === 'Nuit') {
            $('#div-conducteur2').show();
        } else {
            $('#div-conducteur2').hide();
            $('#conducteur_2_id').val('');
        }
    }
    
    // Au chargement et au changement
    toggleConducteur2();
    $('#periode').change(toggleConducteur2);
});
</script>
@endpush