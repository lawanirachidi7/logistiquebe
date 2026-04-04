@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas {{ $conge->type_icone }} text-{{ $conge->type_couleur }} me-2"></i>
                        Détails du congé
                    </h1>
                    <p class="text-muted mb-0">{{ $conge->conducteur->prenom }} {{ $conge->conducteur->nom }}</p>
                </div>
                <div>
                    <a href="{{ route('conges.edit', $conge) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </a>
                    <a href="{{ route('conges.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Retour
                    </a>
                </div>
            </div>

            <!-- Carte principale -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-{{ $conge->type_couleur }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas {{ $conge->type_icone }} me-2"></i>
                            {{ $conge->type_label }}
                        </h5>
                        <span class="badge bg-{{ $conge->statut_couleur == 'success' ? 'light text-success' : ($conge->statut_couleur == 'warning' ? 'dark' : 'light text-'.$conge->statut_couleur) }}">
                            {{ $conge->statut_label }}
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Conducteur -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-3 me-3">
                                    <i class="fas fa-user fa-2x text-primary"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Conducteur</small>
                                    <h5 class="mb-0">{{ $conge->conducteur->prenom }} {{ $conge->conducteur->nom }}</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Durée -->
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded-circle p-3 me-3">
                                    <i class="fas fa-clock fa-2x text-info"></i>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Durée</small>
                                    <h5 class="mb-0">{{ $conge->duree }} jour(s)</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Période -->
                        <div class="col-12">
                            <div class="bg-light rounded p-3">
                                <div class="row text-center">
                                    <div class="col-md-5">
                                        <small class="text-muted d-block">Date de début</small>
                                        <h4 class="mb-0">{{ $conge->date_debut->format('d/m/Y') }}</h4>
                                        <small class="text-muted">{{ $conge->date_debut->translatedFormat('l') }}</small>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center justify-content-center">
                                        <i class="fas fa-arrow-right fa-2x text-muted"></i>
                                    </div>
                                    <div class="col-md-5">
                                        <small class="text-muted d-block">Date de fin</small>
                                        <h4 class="mb-0">{{ $conge->date_fin->format('d/m/Y') }}</h4>
                                        <small class="text-muted">{{ $conge->date_fin->translatedFormat('l') }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Motif -->
                        <div class="col-12">
                            <h6 class="text-muted"><i class="fas fa-quote-left me-2"></i>Motif</h6>
                            <p class="mb-0 fs-5">{{ $conge->motif }}</p>
                        </div>

                        <!-- Notes -->
                        @if($conge->notes)
                        <div class="col-12">
                            <h6 class="text-muted"><i class="fas fa-sticky-note me-2"></i>Notes</h6>
                            <p class="mb-0">{{ $conge->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Informations de validation -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Informations de validation</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Créé le</small>
                            <p class="mb-0">{{ $conge->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Validé</small>
                            <p class="mb-0">
                                @if($conge->valide)
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Oui</span>
                                @else
                                    <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>En attente</span>
                                @endif
                            </p>
                        </div>
                        @if($conge->validateur)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Validé par</small>
                            <p class="mb-0">{{ $conge->validateur->name }}</p>
                            <small class="text-muted">{{ $conge->valide_le->format('d/m/Y à H:i') }}</small>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('conges.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                </a>
                <div>
                    <a href="{{ route('conges.edit', $conge) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </a>
                    <form action="{{ route('conges.destroy', $conge) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce congé ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
