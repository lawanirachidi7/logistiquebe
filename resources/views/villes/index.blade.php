@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-city"></i>
                Liste des Villes
            </h1>
            <p class="page-subtitle">Gérez les villes de desserte</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('villes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter une ville
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
                            <th>Statut</th>
                            <th class="no-sort no-export">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($villes as $ville)
                            <tr>
                                <td></td>
                                <td class="fw-semibold">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>{{ $ville->nom }}
                                </td>
                                <td>
                                    @if($ville->actif)
                                        <span class="badge badge-status-actif">Active</span>
                                    @else
                                        <span class="badge badge-status-inactif">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group-actions">
                                        <a href="{{ route('villes.edit', $ville->id) }}" class="btn btn-sm btn-warning" title="Éditer">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('villes.destroy', $ville->id) }}" method="POST" class="d-inline">
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
