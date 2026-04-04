@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-id-card"></i>
                Liste des Conducteurs
            </h1>
            <p class="page-subtitle">Gérez vos conducteurs et leurs informations</p>
        </div>
        @canaction
        <div class="page-header-actions">
            <a href="{{ route('conducteurs.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter
            </a>
            <a href="{{ route('conducteurs.import.form') }}" class="btn btn-success">
                <i class="fas fa-upload"></i> Importer
            </a>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                <i class="fas fa-trash"></i> Supprimer tout
            </button>
        </div>
        @endcanaction
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>Erreurs d'importation :</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
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
                            <th>Prénom</th>
                            <th>Spécialiste nuit</th>
                            <th>Remplaçant nuit</th>
                            <th>Famille hors Parakou</th>
                            <th>Actif</th>
                            <th class="no-sort no-export">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conducteurs as $conducteur)
                        <tr>
                            <td></td>
                            <td class="fw-semibold">{{ $conducteur->nom }}</td>
                            <td>{{ $conducteur->prenom }}</td>
                            <td>
                                @if($conducteur->specialiste_nuit) 
                                    <span class="badge badge-oui">Oui</span> 
                                @else 
                                    <span class="badge badge-non">Non</span> 
                                @endif
                            </td>
                            <td>
                                @if($conducteur->remplacant_nuit) 
                                    <span class="badge badge-oui">Oui</span> 
                                @else 
                                    <span class="badge badge-non">Non</span> 
                                @endif
                            </td>
                            <td>
                                @if($conducteur->famille_hors_parakou) 
                                    <span class="badge badge-oui">Oui</span> 
                                @else 
                                    <span class="badge badge-non">Non</span> 
                                @endif
                            </td>
                            <td>
                                @if($conducteur->actif) 
                                    <span class="badge badge-status-actif">Actif</span> 
                                @else 
                                    <span class="badge badge-status-inactif">Inactif</span> 
                                @endif
                            </td>
                            <td>
                                <div class="btn-group-actions">
                                    <a href="{{ route('conducteurs.show', $conducteur->id) }}" class="btn btn-sm btn-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @canaction
                                    <a href="{{ route('conducteurs.edit', $conducteur->id) }}" class="btn btn-sm btn-warning" title="Éditer">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('conducteurs.destroy', $conducteur->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr ?')" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcanaction
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

<!-- Modal de confirmation pour supprimer tous les conducteurs -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAllModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Attention !</strong> Cette action est irréversible.
                </div>
                <p>Êtes-vous sûr de vouloir supprimer <strong>tous les conducteurs</strong> de la liste ?</p>
                <p class="mb-2">Nombre de conducteurs à supprimer : <span class="badge bg-danger fs-6">{{ $conducteurs->count() }}</span></p>
                <p class="text-danger mb-0"><small><i class="fas fa-info-circle me-1"></i>Cela supprimera aussi les voyages, repos et indisponibilités liés aux conducteurs.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Annuler
                </button>
                <form action="{{ route('conducteurs.deleteAll') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i>Supprimer tout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
