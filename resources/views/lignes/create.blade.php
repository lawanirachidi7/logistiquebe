@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Ajouter une nouvelle ligne</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('lignes.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ville_depart" class="form-label">Ville de départ</label>
                                    <select class="form-select @error('ville_depart') is-invalid @enderror" id="ville_depart" name="ville_depart" required>
                                        <option value="">-- Sélectionner --</option>
                                        @foreach($villes as $ville)
                                            <option value="{{ $ville->nom }}" {{ old('ville_depart') == $ville->nom ? 'selected' : '' }}>{{ $ville->nom }}</option>
                                        @endforeach
                                    </select>
                                    @error('ville_depart')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ville_arrivee" class="form-label">Ville d'arrivée</label>
                                    <select class="form-select @error('ville_arrivee') is-invalid @enderror" id="ville_arrivee" name="ville_arrivee" required>
                                        <option value="">-- Sélectionner --</option>
                                        @foreach($villes as $ville)
                                            <option value="{{ $ville->nom }}" {{ old('ville_arrivee') == $ville->nom ? 'selected' : '' }}>{{ $ville->nom }}</option>
                                        @endforeach
                                    </select>
                                    @error('ville_arrivee')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom de la ligne</label>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom') }}" readonly>
                            <small class="text-muted">Le nom est généré automatiquement</small>
                            @error('nom')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Type de ligne</label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                        <option value="Aller" {{ old('type') == 'Aller' ? 'selected' : '' }}>Aller (départ de Parakou)</option>
                                        <option value="Retour" {{ old('type') == 'Retour' ? 'selected' : '' }}>Retour (arrivée à Parakou)</option>
                                    </select>
                                    <small class="text-muted">Le type est déterminé automatiquement selon les villes</small>
                                    @error('type')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="horaire" class="form-label">Horaire de départ</label>
                                    <input type="time" class="form-control @error('horaire') is-invalid @enderror" id="horaire" name="horaire" value="{{ old('horaire', '06:00') }}" required>
                                    @error('horaire')
                                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="ligne_retour_container">
                            <label for="ligne_retour_id" class="form-label">Ligne retour associée</label>
                            <select class="form-select @error('ligne_retour_id') is-invalid @enderror" id="ligne_retour_id" name="ligne_retour_id">
                                <option value="">-- Aucune (détection automatique) --</option>
                                @foreach($lignesRetour as $ligneRetour)
                                    <option value="{{ $ligneRetour->id }}" {{ old('ligne_retour_id') == $ligneRetour->id ? 'selected' : '' }}>
                                        {{ $ligneRetour->nom }} ({{ $ligneRetour->horaire_formate }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Associer cette ligne aller à sa ligne retour spécifique</small>
                            @error('ligne_retour_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="est_ligne_nord" name="est_ligne_nord" value="1" {{ old('est_ligne_nord') ? 'checked' : '' }}>
                            <label class="form-check-label" for="est_ligne_nord">Est-ce une ligne Nord ?</label>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="aller_retour_meme_jour" name="aller_retour_meme_jour" value="1" {{ old('aller_retour_meme_jour') ? 'checked' : '' }}>
                            <label class="form-check-label" for="aller_retour_meme_jour">
                                <i class="fas fa-exchange-alt"></i> Aller-retour dans la même journée
                            </label>
                            <small class="form-text text-muted d-block">Le même bus fait l'aller et le retour le même jour (ex: Natitingou)</small>
                        </div>

                        <div class="mb-3">
                            <label for="distance_km" class="form-label">Distance (km)</label>
                            <input type="number" class="form-control @error('distance_km') is-invalid @enderror" id="distance_km" name="distance_km" value="{{ old('distance_km') }}">
                            @error('distance_km')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="duree_estimee" class="form-label">Durée estimée (minutes)</label>
                            <input type="number" class="form-control @error('duree_estimee') is-invalid @enderror" id="duree_estimee" name="duree_estimee" value="{{ old('duree_estimee') }}">
                            @error('duree_estimee')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="{{ route('lignes.index') }}" class="btn btn-secondary">Annuler</a>
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
        function updateNomLigne() {
            var villeDepart = $('#ville_depart').val();
            var villeArrivee = $('#ville_arrivee').val();
            if (villeDepart && villeArrivee) {
                $('#nom').val(villeDepart + '-' + villeArrivee);
                // Auto-déterminer le type
                if (villeDepart === 'Parakou') {
                    $('#type').val('Aller');
                    toggleLigneRetour('Aller');
                } else if (villeArrivee === 'Parakou') {
                    $('#type').val('Retour');
                    toggleLigneRetour('Retour');
                }
            } else {
                $('#nom').val('');
            }
        }

        function toggleLigneRetour(type) {
            if (type === 'Aller') {
                $('#ligne_retour_container').show();
            } else {
                $('#ligne_retour_container').hide();
                $('#ligne_retour_id').val('');
            }
        }
        
        $('#ville_depart, #ville_arrivee').on('change', updateNomLigne);
        
        $('#type').on('change', function() {
            toggleLigneRetour($(this).val());
        });

        // Initialisation au chargement
        toggleLigneRetour($('#type').val());
    });
</script>
@endpush
