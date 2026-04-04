@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier le repos</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('repos.update', $repo->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="conducteur_id" class="form-label">Conducteur <span class="text-danger">*</span></label>
                            <select name="conducteur_id" id="conducteur_id" class="form-select @error('conducteur_id') is-invalid @enderror" required>
                                <option value="">Sélectionner un conducteur</option>
                                @foreach($conducteurs as $conducteur)
                                    <option value="{{ $conducteur->id }}" 
                                        {{ old('conducteur_id', $repo->conducteur_id) == $conducteur->id ? 'selected' : '' }}>
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
                                       value="{{ old('date_debut', $repo->date_debut->format('Y-m-d')) }}"
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
                                       value="{{ old('date_fin', $repo->date_fin->format('Y-m-d')) }}"
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
                                    <option value="complet" {{ old('type_repos', $repo->type_repos) == 'complet' ? 'selected' : '' }}>
                                        Repos complet (jour et nuit)
                                    </option>
                                    <option value="nuit" {{ old('type_repos', $repo->type_repos) == 'nuit' ? 'selected' : '' }}>
                                        Repos nuit uniquement
                                    </option>
                                    <option value="jour" {{ old('type_repos', $repo->type_repos) == 'jour' ? 'selected' : '' }}>
                                        Repos jour uniquement
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
                                        <option value="{{ $motif }}" {{ old('motif', $repo->motif) == $motif ? 'selected' : '' }}>
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
                                      placeholder="Notes additionnelles...">{{ old('notes', $repo->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($repo->source !== 'manuel')
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Ce repos a été <strong>{{ $repo->source_libelle }}</strong>
                            @if($repo->score_fatigue_declencheur)
                                avec un score de fatigue de <strong>{{ $repo->score_fatigue_declencheur }}%</strong>
                            @endif
                        </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('repos.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
