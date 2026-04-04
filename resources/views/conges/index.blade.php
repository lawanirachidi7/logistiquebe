@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-umbrella-beach text-primary me-2"></i>Gestion des Congés</h1>
            <p class="text-muted mb-0">Planification et suivi des congés des conducteurs</p>
        </div>
        <div>
            <a href="{{ route('conges.calendrier') }}" class="btn btn-outline-primary me-2">
                <i class="fas fa-calendar-alt me-1"></i> Calendrier
            </a>
            <a href="{{ route('conges.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nouveau Congé
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-user-clock fa-2x me-3"></i>
                    <div>
                        <h3 class="mb-0">{{ $stats['en_cours'] }}</h3>
                        <span>En cours</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-calendar-plus fa-2x me-3"></i>
                    <div>
                        <h3 class="mb-0">{{ $stats['a_venir'] }}</h3>
                        <span>À venir</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-users fa-2x me-3"></i>
                    <div>
                        <h3 class="mb-0">{{ $stats['conducteurs_en_conge'] }}</h3>
                        <span>Conducteurs en congé</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-chart-bar fa-2x me-3"></i>
                    <div>
                        <h3 class="mb-0">{{ $stats['total_annee'] }}</h3>
                        <span>Total cette année</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('conges.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Conducteur</label>
                    <select name="conducteur_id" class="form-select">
                        <option value="">Tous</option>
                        @foreach($conducteurs as $conducteur)
                            <option value="{{ $conducteur->id }}" {{ request('conducteur_id') == $conducteur->id ? 'selected' : '' }}>
                                {{ $conducteur->prenom }} {{ $conducteur->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">Tous</option>
                        @foreach(\App\Models\Conge::getTypesForSelect() as $key => $label)
                            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="">Tous</option>
                        <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                        <option value="a_venir" {{ request('statut') == 'a_venir' ? 'selected' : '' }}>À venir</option>
                        <option value="termines" {{ request('statut') == 'termines' ? 'selected' : '' }}>Terminés</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date début</label>
                    <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <a href="{{ route('conges.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages Flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Liste des congés -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Conducteur</th>
                            <th>Période</th>
                            <th>Durée</th>
                            <th>Type</th>
                            <th>Motif</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($conges as $conge)
                        <tr class="{{ $conge->est_actif ? 'table-success' : '' }}">
                            <td>
                                <strong>{{ $conge->conducteur->prenom }} {{ $conge->conducteur->nom }}</strong>
                            </td>
                            <td>
                                <span class="text-nowrap">
                                    {{ $conge->date_debut->format('d/m/Y') }}
                                    <i class="fas fa-arrow-right text-muted mx-1"></i>
                                    {{ $conge->date_fin->format('d/m/Y') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $conge->duree }} jour(s)</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $conge->type_couleur }}">
                                    <i class="fas {{ $conge->type_icone }} me-1"></i>
                                    {{ $conge->type_label }}
                                </span>
                            </td>
                            <td>{{ Str::limit($conge->motif, 30) }}</td>
                            <td>
                                <span class="badge bg-{{ $conge->statut_couleur }}">
                                    {{ $conge->statut_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('conges.show', $conge) }}" class="btn btn-outline-info" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('conges.edit', $conge) }}" class="btn btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('conges.destroy', $conge) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce congé ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-umbrella-beach fa-3x mb-3 d-block opacity-50"></i>
                                Aucun congé trouvé
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($conges->hasPages())
        <div class="card-footer bg-white">
            {{ $conges->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
