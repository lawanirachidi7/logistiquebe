@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-route"></i>
                Liste des Lignes
            </h1>
            <p class="page-subtitle">Gérez vos itinéraires et trajets</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('lignes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter une ligne
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            @if(session('ligne_a_supprimer'))
                <form action="{{ route('lignes.destroy', session('ligne_a_supprimer')) }}" method="POST" class="d-inline ms-2">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="force" value="1">
                    <button type="submit" class="btn btn-sm btn-danger" 
                        onclick="return confirm('Attention ! Cela supprimera aussi tous les voyages associés. Continuer ?')">
                        <i class="fas fa-trash me-1"></i>Forcer la suppression
                    </button>
                </form>
            @endif
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                        <tr>
                            <th class="row-num">N°</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Horaire</th>
                            <th>Ligne Retour</th>
                            <th>Ville Départ</th>
                            <th>Ville Arrivée</th>
                            <th>Ligne Nord</th>
                            <th class="no-sort no-export">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lignes as $ligne)
                            <tr>
                                <td></td>
                                <td class="fw-semibold">{{ $ligne->nom }}</td>
                                <td>
                                    @if($ligne->type === 'Aller')
                                        <span class="badge bg-primary">
                                            <i class="fas fa-arrow-right me-1"></i>Aller
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="fas fa-arrow-left me-1"></i>Retour
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <i class="fas fa-clock me-1"></i>{{ $ligne->horaire_formate }}
                                    </span>
                                </td>
                                <td>
                                    @if($ligne->type === 'Aller')
                                        @if($ligne->ligneRetourAssociee)
                                            <span class="badge bg-secondary">{{ $ligne->ligneRetourAssociee->nom }} ({{ $ligne->ligneRetourAssociee->horaire_formate }})</span>
                                        @else
                                            <span class="text-muted">Auto</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt text-success me-1"></i>{{ $ligne->ville_depart }}
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>{{ $ligne->ville_arrivee }}
                                </td>
                                <td>
                                    @if($ligne->est_ligne_nord)
                                        <span class="badge badge-oui">Oui</span>
                                    @else
                                        <span class="badge badge-non">Non</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group-actions">
                                        <a href="{{ route('lignes.edit', $ligne->id) }}" class="btn btn-sm btn-warning" title="Éditer">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('lignes.destroy', $ligne->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr ?')" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
