@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Modifier le congé</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('conges.update', $conge) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="conducteur_id" class="form-label">Conducteur <span class="text-danger">*</span></label>
                            <select name="conducteur_id" id="conducteur_id" class="form-select @error('conducteur_id') is-invalid @enderror" required>
                                <option value="">Sélectionner un conducteur</option>
                                @foreach($conducteurs as $conducteur)
                                    <option value="{{ $conducteur->id }}" 
                                        {{ old('conducteur_id', $conge->conducteur_id) == $conducteur->id ? 'selected' : '' }}>
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
                                       value="{{ old('date_debut', $conge->date_debut->format('Y-m-d')) }}"
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
                                       value="{{ old('date_fin', $conge->date_fin->format('Y-m-d')) }}"
                                       required>
                                @error('date_fin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type de congé <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Sélectionner un type</option>
                                    @foreach($types as $key => $label)
                                        <option value="{{ $key }}" {{ old('type', $conge->type) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="motif" class="form-label">Motif <span class="text-danger">*</span></label>
                                <input type="text" 
                                       name="motif" 
                                       id="motif" 
                                       class="form-control @error('motif') is-invalid @enderror"
                                       value="{{ old('motif', $conge->motif) }}"
                                       placeholder="Ex: Vacances familiales, récupération..."
                                       required>
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
                                      placeholder="Informations complémentaires...">{{ old('notes', $conge->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Informations sur le congé -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Informations</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-muted">Créé le:</small>
                                        <p class="mb-0">{{ $conge->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Statut actuel:</small>
                                        <p class="mb-0">
                                            <span class="badge bg-{{ $conge->statut_couleur }}">{{ $conge->statut_label }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-muted">Durée:</small>
                                        <p class="mb-0 fw-bold" id="duree-estimee">{{ $conge->duree }} jour(s)</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('conges.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Retour
                            </a>
                            <div>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-1"></i> Mettre à jour
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateDebut = document.getElementById('date_debut');
    const dateFin = document.getElementById('date_fin');
    const dureeEstimee = document.getElementById('duree-estimee');
    
    function calculerDuree() {
        if (dateDebut.value && dateFin.value) {
            const debut = new Date(dateDebut.value);
            const fin = new Date(dateFin.value);
            const diffTime = Math.abs(fin - debut);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            
            if (diffDays > 0) {
                dureeEstimee.textContent = diffDays + ' jour(s)';
            } else {
                dureeEstimee.textContent = 'Dates invalides';
            }
        }
    }
    
    dateDebut.addEventListener('change', calculerDuree);
    dateFin.addEventListener('change', calculerDuree);
});
</script>
@endpush
@endsection
