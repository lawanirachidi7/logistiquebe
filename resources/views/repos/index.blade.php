@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-bed text-primary me-2"></i>Gestion des Repos</h1>
            <p class="text-muted mb-0">Historique et gestion des périodes de repos des conducteurs</p>
        </div>
        <a href="{{ route('repos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Nouveau Repos
        </a>
    </div>

    <!-- Statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-clock fa-2x me-3"></i>
                    <div>
                        <h3 class="mb-0">{{ $stats['en_attente'] }}</h3>
                        <span>En attente de validation</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-bed fa-2x me-3"></i>
                    <div>
                        <h3 class="mb-0">{{ $stats['actifs_aujourdhui'] }}</h3>
                        <span>Repos actifs aujourd'hui</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-magic fa-2x me-3"></i>
                    <div>
                        <h3 class="mb-0">{{ $stats['automatiques_semaine'] }}</h3>
                        <span>Auto-générés cette semaine</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('repos.index') }}" method="GET" class="row g-3">
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
                    <label class="form-label">Type de repos</label>
                    <select name="type_repos" class="form-select">
                        <option value="">Tous</option>
                        <option value="complet" {{ request('type_repos') == 'complet' ? 'selected' : '' }}>Complet</option>
                        <option value="nuit" {{ request('type_repos') == 'nuit' ? 'selected' : '' }}>Nuit seulement</option>
                        <option value="jour" {{ request('type_repos') == 'jour' ? 'selected' : '' }}>Jour seulement</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Source</label>
                    <select name="source" class="form-select">
                        <option value="">Toutes</option>
                        <option value="manuel" {{ request('source') == 'manuel' ? 'selected' : '' }}>Manuel</option>
                        <option value="automatique" {{ request('source') == 'automatique' ? 'selected' : '' }}>Automatique</option>
                        <option value="suggere" {{ request('source') == 'suggere' ? 'selected' : '' }}>Suggéré</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="">Tous</option>
                        <option value="actifs" {{ request('statut') == 'actifs' ? 'selected' : '' }}>Actifs</option>
                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                        <option value="valides" {{ request('statut') == 'valides' ? 'selected' : '' }}>Validés</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    <a href="{{ route('repos.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des repos -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Conducteur</th>
                            <th>Période</th>
                            <th>Type</th>
                            <th>Motif</th>
                            <th>Source</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($repos as $r)
                        <tr class="{{ $r->estActif() ? 'table-success' : '' }}">
                            <td>
                                <a href="{{ route('repos.detail-conducteur', $r->conducteur_id) }}" class="text-decoration-none">
                                    <strong>{{ $r->conducteur->prenom }} {{ $r->conducteur->nom }}</strong>
                                </a>
                            </td>
                            <td>
                                <span class="text-nowrap">
                                    {{ $r->date_debut->format('d/m/Y') }}
                                    <i class="fas fa-arrow-right text-muted mx-1"></i>
                                    {{ $r->date_fin->format('d/m/Y') }}
                                </span>
                                <br>
                                <small class="text-muted">{{ $r->duree }} jour(s)</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <i class="fas {{ $r->icone }} me-1"></i>
                                    {{ $r->type_libelle }}
                                </span>
                            </td>
                            <td>{{ $r->motif }}</td>
                            <td>
                                <span class="badge bg-{{ $r->badge_couleur }}">
                                    {{ $r->source_libelle }}
                                </span>
                                @if($r->score_fatigue_declencheur)
                                    <br><small class="text-muted">Score: {{ $r->score_fatigue_declencheur }}%</small>
                                @endif
                            </td>
                            <td>
                                @if($r->estActif())
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Actif</span>
                                @elseif(!$r->accepte && $r->source !== 'manuel')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> En attente</span>
                                @elseif($r->date_fin < now())
                                    <span class="badge bg-secondary"><i class="fas fa-history"></i> Terminé</span>
                                @else
                                    <span class="badge bg-info"><i class="fas fa-calendar"></i> Planifié</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if(!$r->accepte && $r->source !== 'manuel')
                                    <form action="{{ route('repos.accepter', $r->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Accepter">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('repos.refuser', $r->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Refuser" onclick="return confirm('Refuser ce repos ?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @else
                                    @canaction
                                    <a href="{{ route('repos.edit', $r->id) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('repos.destroy', $r->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Supprimer ce repos ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcanaction
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p class="mb-0">Aucun repos trouvé</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($repos->hasPages())
        <div class="card-footer">
            {{ $repos->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
