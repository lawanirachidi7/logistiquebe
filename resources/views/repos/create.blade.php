@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Créer un repos</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('repos.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="conducteur_id" class="form-label">Conducteur <span class="text-danger">*</span></label>
                            <select name="conducteur_id" id="conducteur_id" class="form-select @error('conducteur_id') is-invalid @enderror" required>
                                <option value="">Sélectionner un conducteur</option>
                                @foreach($conducteurs as $conducteur)
                                    <option value="{{ $conducteur->id }}" 
                                        {{ old('conducteur_id', request('conducteur_id')) == $conducteur->id ? 'selected' : '' }}>
                                        {{ $conducteur->prenom }} {{ $conducteur->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('conducteur_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_debut" class="form-label">Date de début <span class="text-danger">*</span></label>
                                <input type="date" 
                                       name="date_debut" 
                                       id="date_debut" 
                                       class="form-control @error('date_debut') is-invalid @enderror"
                                       value="{{ old('date_debut', now()->addDay()->format('Y-m-d')) }}"
                                       required>
                                @error('date_debut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_fin" class="form-label">Date de fin <span class="text-danger">*</span></label>
                                <input type="date" 
                                       name="date_fin" 
                                       id="date_fin" 
                                       class="form-control @error('date_fin') is-invalid @enderror"
                                       value="{{ old('date_fin', now()->addDay()->format('Y-m-d')) }}"
                                       required>
                                @error('date_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type_repos" class="form-label">Type de repos <span class="text-danger">*</span></label>
                                <select name="type_repos" id="type_repos" class="form-select @error('type_repos') is-invalid @enderror" required>
                                    <option value="complet" {{ old('type_repos') == 'complet' ? 'selected' : '' }}>
                                        <i class="fas fa-bed"></i> Repos complet (jour et nuit)
                                    </option>
                                    <option value="nuit" {{ old('type_repos') == 'nuit' ? 'selected' : '' }}>
                                        <i class="fas fa-moon"></i> Repos nuit uniquement
                                    </option>
                                    <option value="jour" {{ old('type_repos') == 'jour' ? 'selected' : '' }}>
                                        <i class="fas fa-sun"></i> Repos jour uniquement
                                    </option>
                                </select>
                                @error('type_repos')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="motif" class="form-label">Motif <span class="text-danger">*</span></label>
                                <select name="motif" id="motif" class="form-select @error('motif') is-invalid @enderror" required>
                                    <option value="">Sélectionner un motif</option>
                                    @foreach(\App\Models\ReposConducteur::MOTIFS as $motif)
                                        <option value="{{ $motif }}" {{ old('motif') == $motif ? 'selected' : '' }}>
                                            {{ $motif }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('motif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea name="notes" 
                                      id="notes" 
                                      class="form-control @error('notes') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Notes additionnelles...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('repos.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
