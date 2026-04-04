@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-clock text-warning me-2"></i>Repos en attente de validation</h1>
            <p class="text-muted mb-0">Repos générés automatiquement par le système de détection de fatigue</p>
        </div>
        <a href="{{ route('repos.dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-heartbeat me-1"></i> Dashboard Fatigue
        </a>
    </div>

    @if($reposEnAttente->count() > 0)
    <!-- Actions en masse -->
    <form action="{{ route('repos.valider-masse') }}" method="POST" id="formMasse">
        @csrf
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning d-flex justify-content-between align-items-center">
                <span><i class="fas fa-list me-2"></i>{{ $reposEnAttente->count() }} repos en attente</span>
                <button type="submit" class="btn btn-success btn-sm" id="btnValiderSelection" disabled>
                    <i class="fas fa-check-double me-1"></i> Valider la sélection
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Conducteur</th>
                                <th>Période</th>
                                <th>Type</th>
                                <th>Contexte</th>
                                <th>Score fatigue</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reposEnAttente as $repos)
                            <tr>
                                <td>
                                    <input type="checkbox" name="repos_ids[]" value="{{ $repos->id }}" class="form-check-input checkbox-repos">
                                </td>
                                <td>
                                    <a href="{{ route('repos.detail-conducteur', $repos->conducteur_id) }}" class="text-decoration-none">
                                        <strong>{{ $repos->conducteur->prenom }} {{ $repos->conducteur->nom }}</strong>
                                    </a>
                                </td>
                                <td>
                                    <span class="text-nowrap">
                                        {{ $repos->date_debut->format('d/m/Y') }}
                                        <i class="fas fa-arrow-right text-muted mx-1"></i>
                                        {{ $repos->date_fin->format('d/m/Y') }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ $repos->duree }} jour(s)</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <i class="fas {{ $repos->icone }} me-1"></i>
                                        {{ $repos->type_libelle }}
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        @if($repos->voyages_nuit_avant > 0)
                                            <span class="badge bg-dark me-1">
                                                <i class="fas fa-moon"></i> {{ $repos->voyages_nuit_avant }} nuits
                                            </span>
                                        @endif
                                        @if($repos->voyages_jour_avant > 0)
                                            <span class="badge bg-warning text-dark me-1">
                                                <i class="fas fa-sun"></i> {{ $repos->voyages_jour_avant }} jours
                                            </span>
                                        @endif
                                        @if($repos->jours_travail_consecutifs > 0)
                                            <span class="badge bg-info">
                                                <i class="fas fa-calendar"></i> {{ $repos->jours_travail_consecutifs }}j sans repos
                                            </span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    @if($repos->score_fatigue_declencheur)
                                        @php
                                            $couleur = match(true) {
                                                $repos->score_fatigue_declencheur >= 85 => 'danger',
                                                $repos->score_fatigue_declencheur >= 70 => 'orange',
                                                $repos->score_fatigue_declencheur >= 50 => 'warning',
                                                default => 'success'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $couleur }}" style="{{ $couleur === 'orange' ? 'background-color: #fd7e14 !important;' : '' }}">
                                            {{ $repos->score_fatigue_declencheur }}%
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <form action="{{ route('repos.accepter', $repos->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Accepter">
                                            <i class="fas fa-check"></i> Accepter
                                        </button>
                                    </form>
                                    <form action="{{ route('repos.refuser', $repos->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Refuser" onclick="return confirm('Refuser et supprimer ce repos suggéré ?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
            <h4>Aucun repos en attente</h4>
            <p class="text-muted">Tous les repos suggérés ont été traités.</p>
            <a href="{{ route('repos.dashboard') }}" class="btn btn-primary">
                <i class="fas fa-heartbeat me-1"></i> Voir le dashboard fatigue
            </a>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.checkbox-repos');
        const btnValider = document.getElementById('btnValiderSelection');

        function updateButton() {
            const checked = document.querySelectorAll('.checkbox-repos:checked').length;
            btnValider.disabled = checked === 0;
            btnValider.textContent = checked > 0 
                ? `Valider la sélection (${checked})` 
                : 'Valider la sélection';
        }

        selectAll?.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateButton();
        });

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = document.querySelectorAll('.checkbox-repos:checked').length === checkboxes.length;
                if (selectAll) selectAll.checked = allChecked;
                updateButton();
            });
        });
    });
</script>
@endpush
@endsection
