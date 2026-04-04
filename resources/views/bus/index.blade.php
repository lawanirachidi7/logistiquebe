@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-bus"></i>
                Liste des Bus
            </h1>
            <p class="page-subtitle">Gérez votre flotte de véhicules</p>
        </div>
        @canaction
        <div class="page-header-actions">
            <a href="{{ route('bus.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Ajouter un bus
            </a>
        </div>
        @endcanaction
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
                            <th>Immatriculation</th>
                            <th>Type</th>
                            <th>Position actuelle</th>
                            <th>Ligne Nord</th>
                            <th>Disponible</th>
                            <th class="no-sort no-export">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($buses as $bus)
                            <tr>
                                <td></td>
                                <td class="fw-semibold">{{ $bus->immatriculation }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ optional($bus->typeBus)->libelle ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $bus->ville_actuelle ?? 'Parakou' }}
                                    </span>
                                </td>
                                <td>
                                    @if($bus->ligne_nord)
                                        <span class="badge badge-oui">Oui</span>
                                    @else
                                        <span class="badge badge-non">Non</span>
                                    @endif
                                </td>
                                <td>
                                    @if($bus->disponible)
                                        <span class="badge badge-status-actif">Disponible</span>
                                    @else
                                        <span class="badge badge-status-inactif">Indisponible</span>
                                    @endif
                                </td>
                                <td>
                                    @canaction
                                    <div class="btn-group-actions">
                                        <a href="{{ route('bus.edit', $bus->id) }}" class="btn btn-sm btn-warning" title="Éditer">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('bus.destroy', $bus->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr ?')" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @endcanaction
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
